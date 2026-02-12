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
use App\Models\CertificationReport;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\Warehouse;
use App\Models\Expense;
use App\Models\Task;
use App\Models\PlantingLocationNote;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

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
        // Check if export requested
        if ($request->has('export')) {
            return $this->exportPlantingHarvest($request);
        }

        $query = Planting::with([
            'plant.type', 
            'harvest.certification'
        ])->whereNotNull('planted_at');

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

        $plantings = $query->get();

        // Transform data for Excel format
        $plantings->transform(function ($planting) {
            $harvest = $planting->harvest;
            $certification = $harvest ? $harvest->certification : null;
            
            // Get area in hectares from planting area_ha field, fallback to location map_size
            $area = $planting->area_ha ?? ($planting->location->map_size ?? 0);
            
            // Calon Benih (kg) - harvest quantity in kg
            $candidateSeed = 0;
            if ($harvest && $harvest->quantity > 0) {
                $unit = strtolower($harvest->unit ?? 'kg');
                $factors = ['kg' => 1, 'kilogram' => 1, 'gram' => 0.001, 'ton' => 1000, 'kuintal' => 100];
                $candidateSeed = $harvest->quantity * ($factors[$unit] ?? 1);
            }
            
            // Benih Bersertifikat (kg) - only if certification is lulus or selesai
            $certifiedSeed = 0;
            if ($certification && in_array($certification->certification_status, ['lulus', 'selesai'])) {
                $certifiedSeed = $candidateSeed; // Same as candidate seed if certified
            }
            
            // Kelas Benih
            $seedClass = $certification ? $certification->seed_class_requested : null;
            if ($seedClass) {
                // Format: BS-BD, BD-BP, etc.
                $seedClassFormatted = $seedClass;
                if ($seedClass === 'BS') {
                    $seedClassFormatted = 'BS-BD';
                } elseif ($seedClass === 'BD') {
                    $seedClassFormatted = 'BD-BP';
                } elseif ($seedClass === 'BP') {
                    $seedClassFormatted = 'BP-BR';
                }
            } else {
                $seedClassFormatted = null;
            }
            
            $planting->area_ha = $area;
            $planting->candidate_seed_kg = round($candidateSeed, 0);
            $planting->certified_seed_kg = round($certifiedSeed, 0);
            $planting->seed_class = $seedClassFormatted;
            
            return $planting;
        });

        // Sort by commodity (plant type) and seed class
        $plantings = $plantings->sortBy(function($planting) {
            $commodity = $planting->plant->type->name ?? 'ZZZ_Lainnya';
            $seedClass = $planting->seed_class ?? 'ZZZ';
            return $commodity . '_' . $seedClass;
        })->values();

        // Paginate after sorting
        $currentPage = request()->get('page', 1);
        $perPage = 50;
        $currentItems = $plantings->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $plantings = new LengthAwarePaginator(
            $currentItems,
            $plantings->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

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
     * Laporan Per Lokasi Lahan
     */
    public function byLocation(Request $request)
    {
        // Get selected location
        $selectedLocationId = $request->input('planting_location_id');
        
        if (!$selectedLocationId) {
            // If no location selected, show location selection page
            $locations = PlantingLocation::orderBy('name')->get();
            $plants = Plant::orderBy('name')->get();
            $years = Planting::selectRaw('YEAR(planted_at) as year')
                ->whereNotNull('planted_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
            
            return view('reports.by-location-select', compact('locations', 'plants', 'years'));
        }

        // Check if export requested
        if ($request->has('export')) {
            return $this->exportByLocation($request, $selectedLocationId);
        }

        $plantingLocation = PlantingLocation::findOrFail($selectedLocationId);

        // Build queries with filters
        $plantingQuery = $plantingLocation->plantings()->with(['plant.type', 'harvest', 'losses']);
        $treatmentQuery = $plantingLocation->treatments()->with(['planting.plant', 'responsiblePerson', 'editor']);
        $nutrientQuery = $plantingLocation->nutrients()->with(['planting.plant', 'editor', 'responsiblePerson']);
        $expenseQuery = $plantingLocation->expenses()->with(['planting.plant', 'responsiblePerson', 'treatment', 'nutrient', 'editor']);
        $taskQuery = $plantingLocation->tasks()->with(['assignedUser', 'createdByUser']);
        $noteQuery = $plantingLocation->notes()->with('user');
        $attachmentQuery = $plantingLocation->attachments()->with(['creator', 'editor']);

        // Apply filters
        if ($request->filled('year')) {
            $year = $request->year;
            $plantingQuery->whereYear('planted_at', $year);
            $treatmentQuery->whereYear('treatment_date', $year);
            $nutrientQuery->whereYear('application_date', $year);
            $expenseQuery->whereYear('expense_date', $year);
            $taskQuery->whereYear('due_date', $year);
            $noteQuery->whereYear('note_date', $year);
            $attachmentQuery->whereYear('attachment_date', $year);
        }

        if ($request->filled('date_from')) {
            $dateFrom = $request->date_from;
            $plantingQuery->whereDate('planted_at', '>=', $dateFrom);
            $treatmentQuery->whereDate('treatment_date', '>=', $dateFrom);
            $nutrientQuery->whereDate('application_date', '>=', $dateFrom);
            $expenseQuery->whereDate('expense_date', '>=', $dateFrom);
            $taskQuery->whereDate('due_date', '>=', $dateFrom);
            $noteQuery->whereDate('note_date', '>=', $dateFrom);
            $attachmentQuery->whereDate('attachment_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = $request->date_to;
            $plantingQuery->whereDate('planted_at', '<=', $dateTo);
            $treatmentQuery->whereDate('treatment_date', '<=', $dateTo);
            $nutrientQuery->whereDate('application_date', '<=', $dateTo);
            $expenseQuery->whereDate('expense_date', '<=', $dateTo);
            $taskQuery->whereDate('due_date', '<=', $dateTo);
            $noteQuery->whereDate('note_date', '<=', $dateTo);
            $attachmentQuery->whereDate('attachment_date', '<=', $dateTo);
        }

        if ($request->filled('plant_id')) {
            $plantId = $request->plant_id;
            $plantingQuery->where('plant_id', $plantId);
            $treatmentQuery->whereHas('planting', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
            $nutrientQuery->whereHas('planting', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
            $expenseQuery->whereHas('planting', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
        }

        // Filter by specific planting
        if ($request->filled('planting_id')) {
            $plantingId = $request->planting_id;
            $plantingQuery->where('id', $plantingId);
            $treatmentQuery->where('planting_id', $plantingId);
            $nutrientQuery->where('planting_id', $plantingId);
            $expenseQuery->where('planting_id', $plantingId);
            $taskQuery->where('planting_id', $plantingId);
        }

        // Get data
        $plantings = $plantingQuery->orderBy('planted_at', 'desc')->get();
        $treatments = $treatmentQuery->orderBy('treatment_date', 'desc')->get();
        $nutrients = $nutrientQuery->orderBy('application_date', 'desc')->get();
        $expenses = $expenseQuery->orderBy('expense_date', 'desc')->get();
        $tasks = $taskQuery->orderBy('due_date', 'desc')->get();
        $notes = $noteQuery->orderBy('note_date', 'desc')->get();
        $attachments = $attachmentQuery->orderBy('attachment_date', 'desc')->get();

        // Calculate statistics
        $totalPlantings = $plantings->count();
        $totalHarvests = $plantings->whereNotNull('harvest')->count();
        $totalExpenses = $expenses->sum('amount');
        $totalTreatments = $treatments->count();
        $totalNutrients = $nutrients->count();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('new_status', 'selesai')->count();
        $totalNotes = $notes->count();
        $totalAttachments = $attachments->count();

        // Get filter options
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $years = Planting::selectRaw('YEAR(planted_at) as year')
            ->whereNotNull('planted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Get all plantings for this location for filter dropdown
        $allPlantingsForLocation = $plantingLocation->plantings()
            ->with(['plant'])
            ->orderBy('planted_at', 'desc')
            ->get();

        return view('reports.by-location', compact(
            'plantingLocation',
            'plantings',
            'treatments',
            'nutrients',
            'expenses',
            'tasks',
            'notes',
            'attachments',
            'totalPlantings',
            'totalHarvests',
            'totalExpenses',
            'totalTreatments',
            'totalNutrients',
            'totalTasks',
            'completedTasks',
            'totalNotes',
            'totalAttachments',
            'plants',
            'locations',
            'years',
            'allPlantingsForLocation'
        ));
    }

    /**
     * Laporan Penggunaan Sarana Produksi (Pengeluaran)
     */
    public function productionSupplies(Request $request)
    {
        // Check if export requested
        if ($request->has('export')) {
            return $this->exportProductionSupplies($request);
        }

        $query = Expense::with([
            'plantingLocation',
            'planting.plant',
            'treatment.planting.plant',
            'nutrient.planting.plant',
            'responsiblePerson'
        ]);

        // Filter: Tahun
        if ($request->filled('year')) {
            $query->whereYear('expense_date', $request->year);
        }

        // Filter: Dari Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        // Filter: Sampai Tanggal
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        // Filter: Komoditas (melalui planting)
        if ($request->filled('plant_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('planting', function($subQ) use ($request) {
                    $subQ->where('plant_id', $request->plant_id);
                })->orWhereHas('treatment.planting', function($subQ) use ($request) {
                    $subQ->where('plant_id', $request->plant_id);
                })->orWhereHas('nutrient.planting', function($subQ) use ($request) {
                    $subQ->where('plant_id', $request->plant_id);
                });
            });
        }

        // Filter: Lokasi Lahan
        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }

        // Filter: Jenis Pengeluaran
        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Get filter options
        $years = Expense::selectRaw('YEAR(expense_date) as year')
            ->whereNotNull('expense_date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $plants = Plant::with('type')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        
        $expenseTypes = [
            'perawatan' => 'Perawatan',
            'nutrisi' => 'Nutrisi',
            'upah_pekerja' => 'Upah Pekerja',
            'lainnya' => 'Pengeluaran Lainnya',
        ];

        return view('reports.production-supplies', compact('expenses', 'years', 'plants', 'locations', 'expenseTypes'));
    }

    /**
     * Export Production Supplies Report
     */
    private function exportProductionSupplies(Request $request)
    {
        $query = Expense::with([
            'plantingLocation',
            'planting.plant',
            'treatment.planting.plant',
            'nutrient.planting.plant',
            'responsiblePerson'
        ]);

        // Apply same filters
        if ($request->filled('year')) {
            $query->whereYear('expense_date', $request->year);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('plant_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('planting', function($subQ) use ($request) {
                    $subQ->where('plant_id', $request->plant_id);
                })->orWhereHas('treatment.planting', function($subQ) use ($request) {
                    $subQ->where('plant_id', $request->plant_id);
                })->orWhereHas('nutrient.planting', function($subQ) use ($request) {
                    $subQ->where('plant_id', $request->plant_id);
                });
            });
        }

        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }

        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $expenseTypes = [
            'perawatan' => 'Perawatan',
            'nutrisi' => 'Nutrisi',
            'upah_pekerja' => 'Upah Pekerja',
            'lainnya' => 'Pengeluaran Lainnya',
        ];

        if ($request->get('export') === 'pdf') {
            return view('reports.exports.production-supplies-pdf', compact('expenses', 'expenseTypes'));
        } elseif ($request->get('export') === 'excel') {
            return $this->exportProductionSuppliesExcel($expenses, $expenseTypes);
        }

        return redirect()->back();
    }

    /**
     * Export Production Supplies to Excel (CSV format)
     */
    private function exportProductionSuppliesExcel($expenses, $expenseTypes)
    {
        $filename = 'Laporan_Penggunaan_Sarana_Produksi_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($expenses, $expenseTypes) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['No', 'Tanggal Pengeluaran', 'Nama Pengeluaran', 'Jenis Pengeluaran', 'Komoditas', 'Lokasi Lahan', 'Penanggung Jawab', 'Total Biaya']);
            
            $rowNumber = 1;
            foreach ($expenses as $expense) {
                // Get plant from expense
                $plant = null;
                if ($expense->planting && $expense->planting->plant) {
                    $plant = $expense->planting->plant;
                } elseif ($expense->treatment && $expense->treatment->planting && $expense->treatment->planting->plant) {
                    $plant = $expense->treatment->planting->plant;
                } elseif ($expense->nutrient && $expense->nutrient->planting && $expense->nutrient->planting->plant) {
                    $plant = $expense->nutrient->planting->plant;
                }
                
                fputcsv($file, [
                    $rowNumber++,
                    $expense->expense_date ? $expense->expense_date->format('d-m-Y') : '-',
                    $expense->expense_name ?? '-',
                    $expenseTypes[$expense->expense_type] ?? '-',
                    $plant ? $plant->name : '-',
                    $expense->plantingLocation->name ?? '-',
                    $expense->responsiblePerson->name ?? '-',
                    number_format($expense->amount, 0, ',', '.'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * B. Laporan Stok & Gudang
     */

    /**
     * Laporan Posisi Stok Akhir (Stock Opname)
     */
    public function stockPosition(Request $request)
    {
        $query = InventoryLot::with(['inventoryType.plant', 'warehouse', 'bin'])
            ->where('current_stock', '>', 0);

        // Filter: Gudang
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter: Komoditas/Tanaman
        if ($request->filled('plant_id')) {
            $query->whereHas('inventoryType', function($q) use ($request) {
                $q->where('plant_id', $request->plant_id);
            });
        }

        // Filter: Tipe Inventaris
        if ($request->filled('inventory_type_id')) {
            $query->where('inventory_type_id', $request->inventory_type_id);
        }

        $lots = $query->orderBy('warehouse_id')
            ->orderBy('inventory_type_id')
            ->orderBy('production_id')
            ->paginate(50);

        // Calculate asset value
        $lots->getCollection()->transform(function ($lot) {
            // Using estimated_value_per_unit from inventory_type
            $unitPrice = $lot->inventoryType->estimated_value_per_unit ?? 0;
            $lot->asset_value = $lot->current_stock * $unitPrice;
            return $lot;
        });

        $warehouses = Warehouse::orderBy('name')->get();
        $inventoryTypes = \App\Models\InventoryType::with('plant')->orderBy('name')->get();
        $plants = Plant::with('type')->orderBy('name')->get();

        return view('reports.stock-position', compact('lots', 'warehouses', 'inventoryTypes', 'plants'));
    }

    /**
     * Laporan Mutasi Stok (Kartu Stok)
     */
    public function stockMutation(Request $request)
    {
        $query = InventoryTransaction::with(['inventoryType.plant', 'inventoryLot', 'warehouse', 'bin', 'user'])
            ->whereHas('inventoryType');

        // Filter: Dari Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter: Sampai Tanggal
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: Komoditas/Tanaman
        if ($request->filled('plant_id')) {
            $query->whereHas('inventoryType', function($q) use ($request) {
                $q->where('plant_id', $request->plant_id);
            });
        }

        // Filter: Lot/Batch
        if ($request->filled('inventory_lot_id')) {
            $query->where('inventory_lot_id', $request->inventory_lot_id);
        }

        // Filter: Tipe Inventaris
        if ($request->filled('inventory_type_id')) {
            $query->where('inventory_type_id', $request->inventory_type_id);
        }

        // Filter: Gudang
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate running balance per lot
        $balances = [];
        $transactions->getCollection()->transform(function ($transaction) use (&$balances) {
            $lotId = $transaction->inventory_lot_id ?? 'general';
            if (!isset($balances[$lotId])) {
                $balances[$lotId] = 0;
            }
            
            // Determine if transaction is addition or subtraction
            $isAddition = in_array($transaction->transaction_type, ['stok_masuk', 'penyesuaian_tambah', 'pindah_lokasi']);
            if ($isAddition) {
                $balances[$lotId] += abs($transaction->quantity);
            } else {
                $balances[$lotId] -= abs($transaction->quantity);
            }
            
            $transaction->balance = $balances[$lotId];
            return $transaction;
        });

        $lots = InventoryLot::with('inventoryType')->orderBy('production_id')->get();
        $inventoryTypes = \App\Models\InventoryType::with('plant')->orderBy('name')->get();
        $plants = Plant::with('type')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('reports.stock-mutation', compact('transactions', 'lots', 'inventoryTypes', 'plants', 'warehouses'));
    }

    /**
     * C. Laporan Penjualan & Distribusi
     */

    /**
     * Laporan Rekapitulasi Penjualan
     */
    public function sales(Request $request)
    {
        // Check if export requested
        if ($request->has('export')) {
            return $this->exportSales($request);
        }

        $query = Sale::with([
            'user', 
            'items.inventoryType.plant',
            'items.inventoryLot.warehouse'
        ]);

        // Filter: Tahun
        if ($request->filled('year')) {
            $query->whereYear('sale_date', $request->year);
        }

        // Filter: Dari Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        // Filter: Sampai Tanggal
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Filter: Komoditas (melalui inventory type)
        if ($request->filled('plant_id')) {
            $query->whereHas('items.inventoryType', function($q) use ($request) {
                $q->where('plant_id', $request->plant_id);
            });
        }

        // Filter: Lokasi Lahan (melalui inventory type -> seeds -> planting location)
        if ($request->filled('planting_location_id')) {
            $query->whereHas('items.inventoryType', function($q) use ($request) {
                $q->whereHas('seeds', function($q2) use ($request) {
                    $q2->where('planting_location_id', $request->planting_location_id);
                });
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(50);

        $sales->getCollection()->transform(function ($sale) {
            $sale->total_items = $sale->items->sum('quantity');
            return $sale;
        });

        // Get filter options
        $years = Sale::selectRaw('YEAR(sale_date) as year')
            ->whereNotNull('sale_date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $plants = Plant::with('type')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();

        return view('reports.sales', compact('sales', 'years', 'plants', 'locations'));
    }


    /**
     * D. Laporan Sertifikasi
     */

    /**
     * Rekap Status Sertifikasi
     */
    public function certification(Request $request)
    {
        // Check if export requested
        if ($request->has('export')) {
            return $this->exportCertification($request);
        }

        $query = Certification::with([
            'plant.type',
            'harvest.plant.type',
            'plantingLocation',
            'reports' => function($q) {
                $q->orderBy('report_date', 'desc');
            }
        ]);

        // Filter: Tahun
        if ($request->filled('year')) {
            $query->whereHas('reports', function($q) use ($request) {
                $q->whereYear('report_date', $request->year);
            })->orWhereYear('created_at', $request->year);
        }

        // Filter: Dari Tanggal
        if ($request->filled('date_from')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('reports', function($subQ) use ($request) {
                    $subQ->whereDate('report_date', '>=', $request->date_from);
                })->orWhereDate('created_at', '>=', $request->date_from);
            });
        }

        // Filter: Sampai Tanggal
        if ($request->filled('date_to')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('reports', function($subQ) use ($request) {
                    $subQ->whereDate('report_date', '<=', $request->date_to);
                })->orWhereDate('created_at', '<=', $request->date_to);
            });
        }

        // Filter: Komoditas
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        $certifications = $query->orderBy('created_at', 'desc')->paginate(50);

        // Transform certifications to add certification_status
        $certifications->getCollection()->transform(function ($certification) {
            $latestReport = $certification->reports->first();
            if ($latestReport) {
                if ($latestReport->conclusion === 'LULUS') {
                    $certification->certification_status = 'lulus';
                } elseif ($latestReport->conclusion === 'TIDAK LULUS') {
                    $certification->certification_status = 'tidak_lulus';
                } else {
                    $certification->certification_status = $certification->certification_status ?? 'dalam_proses';
                }
            } else {
                $certification->certification_status = $certification->certification_status ?? 'dalam_proses';
            }
            return $certification;
        });

        // Get filter options
        $years = Certification::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->merge(
                CertificationReport::selectRaw('YEAR(report_date) as year')
                    ->whereNotNull('report_date')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year')
            )
            ->unique()
            ->sortDesc()
            ->values();

        $plants = Plant::with('type')->orderBy('name')->get();

        return view('reports.certification', compact('certifications', 'years', 'plants'));
    }

    /**
     * Export Planting Harvest Report
     */
    private function exportPlantingHarvest(Request $request)
    {
        $query = Planting::with([
            'plant.type', 
            'harvest.certification'
        ])->whereNotNull('planted_at');

        // Apply same filters as main method
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

        $plantings = $query->orderBy('planted_at', 'desc')->get();

        // Transform data
        $plantings->transform(function ($planting) {
            $harvest = $planting->harvest;
            $certification = $harvest ? $harvest->certification : null;
            
            // Get area in hectares from planting area_ha field, fallback to location map_size
            $area = $planting->area_ha ?? ($planting->location->map_size ?? 0);
            
            $candidateSeed = 0;
            if ($harvest && $harvest->quantity > 0) {
                $unit = strtolower($harvest->unit ?? 'kg');
                $factors = ['kg' => 1, 'kilogram' => 1, 'gram' => 0.001, 'ton' => 1000, 'kuintal' => 100];
                $candidateSeed = $harvest->quantity * ($factors[$unit] ?? 1);
            }
            
            $certifiedSeed = 0;
            if ($certification && in_array($certification->certification_status, ['lulus', 'selesai'])) {
                $certifiedSeed = $candidateSeed;
            }
            
            $seedClass = $certification ? $certification->seed_class_requested : null;
            if ($seedClass) {
                $seedClassFormatted = $seedClass;
                if ($seedClass === 'BS') {
                    $seedClassFormatted = 'BS-BD';
                } elseif ($seedClass === 'BD') {
                    $seedClassFormatted = 'BD-BP';
                } elseif ($seedClass === 'BP') {
                    $seedClassFormatted = 'BP-BR';
                }
            } else {
                $seedClassFormatted = null;
            }
            
            $planting->area_ha = $area;
            $planting->candidate_seed_kg = round($candidateSeed, 0);
            $planting->certified_seed_kg = round($certifiedSeed, 0);
            $planting->seed_class = $seedClassFormatted;
            
            return $planting;
        });

        if ($request->get('export') === 'pdf') {
            return view('reports.exports.planting-harvest-pdf', compact('plantings'));
        } elseif ($request->get('export') === 'excel') {
            return $this->exportPlantingHarvestExcel($plantings);
        }

        return redirect()->back();
    }

    /**
     * Export Planting Harvest to Excel (CSV format)
     */
    private function exportPlantingHarvestExcel($plantings)
    {
        $filename = 'Laporan_Realisasi_Tanam_Panen_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($plantings) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row 1
            fputcsv($file, ['No', 'KOMODITI', 'KELAS BENIH', 'VARIETAS', 'LUAS (ha)', 'LOKASI KEGIATAN', 'TANAM', 'PANEN', 'CALON BENIH (kg)', 'BENIH BERSERTIFIKAT']);
            
            $rowNumber = 1;
            foreach ($plantings as $planting) {
                fputcsv($file, [
                    $rowNumber++,
                    $planting->plant->type->name ?? 'Lainnya',
                    $planting->seed_class ?? '-',
                    $planting->plant->name ?? '-',
                    $planting->area_ha > 0 ? number_format($planting->area_ha, 2, ',', '.') : '-',
                    $planting->location->name ?? '-',
                    $planting->planted_at ? $planting->planted_at->format('d-m-Y') : '-',
                    $planting->harvest && $planting->harvest->harvested_at ? $planting->harvest->harvested_at->format('d-m-Y') : '-',
                    $planting->candidate_seed_kg > 0 ? number_format($planting->candidate_seed_kg, 0, ',', '.') : '-',
                    $planting->certified_seed_kg > 0 ? number_format($planting->certified_seed_kg, 0, ',', '.') : '-',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export By Location Report
     */
    private function exportByLocation(Request $request, $selectedLocationId)
    {
        $plantingLocation = PlantingLocation::findOrFail($selectedLocationId);

        // Build queries with filters (same as main method)
        $plantingQuery = $plantingLocation->plantings()->with(['plant.type', 'harvest']);
        $treatmentQuery = $plantingLocation->treatments()->with(['planting.plant', 'responsiblePerson', 'editor']);
        $nutrientQuery = $plantingLocation->nutrients()->with(['planting.plant', 'editor']);
        $expenseQuery = $plantingLocation->expenses()->with(['planting.plant', 'responsiblePerson', 'treatment', 'nutrient', 'editor']);
        $taskQuery = $plantingLocation->tasks()->with(['assignedUser', 'createdByUser']);
        $noteQuery = $plantingLocation->notes()->with('user');
        $attachmentQuery = $plantingLocation->attachments()->with(['creator', 'editor']);

        // Apply filters
        if ($request->filled('year')) {
            $year = $request->year;
            $plantingQuery->whereYear('planted_at', $year);
            $treatmentQuery->whereYear('treatment_date', $year);
            $nutrientQuery->whereYear('application_date', $year);
            $expenseQuery->whereYear('expense_date', $year);
            $taskQuery->whereYear('due_date', $year);
            $noteQuery->whereYear('note_date', $year);
            $attachmentQuery->whereYear('attachment_date', $year);
        }

        if ($request->filled('date_from')) {
            $dateFrom = $request->date_from;
            $plantingQuery->whereDate('planted_at', '>=', $dateFrom);
            $treatmentQuery->whereDate('treatment_date', '>=', $dateFrom);
            $nutrientQuery->whereDate('application_date', '>=', $dateFrom);
            $expenseQuery->whereDate('expense_date', '>=', $dateFrom);
            $taskQuery->whereDate('due_date', '>=', $dateFrom);
            $noteQuery->whereDate('note_date', '>=', $dateFrom);
            $attachmentQuery->whereDate('attachment_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = $request->date_to;
            $plantingQuery->whereDate('planted_at', '<=', $dateTo);
            $treatmentQuery->whereDate('treatment_date', '<=', $dateTo);
            $nutrientQuery->whereDate('application_date', '<=', $dateTo);
            $expenseQuery->whereDate('expense_date', '<=', $dateTo);
            $taskQuery->whereDate('due_date', '<=', $dateTo);
            $noteQuery->whereDate('note_date', '<=', $dateTo);
            $attachmentQuery->whereDate('attachment_date', '<=', $dateTo);
        }

        if ($request->filled('plant_id')) {
            $plantId = $request->plant_id;
            $plantingQuery->where('plant_id', $plantId);
            $treatmentQuery->whereHas('planting', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
            $nutrientQuery->whereHas('planting', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
            $expenseQuery->whereHas('planting', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
        }

        // Get data
        $plantings = $plantingQuery->orderBy('planted_at', 'desc')->get();
        $treatments = $treatmentQuery->orderBy('treatment_date', 'desc')->get();
        $nutrients = $nutrientQuery->orderBy('application_date', 'desc')->get();
        $expenses = $expenseQuery->orderBy('expense_date', 'desc')->get();
        $tasks = $taskQuery->orderBy('due_date', 'desc')->get();
        $notes = $noteQuery->orderBy('note_date', 'desc')->get();
        $attachments = $attachmentQuery->orderBy('attachment_date', 'desc')->get();

        $totalExpenses = $expenses->sum('amount');

        if ($request->get('export') === 'pdf') {
            return view('reports.exports.by-location-pdf', compact(
                'plantingLocation',
                'plantings',
                'treatments',
                'nutrients',
                'expenses',
                'tasks',
                'notes',
                'attachments',
                'totalExpenses'
            ));
        } elseif ($request->get('export') === 'excel') {
            return $this->exportByLocationExcel(
                $plantingLocation,
                $plantings,
                $treatments,
                $nutrients,
                $expenses,
                $tasks,
                $notes,
                $attachments,
                $totalExpenses
            );
        }

        return redirect()->back();
    }

    /**
     * Export By Location to Excel (CSV format)
     */
    private function exportByLocationExcel($plantingLocation, $plantings, $treatments, $nutrients, $expenses, $tasks, $notes, $attachments, $totalExpenses)
    {
        $filename = 'Laporan_Per_Lokasi_Lahan_' . str_replace(' ', '_', $plantingLocation->name) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($plantingLocation, $plantings, $treatments, $nutrients, $expenses, $tasks, $notes, $attachments, $totalExpenses) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['No', 'Jenis Data', 'Tanggal', 'Judul/Nama', 'Deskripsi/Detail', 'Penanggung Jawab', 'Status', 'Biaya/Nilai']);
            
            $rowNumber = 1;
            
            // Plantings
            foreach ($plantings as $planting) {
                $harvest = $planting->harvest;
                fputcsv($file, [
                    $rowNumber++,
                    'Penanaman',
                    $planting->planted_at ? $planting->planted_at->format('d-m-Y') : '-',
                    $planting->plant->name ?? '-',
                    'Varietas: ' . ($planting->plant->variety ?? '-') . ($planting->bed_label ? ' | Bed: ' . $planting->bed_label : ''),
                    '-',
                    $harvest && $harvest->quantity > 0 ? 'Berhasil' : ($harvest ? 'Gagal' : 'Belum Panen'),
                    '-'
                ]);
            }
            
            // Treatments
            foreach ($treatments as $treatment) {
                fputcsv($file, [
                    $rowNumber++,
                    'Perawatan',
                    $treatment->treatment_date ? $treatment->treatment_date->format('d-m-Y') : '-',
                    $treatment->treatment_name ?? '-',
                    'Tipe: ' . ($treatment->treatment_type ?? '-') . ' | Metode: ' . ($treatment->application_method ?? '-'),
                    $treatment->responsiblePerson->name ?? '-',
                    '-',
                    number_format($treatment->total_cost ?? 0, 0, ',', '.')
                ]);
            }
            
            // Nutrients
            foreach ($nutrients as $nutrient) {
                fputcsv($file, [
                    $rowNumber++,
                    'Nutrisi',
                    $nutrient->application_date ? $nutrient->application_date->format('d-m-Y') : '-',
                    $nutrient->product_applied ?? '-',
                    'Metode: ' . ($nutrient->application_method ?? '-') . ' | Jumlah: ' . ($nutrient->amount_applied ?? '-') . ' ' . ($nutrient->unit ?? ''),
                    $nutrient->responsiblePerson->name ?? '-',
                    '-',
                    number_format($nutrient->total_cost ?? 0, 0, ',', '.')
                ]);
            }
            
            // Tasks
            foreach ($tasks as $task) {
                fputcsv($file, [
                    $rowNumber++,
                    'Tugas',
                    $task->due_date ? $task->due_date->format('d-m-Y') : '-',
                    $task->title ?? '-',
                    \Illuminate\Support\Str::limit($task->description ?? '-', 100),
                    $task->assignedUser->name ?? '-',
                    $task->new_status === 'selesai' ? 'Selesai' : ($task->new_status === 'dalam_progress' ? 'Dalam Progress' : 'Belum Selesai'),
                    '-'
                ]);
            }
            
            // Notes
            foreach ($notes as $note) {
                fputcsv($file, [
                    $rowNumber++,
                    'Catatan',
                    $note->note_date ? $note->note_date->format('d-m-Y') : '-',
                    $note->title ?? 'Catatan',
                    \Illuminate\Support\Str::limit($note->description ?? '-', 100),
                    $note->user->name ?? '-',
                    '-',
                    '-'
                ]);
            }
            
            // Attachments
            foreach ($attachments as $attachment) {
                fputcsv($file, [
                    $rowNumber++,
                    'Lampiran',
                    $attachment->attachment_date ? $attachment->attachment_date->format('d-m-Y') : '-',
                    $attachment->title ?? '-',
                    \Illuminate\Support\Str::limit($attachment->description ?? '-', 100),
                    $attachment->creator->name ?? '-',
                    '-',
                    '-'
                ]);
            }
            
            // Total row
            fputcsv($file, ['', '', '', '', '', '', 'Total Pengeluaran:', number_format($totalExpenses, 0, ',', '.')]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Sales Report
     */
    private function exportSales(Request $request)
    {
        $query = Sale::with([
            'user', 
            'items.inventoryType.plant',
            'items.inventoryLot.warehouse'
        ]);

        // Apply same filters as main method
        if ($request->filled('year')) {
            $query->whereYear('sale_date', $request->year);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        if ($request->filled('plant_id')) {
            $query->whereHas('items.inventoryType', function($q) use ($request) {
                $q->where('plant_id', $request->plant_id);
            });
        }

        if ($request->filled('planting_location_id')) {
            $query->whereHas('items.inventoryType', function($q) use ($request) {
                $q->whereHas('seeds', function($q2) use ($request) {
                    $q2->where('planting_location_id', $request->planting_location_id);
                });
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        $sales->transform(function ($sale) {
            $sale->total_items = $sale->items->sum('quantity');
            return $sale;
        });

        if ($request->get('export') === 'pdf') {
            return view('reports.exports.sales-pdf', compact('sales'));
        } elseif ($request->get('export') === 'excel') {
            return $this->exportSalesExcel($sales);
        }

        return redirect()->back();
    }

    /**
     * Export Sales to Excel (CSV format)
     */
    private function exportSalesExcel($sales)
    {
        $filename = 'Laporan_Penjualan_Distribusi_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'No. Struk',
                'Tanggal Penjualan',
                'Pembeli',
                'Kontak Pembeli',
                'Komoditas',
                'Jumlah Item',
                'Total Penjualan',
                'Metode Pembayaran',
                'Status Pembayaran',
                'Dicatat Oleh'
            ]);
            
            // Data
            $no = 1;
            foreach ($sales as $sale) {
                $uniquePlants = $sale->items->map(function($item) {
                    return $item->inventoryType->plant->name ?? ($item->inventoryType->name ?? 'N/A');
                })->unique()->values()->implode(', ');
                
                fputcsv($file, [
                    $no++,
                    $sale->receipt_number ?? '-',
                    $sale->sale_date ? $sale->sale_date->format('d-m-Y') : '-',
                    $sale->buyer_name ?? '-',
                    $sale->buyer_contact ?? '-',
                    $uniquePlants,
                    number_format($sale->total_items, 2),
                    number_format($sale->total_amount, 0, ',', '.'),
                    $sale->payment_method_label ?? '-',
                    $sale->payment_status_label ?? '-',
                    $sale->user->name ?? '-'
                ]);
            }
            
            // Total
            fputcsv($file, []);
            fputcsv($file, ['Total', '', '', '', '', '', '', number_format($sales->sum('total_amount'), 0, ',', '.'), '', '', '']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Certification Report
     */
    private function exportCertification(Request $request)
    {
        $query = Certification::with([
            'plant.type',
            'harvest.plant.type',
            'plantingLocation',
            'reports' => function($q) {
                $q->orderBy('report_date', 'desc');
            }
        ]);

        // Apply same filters as main method
        if ($request->filled('year')) {
            $query->whereHas('reports', function($q) use ($request) {
                $q->whereYear('report_date', $request->year);
            })->orWhereYear('created_at', $request->year);
        }

        if ($request->filled('date_from')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('reports', function($subQ) use ($request) {
                    $subQ->whereDate('report_date', '>=', $request->date_from);
                })->orWhereDate('created_at', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('reports', function($subQ) use ($request) {
                    $subQ->whereDate('report_date', '<=', $request->date_to);
                })->orWhereDate('created_at', '<=', $request->date_to);
            });
        }

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        $certifications = $query->orderBy('created_at', 'desc')->get();

        // Transform certifications to add certification_status
        $certifications->transform(function ($certification) {
            $latestReport = $certification->reports->first();
            if ($latestReport) {
                if ($latestReport->conclusion === 'LULUS') {
                    $certification->certification_status = 'lulus';
                } elseif ($latestReport->conclusion === 'TIDAK LULUS') {
                    $certification->certification_status = 'tidak_lulus';
                } else {
                    $certification->certification_status = $certification->certification_status ?? 'dalam_proses';
                }
            } else {
                $certification->certification_status = $certification->certification_status ?? 'dalam_proses';
            }
            return $certification;
        });

        if ($request->get('export') === 'pdf') {
            return view('reports.exports.certification-pdf', compact('certifications'));
        } elseif ($request->get('export') === 'excel') {
            return $this->exportCertificationExcel($certifications);
        }

        return redirect()->back();
    }

    /**
     * Export Certification to Excel (CSV format)
     */
    private function exportCertificationExcel($certifications)
    {
        $filename = 'Laporan_Rekap_Status_Sertifikasi_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($certifications) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'Komoditas/Tanaman',
                'Varietas',
                'Lokasi Lahan',
                'Kelas Benih Diminta',
                'Status Sertifikasi',
                'Tanggal Laporan Terakhir',
                'Kesimpulan Terakhir',
                'Jumlah Laporan'
            ]);
            
            // Data
            $no = 1;
            foreach ($certifications as $certification) {
                $latestReport = $certification->reports->first();
                $plant = $certification->plant ?? ($certification->harvest->plant ?? null);
                
                fputcsv($file, [
                    $no++,
                    $plant ? $plant->name : '-',
                    $plant && $plant->variety ? $plant->variety : '-',
                    $certification->plantingLocation ? $certification->plantingLocation->name : 
                        ($certification->harvest && $certification->harvest->location ? $certification->harvest->location->name : '-'),
                    $certification->seed_class_requested ?? '-',
                    $certification->status_label ?? '-',
                    $latestReport && $latestReport->report_date ? $latestReport->report_date->format('d-m-Y') : '-',
                    $latestReport && $latestReport->conclusion ? $latestReport->conclusion : '-',
                    $certification->reports->count()
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

