<?php

namespace App\Http\Controllers;

use App\Models\CertificationHarvest;
use App\Models\Planting;
use App\Models\PlantingPostHarvest;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\SaleItem;
use App\Models\CertificationReport;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\Warehouse;
use App\Models\PlantType;
use App\Models\PlantingField;
use App\Models\SeedSource;
use App\Models\Stock;
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
            'seedSource.variety.type',
            'field.plantingLocation',
            'plant.type',
            'certificationHarvests',
            'postHarvests',
        ])->whereNotNull('planted_at');

        $this->applyPlantingHarvestFilters($query, $request);
        $this->applyVarietyScope($query, $request, 'planting');

        $plantings = $query->get();

        // Transform data for Excel format
        $plantings->transform(function ($planting) {
            $harvest = $planting->certificationHarvests->sortByDesc('tgl_panen')->first();
            $latestTest = $planting->postHarvests->sortByDesc('uji_ke')->first();

            $area = $planting->field?->luas_ha ?? $planting->area_ha ?? ($planting->field?->plantingLocation?->map_size ?? 0);

            // Calon Benih (kg) - volume kotor panen dari pengawasan panen
            $candidateSeed = (float) ($harvest?->volume_kotor_panen_kg ?? 0);

            // Benih Bersertifikat (kg) - total hasil uji lab yang lulus
            $certifiedSeed = $latestTest && $latestTest->isLulus() ? (float) $latestTest->total_hasil_uji : 0;

            // Kelas Benih
            $seedClass = $planting->target_kelas;
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
            $planting->seed_source_label = $planting->seedSource?->origin_lot_number ?: ($planting->seedSource?->getKey() ?: '-');
            $planting->location_name = $planting->field?->plantingLocation?->name ?: ($planting->location?->name ?: '-');
            
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
        $commodities = PlantType::orderBy('name')->get();
        $seedSources = SeedSource::with('variety')->orderBy('origin_lot_number')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $years = Planting::selectRaw('YEAR(planted_at) as year')
            ->whereNotNull('planted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('reports.planting-harvest', compact('plantings', 'plants', 'commodities', 'seedSources', 'locations', 'years'));
    }

    /**
     * Laporan Per Lokasi Lahan
     */
    public function byLocation(Request $request)
    {
        // Get selected location
        $selectedLocationId = $request->input('planting_location_id');
        
        $filterMode = $request->input('filter_mode', $selectedLocationId ? 'location' : '');
        $locationRows = collect($request->input('locations', []));
        if ($selectedLocationId && $locationRows->isEmpty()) {
            $locationRows = collect([[
                'planting_location_id' => $selectedLocationId,
                'field_id' => $request->input('field_id'),
                'planting_ids' => $request->filled('planting_id') ? [$request->input('planting_id')] : [],
            ]]);
        }
        $hasLocationFilter = $locationRows->contains(fn ($row) => filled($row['planting_location_id'] ?? null));
        $hasVarietyFilter = $request->input('view_mode') === 'specific'
            && collect($request->input('filters', []))->contains(fn ($row) => filled($row['variety_id'] ?? null) || filled($row['commodity_id'] ?? null) || filled($row['category'] ?? null) || filled($row['seed_source_id'] ?? null));

        if ($filterMode === '' || ($filterMode === 'location' && ! $hasLocationFilter) || ($filterMode === 'variety' && ! $hasVarietyFilter && ! $request->boolean('submitted'))) {
            $locations = PlantingLocation::orderBy('name')->get();
            $plants = Plant::orderBy('name')->get();
            $commodities = PlantType::orderBy('category')->orderBy('name')->get();
            $years = Planting::selectRaw('YEAR(planted_at) as year')
                ->whereNotNull('planted_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

            return view('reports.by-location-select', compact('locations', 'plants', 'commodities', 'years'));
        }

        // Check if export requested
        if ($request->has('export')) {
            return $this->exportByLocation($request, $selectedLocationId);
        }

        $plantingsQuery = Planting::with([
            'seedSource.variety.type',
            'field.plantingLocation',
            'reports.officer',
            'certificationApplications',
            'certificationInspections',
            'certificationFieldSamples',
            'certificationHarvests',
            'certificationPcbs',
            'postHarvests.certificationReports',
        ]);
        if ($filterMode === 'location') {
            $plantingsQuery->where(function ($q) use ($locationRows) {
                foreach ($locationRows as $row) {
                    if (empty($row['planting_location_id'])) {
                        continue;
                    }
                    $q->orWhere(function ($inner) use ($row) {
                        $inner->whereHas('field', fn ($f) => $f->where('planting_location_id', $row['planting_location_id']));
                        if (! empty($row['field_id'])) {
                            $inner->where('planting_field_id', $row['field_id']);
                        }
                        $plantingIds = collect($row['planting_ids'] ?? [])->filter()->values();
                        if ($plantingIds->isNotEmpty()) {
                            $inner->whereIn('planting_production_id', $plantingIds);
                        }
                    });
                }
            });
        } else {
            $this->applyVarietyScope($plantingsQuery, $request, 'planting');
        }
        if ($request->filled('year')) {
            $plantingsQuery->whereYear('planted_at', $request->year);
        }
        if ($request->filled('date_from')) {
            $plantingsQuery->whereDate('planted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $plantingsQuery->whereDate('planted_at', '<=', $request->date_to);
        }
        $plantings = $plantingsQuery->orderByDesc('planted_at')->get();
        $plantingLocations = $plantings->map(fn ($p) => $p->field?->plantingLocation)->filter()->unique('planting_location_id')->values();
        $plantingLocation = $plantingLocations->first() ?: PlantingLocation::find($selectedLocationId);
        $fields = $plantingLocation?->fields ?? collect();

        $timeline = collect();
        foreach ($plantings as $planting) {
            foreach ($planting->reports as $item) {
                $timeline->push(['at' => $item->activity_date ?? $item->created_at, 'type' => 'Log harian', 'title' => $item->title ?: 'Laporan harian', 'variety' => $planting->seedSource?->variety?->variety]);
            }
            foreach ($planting->certificationTimeline() as $entry) {
                $timeline->push([
                    'at' => $entry['at'],
                    'type' => 'Sertifikasi',
                    'title' => $entry['record']->reportTitle(),
                    'variety' => $planting->seedSource?->variety?->variety,
                ]);
            }
            foreach ($planting->postHarvests as $item) {
                $timeline->push(['at' => $item->tgl_selesai_uji ?? $item->created_at, 'type' => 'Pasca panen', 'title' => 'Hasil uji lab '.$item->nomor_lot, 'variety' => $planting->seedSource?->variety?->variety]);
                foreach ($item->certificationReports as $label) {
                    $timeline->push(['at' => $label->created_at, 'type' => 'Label', 'title' => 'Label '.$label->id_label_rilis, 'variety' => $planting->seedSource?->variety?->variety]);
                }
                foreach (Stock::where('planting_post_harvest_id', $item->getKey())->get() as $stock) {
                    $timeline->push(['at' => $stock->created_at, 'type' => 'Stok', 'title' => ($stock->no_label_resmi ?: 'Lot').' — '.$stock->stok_saat_ini, 'variety' => $planting->seedSource?->variety?->variety]);
                }
            }
        }
        $timeline = $timeline->sortByDesc('at')->values();

        $treatments = collect();
        $nutrients = collect();
        $expenses = collect();
        $tasks = collect();
        $notes = collect();
        $attachments = $plantingLocation
            ? $plantingLocation->attachments()->with('creator')->orderByDesc('attachment_date')->get()
            : collect();
        $totalPlantings = $plantings->count();
        $totalHarvests = $plantings->sum(fn ($p) => $p->certificationHarvests->count());
        $totalExpenses = 0;
        $totalTreatments = 0;
        $totalNutrients = 0;
        $totalTasks = 0;
        $completedTasks = 0;
        $totalNotes = 0;
        $totalAttachments = $attachments->count();
        $plants = Plant::orderBy('name')->get();
        $commodities = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $years = Planting::selectRaw('YEAR(planted_at) as year')->whereNotNull('planted_at')->distinct()->orderBy('year', 'desc')->pluck('year');
        $allPlantingsForLocation = $plantings;

        return view('reports.by-location', compact(
            'plantingLocation',
            'plantingLocations',
            'plantings',
            'fields',
            'timeline',
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
            'commodities',
            'locations',
            'years',
            'allPlantingsForLocation'
        ));
    }

    /**
     * Laporan Penggunaan Sarana Produksi (Pengeluaran)
     */
    public function productionSupplies()
    {
        return redirect()->route('reports.index')->with('info', 'Laporan penggunaan sarana produksi telah dihapus.');
    }

    /**
     * Export Production Supplies Report
     */
    private function exportProductionSupplies(Request $request)
    {
        return redirect()->route('reports.index');
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
                $plant = $expense->planting && $expense->planting->plant ? $expense->planting->plant : null;
                $locationName = $expense->planting && $expense->planting->location ? $expense->planting->location->name : '-';
                
                fputcsv($file, [
                    $rowNumber++,
                    $expense->expense_date ? $expense->expense_date->format('d-m-Y') : '-',
                    $expense->expense_name ?? '-',
                    $expenseTypes[$expense->expense_type] ?? '-',
                    $plant ? $plant->name : '-',
                    $locationName,
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
        $query = \App\Models\Stock::with(['plant.satuanStok', 'rack.warehouse', 'certificationReport'])
            ->where('stok_saat_ini', '>', 0);

        // Filter: Gudang
        if ($request->filled('warehouse_id')) {
            $query->whereHas('rack', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter: Komoditas/Varietas benih
        if ($request->filled('plant_id')) {
            $query->where('seed_varieties_id', $request->plant_id);
        }

        // Filter kompatibilitas lama: tipe inventaris = varietas benih
        if ($request->filled('inventory_type_id')) {
            $query->where('seed_varieties_id', $request->inventory_type_id);
        }

        $this->applyVarietyScope($query, $request, 'stock');
        $lots = $query->orderBy('rak_gudang_id')
            ->orderBy('seed_varieties_id')
            ->orderBy('no_label_resmi')
            ->paginate(50);

        // Nilai aset = stok saat ini x harga jual varietas
        $lots->getCollection()->transform(function ($lot) {
            $unitPrice = (float) ($lot->plant?->harga_jual ?? 0);
            $lot->asset_value = (float) $lot->stok_saat_ini * $unitPrice;
            return $lot;
        });

        $warehouses = Warehouse::orderBy('name')->get();
        $plants = Plant::with('type')->orderBy('name')->get();
        $commodities = PlantType::orderBy('category')->orderBy('name')->get();
        $inventoryTypes = $plants;

        return view('reports.stock-position', compact('lots', 'warehouses', 'inventoryTypes', 'plants', 'commodities'));
    }

    /**
     * Laporan Mutasi Stok (Kartu Stok)
     */
    public function stockMutation(Request $request)
    {
        $query = \App\Models\StockHistory::with(['plant', 'stock.rack.warehouse', 'packaging.rack.warehouse', 'user'])
            ->whereNotNull('transaction_type');

        // Filter: Dari Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter: Sampai Tanggal
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: Komoditas
        if ($request->filled('commodity_id')) {
            $query->whereHas('plant', fn ($q) => $q->where('seed_commodity_id', $request->commodity_id));
        }

        // Filter: Varietas benih
        if ($request->filled('variety_id')) {
            $query->where('seed_varieties_id', $request->variety_id);
        } elseif ($request->filled('plant_id')) {
            $query->where('seed_varieties_id', $request->plant_id);
        }

        // Filter: Gudang
        if ($request->filled('warehouse_id')) {
            $query->whereHas('stock.rack', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter: ID rak gudang (internal id)
        if ($request->filled('bin_id')) {
            $query->whereHas('stock.rack', function ($q) use ($request) {
                $q->where('warehouse_bin_id', $request->bin_id);
            });
        } elseif ($request->filled('bin_internal_id')) {
            $query->whereHas('stock.rack', function ($q) use ($request) {
                $q->where('internal_id', $request->bin_internal_id);
            });
        }
        $this->applyVarietyScope($query, $request, 'history');

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate running balance per lot
        $balances = [];
        $transactions->getCollection()->transform(function ($transaction) use (&$balances) {
            $lotId = $transaction->stock_id ?? 'general';
            if (!isset($balances[$lotId])) {
                $balances[$lotId] = 0;
            }
            
        // Determine if transaction is addition or subtraction
        $isAddition = in_array($transaction->transaction_type, ['mendaftarkan_stok', 'stok_masuk', 'penyesuaian_tambah', 'pindah_lokasi']);
            if ($isAddition) {
                $balances[$lotId] += abs($transaction->quantity);
            } else {
                $balances[$lotId] -= abs($transaction->quantity);
            }
            
            $transaction->balance = $balances[$lotId];
            return $transaction;
        });

        $plants = Plant::with('type')->orderBy('variety')->orderBy('name')->get();
        $commodities = PlantType::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $bins = \App\Models\Bin::query()
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->orderBy('internal_id')
            ->orderBy('name')
            ->get();

        return view('reports.stock-mutation', compact('transactions', 'plants', 'commodities', 'warehouses', 'bins'));
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

        $baseQuery = SaleItem::with(['user', 'packaging.stock.plant.type', 'packaging.rack.warehouse']);
        $baseQuery->whereNotNull('receipt_number');

        if ($request->filled('year')) {
            $baseQuery->whereYear('sale_date', $request->year);
        }
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('sale_date', '<=', $request->date_to);
        }
        $this->applySaleCatalogFilters($baseQuery, $request);
        $this->applyVarietyScope($baseQuery, $request, 'sale');
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $baseQuery->where(function ($query) use ($q) {
                $query->where('buyer_name', 'like', '%'.$q.'%')
                    ->orWhere('buyer_contact', 'like', '%'.$q.'%')
                    ->orWhere('receipt_number', 'like', '%'.$q.'%')
                    ->orWhereHas('seedRequest', fn ($s) => $s->where('organization', 'like', '%'.$q.'%')->orWhere('buyer_name', 'like', '%'.$q.'%')->orWhere('buyer_contact', 'like', '%'.$q.'%'));
            });
        }

        $receiptNumbers = (clone $baseQuery)->select('receipt_number')->distinct()->pluck('receipt_number');
        $orderedIds = SaleItem::whereIn('receipt_number', $receiptNumbers)
            ->selectRaw('receipt_number, MAX(sale_date) as max_date')
            ->groupBy('receipt_number')
            ->orderBy('max_date', 'desc')
            ->pluck('receipt_number');

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $saleIdsPage = $orderedIds->forPage($page, $perPage)->values();

        $items = SaleItem::whereIn('receipt_number', $saleIdsPage)
            ->with(['user', 'packaging.stock.plant.type', 'packaging.rack.warehouse'])
            ->orderBy('sale_date', 'desc')
            ->orderBy('sale_item_id')
            ->get();
        $grouped = $items->groupBy('receipt_number');
        $salesCollection = $grouped->map(function ($itemRows) {
            $first = $itemRows->first();
            $first->setRelation('items', $itemRows);
            $first->total_items = $itemRows->sum('quantity');
            $first->label_numbers = $itemRows->map(fn ($item) => $item->packaging?->no_label_seri)->filter()->values();
            $first->sold_volume = $itemRows->groupBy(fn ($item) => $item->unit ?: '-')->map->sum('quantity');
            return $first;
        })->values();

        $sales = new LengthAwarePaginator(
            $salesCollection,
            $orderedIds->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get filter options
        $years = SaleItem::selectRaw('YEAR(sale_date) as year')
            ->whereNotNull('sale_date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $plants = Plant::with('type')->orderBy('variety')->orderBy('name')->get();
        $commodities = PlantType::orderBy('name')->get();

        return view('reports.sales', compact('sales', 'years', 'plants', 'commodities'));
    }

    public function distribution(Request $request)
    {
        $query = SaleItem::with([
            'packaging.stock.plant.type',
            'seedRequest.items.plant.type',
            'user',
        ])->whereNotNull('planned_gps')->where('planned_gps', '!=', '');

        if ($request->filled('year')) {
            $query->whereYear('sale_date', $request->year);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }
        $this->applySaleCatalogFilters($query, $request);
        $this->applyVarietyScope($query, $request, 'sale');

        $items = $query->orderByDesc('sale_date')->orderBy('sale_item_id')->get();

        $points = [];
        $rows = collect();
        foreach ($items as $item) {
            $gps = SaleItem::parseGps($item->planned_gps);
            if (! $gps) {
                continue;
            }

            $points[] = [
                'lat' => $gps['lat'],
                'lng' => $gps['lng'],
                'qty' => (float) $item->quantity,
                'unit' => $item->unit ?: 'kg',
                'buyer' => $item->buyer_name,
                'organization' => $item->displayOrganization() ?: '-',
                'location' => $item->planned_location_name ?: '-',
                'plant' => $item->displayPlantName(),
                'receipt' => $item->receipt_number,
                'date' => $item->sale_date?->format('d M Y'),
                'url' => route('sales.show', $item),
            ];
            $rows->push($item);
        }

        $years = SaleItem::query()
            ->selectRaw('YEAR(sale_date) as year')
            ->whereNotNull('sale_date')
            ->whereNotNull('planned_gps')
            ->where('planned_gps', '!=', '')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $plants = Plant::with('type')->orderBy('variety')->orderBy('name')->get();
        $commodities = PlantType::orderBy('name')->get();
        $totalQty = collect($points)->sum('qty');
        $receiptCount = collect($points)->pluck('receipt')->unique()->count();

        return view('reports.distribution', compact(
            'points',
            'rows',
            'years',
            'plants',
            'commodities',
            'totalQty',
            'receiptCount'
        ));
    }


    /**
     * D. Laporan Sertifikasi
     */

    /**
     * Rekap Status Sertifikasi
     */
    public function certification(Request $request)
    {
        $query = PlantingPostHarvest::with([
            'planting.seedSource.variety.type',
            'planting.field.plantingLocation',
            'certificationReports',
            'stock',
        ])->orderByDesc('tgl_selesai_uji');

        if ($request->filled('year')) {
            $query->whereYear('tgl_selesai_uji', $request->year);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_selesai_uji', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_selesai_uji', '<=', $request->date_to);
        }
        if ($request->filled('commodity_id')) {
            $commodityId = $request->commodity_id;
            $query->whereHas('planting.seedSource.variety', fn ($p) => $p->where('seed_commodity_id', $commodityId));
        }
        if ($request->filled('variety_id')) {
            $varietyId = $request->variety_id;
            $query->whereHas('planting.seedSource', fn ($s) => $s->where('seed_varieties_id', $varietyId));
        }
        $this->applyVarietyScope($query, $request, 'post_harvest');

        $reports = $query->paginate(50);
        $commodities = PlantType::orderBy('name')->get();
        $plants = Plant::orderBy('name')->get();
        $years = PlantingPostHarvest::selectRaw('YEAR(tgl_selesai_uji) as year')
            ->whereNotNull('tgl_selesai_uji')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('reports.certification', compact('reports', 'commodities', 'plants', 'years'));
    }

    /**
     * Export Planting Harvest Report
     */
    private function exportPlantingHarvest(Request $request)
    {
        $query = Planting::with([
            'seedSource.variety.type',
            'field.plantingLocation',
            'plant.type',
            'certificationHarvests',
            'postHarvests',
        ])->whereNotNull('planted_at');

        $this->applyPlantingHarvestFilters($query, $request);

        $plantings = $query->orderBy('planted_at', 'desc')->get();

        // Transform data
        $plantings->transform(function ($planting) {
            $harvest = $planting->certificationHarvests->sortByDesc('tgl_panen')->first();
            $latestTest = $planting->postHarvests->sortByDesc('uji_ke')->first();

            $area = $planting->field?->luas_ha ?? $planting->area_ha ?? ($planting->field?->plantingLocation?->map_size ?? 0);

            $candidateSeed = (float) ($harvest?->volume_kotor_panen_kg ?? 0);
            $certifiedSeed = $latestTest && $latestTest->isLulus() ? (float) $latestTest->total_hasil_uji : 0;

            $seedClass = $planting->target_kelas;
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
                    optional($planting->certificationHarvests->sortByDesc('tgl_panen')->first()?->tgl_panen)->format('d-m-Y') ?: '-',
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
        $plantingsQuery = Planting::with(['seedSource.variety', 'field', 'certificationHarvests', 'reports', 'postHarvests'])
            ->whereHas('field', fn ($q) => $q->where('planting_location_id', $plantingLocation->getKey()));
        if ($request->filled('field_id')) {
            $plantingsQuery->where('planting_field_id', $request->field_id);
        }
        if ($request->filled('planting_id')) {
            $plantingsQuery->whereKey($request->planting_id);
        }
        $plantings = $plantingsQuery->orderByDesc('planted_at')->get();
        $attachments = $plantingLocation->attachments()->with('creator')->get();

        $filename = 'Laporan_Produksi_' . str_replace(' ', '_', $plantingLocation->name) . '_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($plantingLocation, $plantings, $attachments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Lokasi Penanaman', $plantingLocation->name]);
            fputcsv($file, ['No', 'Jenis', 'Tanggal', 'Varietas', 'Judul']);
            $row = 1;
            foreach ($plantings as $planting) {
                $variety = $planting->seedSource?->variety?->variety ?: ($planting->plant?->name ?: '-');
                fputcsv($file, [$row++, 'Produksi', optional($planting->planted_at)->format('d-m-Y'), $variety, $planting->planting_batch_number ?: $planting->getKey()]);
                foreach ($planting->certificationHarvests as $harvest) {
                    fputcsv($file, [$row++, 'Panen', optional($harvest->tgl_panen)->format('d-m-Y'), $variety, $harvest->no_segel_sementara ?: 'Pengawasan panen']);
                }
                foreach ($planting->postHarvests as $test) {
                    fputcsv($file, [$row++, 'Hasil uji lab', optional($test->tgl_selesai_uji)->format('d-m-Y'), $variety, $test->nomor_lot]);
                }
            }
            foreach ($attachments as $attachment) {
                fputcsv($file, [$row++, 'Lampiran', optional($attachment->attachment_date)->format('d-m-Y'), '-', $attachment->title]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
                fputcsv($file, [
                    $rowNumber++,
                    'Penanaman',
                    $planting->planted_at ? $planting->planted_at->format('d-m-Y') : '-',
                    $planting->plant->name ?? '-',
                    'Varietas: ' . ($planting->plant->variety ?? '-') . ($planting->bed_label ? ' | Bed: ' . $planting->bed_label : ''),
                    '-',
                    $planting->statusLabel(),
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
        $query = SaleItem::with(['user', 'packaging.stock.plant.type', 'packaging.rack.warehouse']);
        $query->whereNotNull('receipt_number');

        if ($request->filled('year')) {
            $query->whereYear('sale_date', $request->year);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }
        $this->applySaleCatalogFilters($query, $request);

        $items = $query->orderBy('sale_date', 'desc')->orderBy('sale_item_id')->get();
        $sales = $items->groupBy('receipt_number')->map(function ($itemRows) {
            $first = $itemRows->first();
            $first->setRelation('items', $itemRows);
            $first->total_items = $itemRows->sum('quantity');
            return $first;
        })->values();

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
                    return $item->packaging?->stock?->plant?->name ?? 'N/A';
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

    public function varietiesJson(Request $request)
    {
        $query = Plant::query()->orderBy('variety');
        if ($request->filled('commodity_id')) {
            $id = $request->commodity_id;
            $query->where(function ($q) use ($id) {
                $q->where('seed_commodity_id', $id);
            });
        }

        return response()->json($query->get(['seed_varieties_id', 'name', 'variety']));
    }

    public function fieldsJson(Request $request)
    {
        $fields = PlantingField::query()
            ->when($request->filled('planting_location_id'), fn ($q) => $q->where('planting_location_id', $request->planting_location_id))
            ->orderBy('kode_lahan')
            ->get(['id', 'kode_lahan', 'planting_location_id']);

        return response()->json($fields);
    }

    public function plantingsJson(Request $request)
    {
        $query = Planting::with(['seedSource.variety'])
            ->when($request->filled('field_id'), fn ($q) => $q->where('planting_field_id', $request->field_id))
            ->when($request->filled('planting_location_id'), fn ($q) => $q->whereHas('field', fn ($f) => $f->where('planting_location_id', $request->planting_location_id)))
            ->orderByDesc('planted_at');

        return response()->json($query->get()->map(fn ($p) => [
            'id' => $p->getKey(),
            'label' => ($p->planting_batch_number ?: $p->getKey()).'-'.($p->seedSource?->variety?->variety ?: $p->plant?->name ?: 'Produksi'),
        ]));
    }

    public function catalogJson(Request $request)
    {
        $commodities = PlantType::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->orderBy('name')
            ->get(['seed_commodity_id', 'name', 'category']);
        $plants = Plant::query()
            ->when($request->filled('commodity_id'), fn ($q) => $q->where('seed_commodity_id', $request->commodity_id))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('type', fn ($t) => $t->where('category', $request->category)))
            ->orderBy('variety')
            ->get(['seed_varieties_id', 'name', 'variety', 'seed_commodity_id']);
        $sources = SeedSource::query()
            ->when($request->filled('variety_id'), fn ($q) => $q->where('seed_varieties_id', $request->variety_id))
            ->orderBy('origin_lot_number')
            ->get(['seed_source_id', 'origin_lot_number', 'seed_varieties_id']);

        return response()->json([
            'commodities' => $commodities,
            'plants' => $plants,
            'sources' => $sources,
        ]);
    }

    private function applyVarietyScope($query, Request $request, string $type)
    {
        if ($request->input('view_mode', 'all') !== 'specific') {
            return $query;
        }

        $rows = collect($request->input('filters', []))->filter(function ($row) {
            return filled($row['category'] ?? null)
                || filled($row['commodity_id'] ?? null)
                || filled($row['variety_id'] ?? null)
                || filled($row['seed_source_id'] ?? null);
        });
        if ($rows->isEmpty()) {
            return $query;
        }

        return $query->where(function ($q) use ($rows, $type) {
            foreach ($rows as $row) {
                $q->orWhere(function ($inner) use ($row, $type) {
                    $this->applyCatalogRow($inner, $row, $type);
                });
            }
        });
    }

    private function applyCatalogRow($query, array $row, string $type): void
    {
        if ($type === 'planting' || $type === 'post_harvest') {
            $plantQuery = $type === 'planting' ? $query : $query->whereHas('planting', function ($p) use ($row) {
                $this->constrainPlanting($p, $row);
            });
            if ($type === 'planting') {
                $this->constrainPlanting($query, $row);
            }

            return;
        }

        if ($type === 'sale') {
            $query->where(function ($outer) use ($row) {
                $outer->whereHas('packaging.stock', function ($stock) use ($row) {
                    $this->constrainStock($stock, $row);
                })->orWhereHas('seedRequest.items.plant', function ($plant) use ($row) {
                    if (! empty($row['variety_id'])) {
                        $plant->where('seed_varieties_id', $row['variety_id']);
                    } elseif (! empty($row['commodity_id'])) {
                        $plant->where('seed_commodity_id', $row['commodity_id']);
                    } elseif (! empty($row['category'])) {
                        $plant->whereHas('type', fn ($t) => $t->where('category', $row['category']));
                    }
                });
            });

            return;
        }

        if ($type === 'history') {
            if (! empty($row['variety_id'])) {
                $query->where('seed_varieties_id', $row['variety_id']);
            } elseif (! empty($row['commodity_id'])) {
                $query->whereHas('plant', fn ($p) => $p->where('seed_commodity_id', $row['commodity_id']));
            } elseif (! empty($row['category'])) {
                $query->whereHas('plant.type', fn ($p) => $p->where('category', $row['category']));
            }
            if (! empty($row['seed_source_id'])) {
                $query->whereHas('stock.postHarvest.planting', fn ($p) => $p->where('seed_source_id', $row['seed_source_id']));
            }

            return;
        }

        $this->constrainStock($query, $row);
    }

    private function constrainPlanting($query, array $row): void
    {
        if (! empty($row['seed_source_id'])) {
            $query->where('seed_source_id', $row['seed_source_id']);
        }
        if (! empty($row['variety_id'])) {
            $query->whereHas('seedSource', fn ($s) => $s->where('seed_varieties_id', $row['variety_id']));
        } elseif (! empty($row['commodity_id'])) {
            $query->whereHas('seedSource.variety', fn ($s) => $s->where('seed_commodity_id', $row['commodity_id']));
        } elseif (! empty($row['category'])) {
            $query->whereHas('seedSource.variety.type', fn ($s) => $s->where('category', $row['category']));
        }
    }

    private function constrainStock($query, array $row): void
    {
        if (! empty($row['variety_id'])) {
            $query->where('seed_varieties_id', $row['variety_id']);
        } elseif (! empty($row['commodity_id'])) {
            $query->whereHas('plant', fn ($p) => $p->where('seed_commodity_id', $row['commodity_id']));
        } elseif (! empty($row['category'])) {
            $query->whereHas('plant.type', fn ($p) => $p->where('category', $row['category']));
        }
        if (! empty($row['seed_source_id'])) {
            $query->whereHas('postHarvest.planting', fn ($p) => $p->where('seed_source_id', $row['seed_source_id']));
        }
    }

    private function applyPlantingHarvestFilters($query, Request $request)
    {
        if ($request->filled('year')) {
            $query->whereYear('planted_at', $request->year);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('planted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('planted_at', '<=', $request->date_to);
        }
        if ($request->filled('commodity_id')) {
            $query->whereHas('seedSource.variety', fn ($q) => $q->where('seed_commodity_id', $request->commodity_id));
        }
        if ($request->filled('variety_id')) {
            $query->forPlant($request->variety_id);
        } elseif ($request->filled('plant_id')) {
            $query->forPlant($request->plant_id);
        }
        if ($request->filled('seed_source_id')) {
            $query->where('seed_source_id', $request->seed_source_id);
        }
        if ($request->filled('planting_location_id')) {
            $query->whereHas('field', fn ($q) => $q->where('planting_location_id', $request->planting_location_id));
        }

        return $query;
    }

    private function applySaleCatalogFilters($query, Request $request)
    {
        $commodityIds = collect((array) $request->input('commodity_ids', []))
            ->when($request->filled('commodity_id'), fn ($c) => $c->push($request->commodity_id))
            ->filter()
            ->unique()
            ->values();
        $varietyIds = collect((array) $request->input('variety_ids', []))
            ->when($request->filled('variety_id'), fn ($c) => $c->push($request->variety_id))
            ->when($request->filled('plant_id'), fn ($c) => $c->push($request->plant_id))
            ->filter()
            ->unique()
            ->values();

        if ($commodityIds->isEmpty() && $varietyIds->isEmpty()) {
            return $query;
        }

        $matchPlant = function ($q) use ($commodityIds, $varietyIds) {
            $q->where(function ($inner) use ($commodityIds, $varietyIds) {
                if ($varietyIds->isNotEmpty()) {
                    $inner->whereIn('seed_varieties_id', $varietyIds);
                }
                if ($commodityIds->isNotEmpty()) {
                    if ($varietyIds->isNotEmpty()) {
                        $inner->orWhereIn('seed_commodity_id', $commodityIds);
                    } else {
                        $inner->whereIn('seed_commodity_id', $commodityIds);
                    }
                }
            });
        };

        return $query->where(function ($outer) use ($matchPlant) {
            $outer->whereHas('packaging.stock.plant', $matchPlant)
                ->orWhereHas('seedRequest.items.plant', $matchPlant);
        });
    }
}

