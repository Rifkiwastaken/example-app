<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryType;
use App\Models\InventoryTypeSeed;
use App\Models\Plant;
use App\Models\Warehouse;
use App\Models\CertificationReport;
use App\Models\LandingPageSetting;
use Illuminate\Support\Facades\DB;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        // Get search query
        $searchQuery = $request->get('search', '');
        
        // Get filter parameters
        $warehouseFilter = $request->get('warehouse', 'all');
        $seedClassFilter = $request->get('seed_class', 'all');
        
        // Get inventory type IDs that have seeds (using raw query to avoid Eloquent issues)
        $inventoryTypeIdsQuery = DB::table('inventory_type_seeds')
            ->select('inventory_type_id')
            ->where('total_seed_quantity', '>', 0)
            ->distinct();
        
        // Apply search filter if needed
        if ($searchQuery) {
            $inventoryTypeIdsQuery->join('inventory_types', 'inventory_type_seeds.inventory_type_id', '=', 'inventory_types.inventory_type_id')
                ->join('plants', 'inventory_types.plant_id', '=', 'plants.plant_id')
                ->where(function($q) use ($searchQuery) {
                    $q->where('plants.name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('plants.variety', 'like', '%' . $searchQuery . '%');
                });
        }
        
        $inventoryTypeIds = $inventoryTypeIdsQuery->pluck('inventory_type_id')->toArray();
        
        // Now get the full InventoryType models with relationships
        $inventoryTypes = InventoryType::whereIn('inventory_type_id', $inventoryTypeIds)
            ->with([
                'plant.type',
                'seeds' => function($query) {
                    $query->where('total_seed_quantity', '>', 0);
                }
            ])
            ->get();
        
        // Get warehouses for filter
        $warehouses = Warehouse::orderBy('name')->get();
        
        // Process inventory types to get stock data
        $stockData = $inventoryTypes->map(function($type) use ($warehouseFilter, $seedClassFilter) {
            // Get total stock from seeds
            $totalStock = $type->seeds()->sum('total_seed_quantity') ?? 0;
            
            // Get latest certification report for seed class
            $certificationReport = null;
            if ($type->plant_id) {
                $certification = \App\Models\Certification::where('plant_id', $type->plant_id)->first();
                if ($certification) {
                    $certificationReport = CertificationReport::where('certification_id', $certification->certification_id)
                        ->where('conclusion', 'LULUS')
                        ->orderBy('report_date', 'desc')
                        ->first();
                }
            }
            
            // Get seed class from certification report
            $seedClass = $certificationReport ? $certificationReport->seed_class_result : null;
            
            // Filter by seed class if specified
            if ($seedClassFilter && $seedClassFilter !== 'all') {
                if ($seedClass !== $seedClassFilter) {
                    return null;
                }
            }
            
            // Get estimated price per kg
            $pricePerKg = $type->estimated_value_per_unit ?? 0;
            if ($type->estimated_kg_per_unit && $type->estimated_kg_per_unit > 0) {
                $pricePerKg = ($type->estimated_value_per_unit ?? 0) / $type->estimated_kg_per_unit;
            }
            
            // Get warehouse locations (from lots)
            $warehouseIds = \App\Models\InventoryLot::where('inventory_type_id', $type->inventory_type_id)
                ->where('current_stock', '>', 0)
                ->pluck('warehouse_id')
                ->unique()
                ->filter()
                ->toArray();
            
            $warehouseNames = !empty($warehouseIds) 
                ? Warehouse::whereIn('warehouse_id', $warehouseIds)->pluck('name')->toArray()
                : [];
            
            // Filter by warehouse if specified
            if ($warehouseFilter && $warehouseFilter !== 'all') {
                if (!in_array($warehouseFilter, $warehouseIds)) {
                    return null;
                }
            }
            
            // Determine status
            $status = $totalStock > 0 ? 'Tersedia' : 'Habis';
            
            return [
                'inventory_type_id' => $type->inventory_type_id,
                'variety_name' => $type->plant->name ?? $type->name,
                'variety_detail' => $type->plant->variety ?? null,
                'seed_class' => $seedClass,
                'warehouse_names' => $warehouseNames,
                'stock_available' => $totalStock,
                'stock_unit' => $type->unit ?? 'kg',
                'price_per_kg' => $pricePerKg,
                'status' => $status,
                'plant_id' => $type->plant_id,
            ];
        })->filter()->values();
        
        // Get statistics
        $totalVarieties = Plant::distinct('name')->count();
        $totalStock = InventoryTypeSeed::sum('total_seed_quantity') ?? 0;
        $totalWarehouses = Warehouse::count();
        // Count planting locations that might be partners (you can adjust this logic based on your data structure)
        $totalPartners = \App\Models\PlantingLocation::where(function($query) {
            $query->where('location_type', 'like', '%mitra%')
                  ->orWhere('location_type', 'like', '%partner%')
                  ->orWhere('ownership_status', 'like', '%mitra%');
        })->count();
        
        // If no partners found, use total planting locations as fallback
        if ($totalPartners == 0) {
            $totalPartners = \App\Models\PlantingLocation::count();
        }
        
        // Get featured varieties (top 4 by stock)
        $featuredVarieties = $inventoryTypes->sortByDesc(function($type) {
            return $type->seeds()->sum('total_seed_quantity') ?? 0;
        })->take(4)->map(function($type) {
            $plant = $type->plant;
            
            // Get latest planting data for days_to_harvest and expected_yield
            $latestPlanting = $type->plant_id 
                ? \App\Models\Planting::where('plant_id', $type->plant_id)
                    ->whereNotNull('days_to_harvest')
                    ->orderBy('planted_at', 'desc')
                    ->first()
                : null;
            
            $certificationReport = null;
            if ($type->plant_id) {
                $certification = \App\Models\Certification::where('plant_id', $type->plant_id)->first();
                if ($certification && isset($certification->certification_id)) {
                    $certificationReport = CertificationReport::where('certification_id', $certification->certification_id)
                        ->where('conclusion', 'LULUS')
                        ->orderBy('report_date', 'desc')
                        ->first();
                }
            }
            
            return [
                'name' => $plant->name ?? $type->name,
                'variety' => $plant->variety ?? null,
                'plant_type' => $plant->type->name ?? null,
                'days_to_harvest' => $latestPlanting ? $latestPlanting->days_to_harvest : null,
                'expected_yield' => $latestPlanting ? $latestPlanting->expected_yield_per_hectare : ($certificationReport ? $certificationReport->estimated_yield : null),
                'stock' => $type->seeds()->sum('total_seed_quantity') ?? 0,
            ];
        });
        
        // Get landing page settings
        $landingSettings = LandingPageSetting::getAllSettings();
        
        return view('landing.index', compact(
            'stockData',
            'warehouses',
            'searchQuery',
            'warehouseFilter',
            'seedClassFilter',
            'totalVarieties',
            'totalStock',
            'totalWarehouses',
            'totalPartners',
            'featuredVarieties',
            'landingSettings'
        ));
    }

    /**
     * Show the form for editing landing page settings (Admin only)
     */
    public function edit()
    {
        // Only admin can access
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }

        $settings = LandingPageSetting::getAllSettings();
        
        return view('landing.edit', compact('settings'));
    }

    /**
     * Update landing page settings (Admin only)
     */
    public function update(Request $request)
    {
        // Only admin can access
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }

        $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string|max:500',
            'hero_image' => 'required|url|max:500',
            'office_address' => 'required|string',
            'office_phone' => 'required|string|max:100',
            'office_whatsapp' => 'required|string|max:100',
            'office_email' => 'required|email|max:255',
            'facebook_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'youtube_url' => 'nullable|url|max:500',
        ]);

        // Update all settings
        foreach ($request->only([
            'hero_title', 'hero_subtitle', 'hero_image',
            'office_address', 'office_phone', 'office_whatsapp', 'office_email',
            'facebook_url', 'instagram_url', 'youtube_url'
        ]) as $key => $value) {
            LandingPageSetting::setValue($key, $value ?? '');
        }

        return redirect()->route('landing.edit')
            ->with('success', 'Pengaturan landing page berhasil diperbarui.');
    }
}

