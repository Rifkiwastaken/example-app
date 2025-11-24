<?php

namespace App\Http\Controllers;

use App\Models\Planting;
use App\Models\Harvest;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Certification;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports index page
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * A. Laporan Produksi & Pertanian
     */
    
    /**
     * Laporan Realisasi Tanam & Panen
     */
    public function plantingHarvest(Request $request)
    {
        $query = Planting::with(['plant.type', 'location.baseLocation', 'harvest'])
            ->whereNotNull('planted_at');

        // Filters
        if ($request->filled('year')) {
            $query->whereYear('planted_at', $request->year);
        }
        
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }
        
        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('planted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('planted_at', '<=', $request->date_to);
        }

        $plantings = $query->orderBy('planted_at', 'desc')->paginate(50);

        // Calculate productivity (Ton/Ha) for each planting
        $plantings->getCollection()->transform(function ($planting) {
            $harvest = $planting->harvest;
            $area = $planting->location->map_size ?? 0; // in Ha
            
            $productivity = 0;
            if ($harvest && $harvest->quantity > 0 && $area > 0) {
                // Convert harvest quantity to ton
                $harvestInTon = $this->convertToTon($harvest->quantity, $harvest->unit);
                $productivity = $harvestInTon / $area;
            }
            
            $planting->productivity = round($productivity, 2);
            $planting->status = $harvest ? ($harvest->quantity > 0 ? 'Berhasil' : 'Gagal') : 'Belum Panen';
            
            return $planting;
        });

        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $years = Planting::selectRaw('YEAR(planted_at) as year')
            ->whereNotNull('planted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('reports.planting-harvest', compact('plantings', 'plants', 'locations', 'years'));
    }

    /**
     * Laporan Penggunaan Sarana Produksi (Perawatan & Nutrisi)
     */
    public function productionSupplies(Request $request)
    {
        // Combine treatments and nutrients
        $treatmentsQuery = Treatment::with(['plantingLocation', 'planting.plant'])
            ->whereNotNull('treatment_date');

        $nutrientsQuery = Nutrient::with(['plantingLocation', 'planting.plant'])
            ->whereNotNull('application_date');

        // Filters
        if ($request->filled('date_from')) {
            $treatmentsQuery->whereDate('treatment_date', '>=', $request->date_from);
            $nutrientsQuery->whereDate('application_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $treatmentsQuery->whereDate('treatment_date', '<=', $request->date_to);
            $nutrientsQuery->whereDate('application_date', '<=', $request->date_to);
        }

        if ($request->filled('planting_location_id')) {
            $treatmentsQuery->where('planting_location_id', $request->planting_location_id);
            $nutrientsQuery->where('planting_location_id', $request->planting_location_id);
        }

        $treatments = $treatmentsQuery->orderBy('treatment_date', 'desc')->get();
        $nutrients = $nutrientsQuery->orderBy('application_date', 'desc')->get();

        $locations = PlantingLocation::orderBy('name')->get();

        return view('reports.production-supplies', compact('treatments', 'nutrients', 'locations'));
    }

    /**
     * B. Laporan Stok & Gudang
     */

    /**
     * Laporan Posisi Stok Akhir (Stock Opname)
     */
    public function stockPosition(Request $request)
    {
        $query = InventoryLot::with(['inventoryType', 'warehouse', 'bin'])
            ->where('current_stock', '>', 0);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('inventory_type_id')) {
            $query->where('inventory_type_id', $request->inventory_type_id);
        }

        $lots = $query->orderBy('warehouse_id')->orderBy('inventory_type_id')->paginate(50);

        // Calculate asset value
        $lots->getCollection()->transform(function ($lot) {
            // Using estimated_value_per_unit from inventory_type
            $unitPrice = $lot->inventoryType->estimated_value_per_unit ?? 0;
            $lot->asset_value = $lot->current_stock * $unitPrice;
            return $lot;
        });

        $warehouses = Warehouse::orderBy('name')->get();
        $inventoryTypes = \App\Models\InventoryType::orderBy('name')->get();

        return view('reports.stock-position', compact('lots', 'warehouses', 'inventoryTypes'));
    }

    /**
     * Laporan Mutasi Stok (Kartu Stok)
     */
    public function stockMutation(Request $request)
    {
        $query = InventoryTransaction::with(['inventoryType', 'lot', 'warehouse', 'bin']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('inventory_lot_id')) {
            $query->where('inventory_lot_id', $request->inventory_lot_id);
        }

        if ($request->filled('inventory_type_id')) {
            $query->where('inventory_type_id', $request->inventory_type_id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate running balance
        $balance = 0;
        $transactions->getCollection()->transform(function ($transaction) use (&$balance) {
            if ($transaction->transaction_type === 'masuk') {
                $balance += $transaction->quantity;
            } else {
                $balance -= $transaction->quantity;
            }
            $transaction->balance = $balance;
            return $transaction;
        });

        $lots = InventoryLot::with('inventoryType')->orderBy('production_id')->get();
        $inventoryTypes = \App\Models\InventoryType::orderBy('name')->get();

        return view('reports.stock-mutation', compact('transactions', 'lots', 'inventoryTypes'));
    }

    /**
     * Laporan Monitoring Masa Edar (Kadaluarsa)
     */
    public function expiryMonitoring(Request $request)
    {
        $query = InventoryLot::with(['inventoryType', 'warehouse', 'bin'])
            ->whereNotNull('expiry_date')
            ->where('current_stock', '>', 0);

        if ($request->filled('days_from')) {
            $daysFrom = now()->addDays($request->days_from);
            $query->whereDate('expiry_date', '>=', $daysFrom);
        }

        if ($request->filled('days_to')) {
            $daysTo = now()->addDays($request->days_to);
            $query->whereDate('expiry_date', '<=', $daysTo);
        }

        $lots = $query->orderBy('expiry_date', 'asc')->paginate(50);

        // Calculate days remaining
        $lots->getCollection()->transform(function ($lot) {
            $lot->days_remaining = now()->diffInDays($lot->expiry_date, false);
            return $lot;
        });

        return view('reports.expiry-monitoring', compact('lots'));
    }

    /**
     * C. Laporan Penjualan & Distribusi
     */

    /**
     * Laporan Rekapitulasi Penjualan
     */
    public function sales(Request $request)
    {
        $query = Sale::with(['user', 'items.inventoryType']);

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(50);

        $sales->getCollection()->transform(function ($sale) {
            $sale->total_items = $sale->items->sum('quantity');
            return $sale;
        });

        return view('reports.sales', compact('sales'));
    }

    /**
     * Laporan Sebaran Benih (Distribusi)
     */
    public function distribution(Request $request)
    {
        $query = Sale::with(['items.inventoryType'])
            ->whereHas('items', function($q) {
                $q->whereHas('inventoryType', function($q2) {
                    $q2->where('category', 'like', '%benih%');
                });
            });

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        if ($request->filled('plant_id')) {
            // Filter by plant through inventory type
            $query->whereHas('items.inventoryType', function($q) use ($request) {
                // This needs adjustment based on your inventory type structure
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(50);

        // Extract distribution data
        $distributions = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                if ($item->inventoryType && stripos($item->inventoryType->category ?? '', 'benih') !== false) {
                    $distributions[] = [
                        'buyer_name' => $sale->buyer_name,
                        'buyer_address' => $sale->buyer_contact ?? '-',
                        'variety' => $item->inventoryType->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'purpose' => $sale->notes ?? '-',
                        'date' => $sale->sale_date,
                    ];
                }
            }
        }

        $plants = Plant::orderBy('name')->get();

        return view('reports.distribution', compact('distributions', 'sales', 'plants'));
    }

    /**
     * D. Laporan Sertifikasi
     */

    /**
     * Rekap Status Sertifikasi
     */
    public function certification(Request $request)
    {
        $query = Certification::with([
            'harvest.plant.type',
            'harvest.location.baseLocation',
            'reports' => function($q) {
                $q->orderBy('report_date', 'desc');
            }
        ]);

        if ($request->filled('date_from')) {
            $query->whereHas('reports', function($q) use ($request) {
                $q->whereDate('report_date', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('reports', function($q) use ($request) {
                $q->whereDate('report_date', '<=', $request->date_to);
            });
        }

        if ($request->filled('certification_status')) {
            $query->where('certification_status', $request->certification_status);
        }

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        $certifications = $query->orderBy('created_at', 'desc')->paginate(50);

        $plants = Plant::orderBy('name')->get();

        return view('reports.certification', compact('certifications', 'plants'));
    }

    /**
     * Helper method to convert quantity to ton
     */
    private function convertToTon($quantity, $unit)
    {
        $unit = strtolower($unit);
        
        // Conversion factors
        $factors = [
            'kg' => 0.001,
            'kilogram' => 0.001,
            'gram' => 0.000001,
            'ton' => 1,
            'kuintal' => 0.1,
        ];

        return $quantity * ($factors[$unit] ?? 1);
    }
}

