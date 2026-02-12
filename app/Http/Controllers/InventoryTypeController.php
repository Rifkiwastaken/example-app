<?php

namespace App\Http\Controllers;

use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use App\Models\InventoryNote;
use App\Models\InventoryPhoto;
use App\Models\InventoryTypeSeed;
use App\Models\SeedHistory;
use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\CertificationReport;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua tanaman ("Tanaman Saya") sebagai basis data stok benih
        $plants = Plant::with('type')->orderBy('name')->get();

        // Ambil tipe stok benih per plant_id untuk pencocokan cepat
        $inventoryTypesByPlant = InventoryType::with('seeds')->get()->keyBy('plant_id');

        // Lot per tipe (untuk hitung stok tampilan = ikuti gudang)
        $lotsByType = InventoryLot::whereIn('inventory_type_id', $inventoryTypesByPlant->pluck('inventory_type_id'))
            ->whereNotNull('warehouse_id')
            ->whereNotNull('bin_id')
            ->get()
            ->groupBy('inventory_type_id');

        foreach ($inventoryTypesByPlant as $type) {
            $type->total_stock_from_seeds = $type->seeds()->sum('total_seed_quantity') ?? 0;
            // Total stok tampilan: sama dengan halaman detail (ikuti stok gudang jika seed sudah di gudang)
            $lots = $lotsByType->get($type->inventory_type_id, collect());
            $displayTotal = 0;
            foreach ($type->seeds as $seed) {
                $sn = $seed->storage_number ? trim((string) $seed->storage_number) : null;
                $matchedLot = $lots->first(function ($lot) use ($sn) {
                    $pid = $lot->production_id ? trim((string) $lot->production_id) : null;
                    return $sn && $pid && $pid === $sn;
                });
                if ($matchedLot !== null) {
                    $displayTotal += (float) $matchedLot->current_stock;
                } else {
                    $displayTotal += (float) ($seed->total_seed_quantity ?? $seed->quantity ?? 0);
                }
            }
            $type->display_total_stock = $displayTotal;
        }

        return view('warehouse.seed-stock.index', compact('plants', 'inventoryTypesByPlant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all plants from "Tanaman Saya"
        $plants = Plant::with('type')->orderBy('name')->get();
        
        // Get all users for responsible person dropdown
        $users = User::orderBy('name')->get();
        
        // Get all warehouses with bins for storage location selection
        $warehouses = Warehouse::with('bins')->get();
        
        return view('warehouse.seed-stock.create', compact('plants', 'users', 'warehouses'));
    }

    /**
     * Store step 1 data in session and proceed to step 2.
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'plant_id' => 'required|exists:plants,plant_id',
            'responsible_person_id' => 'nullable|exists:users,user_id',
            'sku' => 'required|string|max:255|unique:inventory_types,sku',
            'unit' => 'required|string|in:kg,ton,kantong,unit,polybag,pcs',
            'estimated_value_per_unit' => 'nullable|numeric|min:0',
            'estimated_kg_per_unit' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'low_stock_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'low_stock_email' => 'nullable|email',
            'description' => 'nullable|string',
        ]);

        // Get plant data and set name and category from plant
        $plant = Plant::with('type')->findOrFail($request->plant_id);
        $request->merge([
            'name' => $plant->name . ($plant->variety ? ' - ' . $plant->variety : ''),
            'category' => $plant->type?->name ?: $plant->name,
        ]);

        // Store step 1 data in session
        $request->session()->put('inventory_type_step1', $request->all());

        return redirect()->route('seed-stock.create-step2');
    }

    /**
     * Show step 2 form (warehouse selection).
     */
    public function createStep2(Request $request)
    {
        if (!$request->session()->has('inventory_type_step1')) {
            return redirect()->route('seed-stock.create');
        }

        $warehouses = Warehouse::with('bins')->get();
        $step1Data = $request->session()->get('inventory_type_step1');

        return view('warehouse.seed-stock.create-step2', compact('warehouses', 'step1Data'));
    }

    /**
     * Store step 2 data and proceed to step 3.
     */
    public function storeStep2(Request $request)
    {
        if (!$request->session()->has('inventory_type_step1')) {
            return redirect()->route('seed-stock.create');
        }

        // Process warehouse data - only include checked warehouses
        $warehousesData = [];
        foreach ($request->input('warehouses', []) as $warehouseId => $data) {
            if (isset($data['warehouse_id'])) {
                $warehousesData[] = [
                    'warehouse_id' => $data['warehouse_id'],
                    'bin_ids' => $data['bin_ids'] ?? [],
                    'warehouse_only' => isset($data['warehouse_only']) ? (bool)$data['warehouse_only'] : false,
                ];
            }
        }

        if (empty($warehousesData)) {
            return back()->withErrors(['warehouses' => 'Pilih minimal satu gudang.']);
        }

        $step2Data = ['warehouses' => $warehousesData];
        $request->session()->put('inventory_type_step2', $step2Data);

        return redirect()->route('seed-stock.create-step3');
    }

    /**
     * Show step 3 (completion).
     */
    public function createStep3(Request $request)
    {
        if (!$request->session()->has('inventory_type_step1') || !$request->session()->has('inventory_type_step2')) {
            return redirect()->route('seed-stock.create');
        }

        $step1Data = $request->session()->get('inventory_type_step1');
        $step2Data = $request->session()->get('inventory_type_step2');

        return view('warehouse.seed-stock.create-step3', compact('step1Data', 'step2Data'));
    }

    /**
     * Store the complete inventory type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plant_id' => 'required|exists:plants,plant_id',
            'sku' => 'required|string|max:255|unique:inventory_types,sku',
            'unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang,kantong,unit,polybag',
            'estimated_value_per_unit' => 'required|numeric|min:0',
            'estimated_kg_per_unit' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'low_stock_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'low_stock_email' => 'nullable|email',
            'description' => 'nullable|string',
            'warehouses' => 'nullable|array',
        ]);

        // Get plant data and set name and category from plant
        $plant = Plant::with('type')->findOrFail($request->plant_id);
        $request->merge([
            'name' => $plant->name . ($plant->variety ? ' - ' . $plant->variety : ''),
            'category' => $plant->type?->name ?: $plant->name,
        ]);

        // Process warehouse data - only include checked warehouses
        $warehousesData = [];
        $warehousesInput = $request->input('warehouses', []);
        if (!empty($warehousesInput)) {
        foreach ($warehousesInput as $warehouseId => $data) {
            if (isset($data['warehouse_id'])) {
                $warehousesData[] = [
                    'warehouse_id' => $data['warehouse_id'],
                    'bin_ids' => $data['bin_ids'] ?? [],
                    'warehouse_only' => isset($data['warehouse_only']) ? (bool)$data['warehouse_only'] : false,
                ];
            }
        }
        }

        DB::beginTransaction();
        try {
            // Create inventory type
            $inventoryType = InventoryType::create($request->only([
                'plant_id',
                'name',
                'category',
                'sku',
                'unit',
                'estimated_value_per_unit',
                'estimated_kg_per_unit',
                'low_stock_threshold',
                'low_stock_unit',
                'low_stock_email',
                'description',
            ]));

            // Attach warehouses and bins (optional)
            if (!empty($warehousesData)) {
            foreach ($warehousesData as $warehouseData) {
                $warehouseId = $warehouseData['warehouse_id'];
                $warehouseOnly = $warehouseData['warehouse_only'] ?? false;

                if ($warehouseOnly) {
                    $inventoryType->warehouses()->attach($warehouseId, [
                        'bin_id' => null,
                        'warehouse_only' => true,
                    ]);
                } else {
                    $binIds = $warehouseData['bin_ids'] ?? [];
                    if (empty($binIds)) {
                        $inventoryType->warehouses()->attach($warehouseId, [
                            'bin_id' => null,
                            'warehouse_only' => true,
                        ]);
                    } else {
                        foreach ($binIds as $binId) {
                            $inventoryType->warehouses()->attach($warehouseId, [
                                'bin_id' => $binId,
                                'warehouse_only' => false,
                            ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('seed-stock.index')
                ->with('success', 'Tipe inventaris "' . $inventoryType->name . '" berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing inventory type: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    // API: Get inventory type data
    public function getInventoryTypeData($id)
    {
        try {
            $inventoryType = InventoryType::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'inventoryType' => [
                    'id' => $inventoryType->inventory_type_id,
                    'name' => $inventoryType->name,
                    'sku' => $inventoryType->sku,
                    'estimated_value_per_unit' => $inventoryType->estimated_value_per_unit,
                    'unit' => $inventoryType->unit,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory type not found'
            ], 404);
        }
    }

    public function show(Request $request, InventoryType $inventoryType)
    {
        $inventoryType->load([
            'lots' => function($query) {
                $query->with(['warehouse', 'bin'])->orderBy('expiry_date');
            },
            'transactions' => function($query) {
                $query->with(['user', 'warehouse', 'bin'])->orderBy('created_at', 'desc')->limit(50);
            },
            'notes' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'photos' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'warehouses',
            'certificationReports' => function($query) {
                $query->with([
                    'certification.plant.type',
                    'certification.harvest'
                ])->orderBy('report_date', 'desc');
            },
            'seeds' => function($query) {
                $query->with([
                    'plant.type',
                    'plant.certifications.harvest',
                    'filledByUser',
                    'histories.user'
                ])->orderBy('created_at', 'desc');
            }
        ]);

        // Update lot statuses
        foreach ($inventoryType->lots as $lot) {
            $lot->updateStatus();
        }

        // Calculate stock summary by location (group by warehouse+bin dengan relasi lengkap)
        $lots = $inventoryType->lots()->with(['warehouse', 'bin'])->get();
        $stockSummary = $lots->groupBy(function ($lot) {
            return ($lot->warehouse_id ?? '') . '|' . ($lot->bin_id ?? '');
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'warehouse' => $first->warehouse,
                'bin' => $first->bin,
                'total_stock' => $group->sum('current_stock'),
            ];
        })->values();

        // Get available certified seeds (not yet added to this inventory type)
        $addedCertReportIds = $inventoryType->certificationReports->pluck('certification_report_id')->toArray();
        $availableCertifiedSeeds = CertificationReport::with([
            'certification.plant.type',
        ])
        ->where('conclusion', 'LULUS')
        ->whereNotNull('certified_seed_quantity')
        ->where('certified_seed_quantity', '>', 0)
        ->whereNotIn('certification_report_id', $addedCertReportIds)
        ->orderBy('report_date', 'desc')
        ->get();

        // Get data for form dropdowns
        $plants = Plant::with('type')->orderBy('name')->get();
        $plantingLocations = PlantingLocation::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $plantTypes = \App\Models\PlantType::orderBy('name')->get();

        // Check if we need to pre-fill data from certification report
        $prefillData = null;
        $certificationReport = null;
        if ($request->has('certification_report_id') && $request->has('prefill') && $request->prefill === 'true') {
            $certificationReport = CertificationReport::with([
                'certification.plant.type',
            ])->find($request->certification_report_id);
            
            if ($certificationReport && $certificationReport->conclusion === 'LULUS') {
                $prefillData = [
                    'plant_id' => $certificationReport->certification->plant_id,
                    'planting_location_id' => $certificationReport->certification->planting_location_id,
                    'quantity' => $certificationReport->certified_seed_quantity,
                    'seed_unit' => $certificationReport->seed_unit ?? 'unit',
                    'seed_unit_quantity' => $certificationReport->seed_unit_quantity ?? 0,
                    'harvest_per_unit' => $certificationReport->harvest_per_unit ?? 0,
                    'harvest_per_unit_unit' => $certificationReport->harvest_per_unit_unit ?? 'unit',
                    'certified_seed_quantity' => $certificationReport->certified_seed_quantity ?? 0,
                    'estimated_sale_price_per_kg' => $certificationReport->estimated_sale_price_per_kg,
                    'expiry_date' => $certificationReport->expiry_date ? $certificationReport->expiry_date->format('Y-m-d') : null,
                ];
            }
        }

        // Get all warehouses with bins for adding to warehouse
        $warehouses = Warehouse::with('bins')->get();

        // Reload seeds from DB so storage_number is fresh (e.g. after add to warehouse)
        $inventoryType->unsetRelation('seeds');
        $inventoryType->load([
            'seeds' => function($query) {
                $query->with([
                    'plant.type',
                    'plant.certifications.harvest',
                    'plantingLocation',
                    'certificationReport.certification.plant.type',
                    'certificationReport.certification.plantingLocation',
                    'certificationReport.certification.harvest.location',
                    'filledByUser',
                    'histories.user'
                ])->orderBy('created_at', 'desc');
            }
        ]);

        // Tidak menyinkronkan semua seed ke satu lot: masing-masing data stok benih punya nomor penyimpanan
        // dan lokasi sendiri; hanya seed yang benar-benar ditambahkan ke gudang yang punya lot yang cocok.

        // Lokasi per seed untuk tabel Data Stok Benih (hanya lot yang production_id = storage_number seed tersebut)
        $lotsForLocations = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
            ->whereNotNull('warehouse_id')
            ->whereNotNull('bin_id')
            ->with(['warehouse', 'bin'])
            ->get();
        // Sertakan lot yang punya warehouse_id dan bin_id; jika relasi kosong (gudang/bin dihapus) tetap tampilkan dengan fallback
        $allLotsWithLocation = $lotsForLocations->filter(fn ($l) => (string) $l->warehouse_id !== '' && (string) $l->bin_id !== '');
        $seedLocations = [];
        $seedDisplayStock = []; // Jumlah inventaris tampilan: ikuti stok di gudang jika seed sudah di gudang
        foreach ($inventoryType->seeds as $seed) {
            $seedId = (string) $seed->inventory_type_seed_id;
            $sn = $seed->storage_number !== null && $seed->storage_number !== ''
                ? trim((string) $seed->storage_number)
                : null;
            // Hanya tampilkan lot yang production_id sama dengan nomor penyimpanan seed ini (satu seed = satu lot)
            $matched = $allLotsWithLocation->filter(function ($l) use ($sn) {
                $pid = $l->production_id !== null && $l->production_id !== ''
                    ? trim((string) $l->production_id)
                    : null;
                return $sn !== null && $pid !== null && $pid === $sn;
            });
            $seedLocations[$seedId] = $matched->map(function ($lot) {
                $warehouseName = $lot->warehouse?->name ?? 'Gudang (ID: ' . ($lot->warehouse_id ?? '-') . ')';
                $binName = $lot->bin?->name ?? 'Bin (ID: ' . ($lot->bin_id ?? '-') . ')';
                return [
                    'warehouse' => $warehouseName,
                    'bin' => $binName,
                    'warehouse_id' => $lot->warehouse_id,
                    'bin_id' => $lot->bin_id,
                ];
            })->unique(fn ($item) => ($item['warehouse_id'] ?? '') . '|' . ($item['bin_id'] ?? ''))->values();

            // Jika seed sudah di gudang, jumlah inventaris yang ditampilkan = current_stock lot (setelah penjualan/kurangi stok)
            $firstLot = $matched->first();
            if ($firstLot !== null) {
                $seedDisplayStock[$seedId] = [
                    'quantity' => $firstLot->current_stock,
                    'unit' => $firstLot->stock_unit ?? $inventoryType->unit,
                ];
            }
        }

        // Total stok tampilan: jumlah dari gudang (current_stock) untuk seed yang di gudang, sisanya dari data seed
        $displayTotalQuantity = 0;
        foreach ($inventoryType->seeds as $seed) {
            $sid = (string) $seed->inventory_type_seed_id;
            if (isset($seedDisplayStock[$sid])) {
                $displayTotalQuantity += (float) $seedDisplayStock[$sid]['quantity'];
            } else {
                $displayTotalQuantity += (float) ($seed->total_seed_quantity ?? $seed->quantity ?? 0);
            }
        }

        return view('warehouse.seed-stock.show', compact('inventoryType', 'stockSummary', 'availableCertifiedSeeds', 'plants', 'plantingLocations', 'users', 'prefillData', 'certificationReport', 'warehouses', 'plantTypes', 'seedLocations', 'seedDisplayStock', 'displayTotalQuantity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryType $inventoryType)
    {
        // Get all plants from "Tanaman Saya"
        $plants = Plant::with('type')->orderBy('name')->get();
        // Get all users for responsible person dropdown
        $users = User::orderBy('name')->get();
        return view('warehouse.seed-stock.edit', compact('inventoryType', 'plants', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'plant_id' => 'required|exists:plants,plant_id',
            'responsible_person_id' => 'nullable|exists:users,user_id',
            'sku' => 'required|string|max:255|unique:inventory_types,sku,' . $inventoryType->inventory_type_id,
            'unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang,kantong,unit,polybag',
            'estimated_value_per_unit' => 'nullable|numeric|min:0',
            'estimated_kg_per_unit' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'low_stock_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'low_stock_email' => 'nullable|email',
            'description' => 'nullable|string',
        ]);

        // Get plant data and set name and category from plant
        $plant = Plant::with('type')->findOrFail($request->plant_id);
        $updateData = $request->only([
            'plant_id',
            'sku',
            'unit',
            'estimated_value_per_unit',
            'estimated_kg_per_unit',
            'low_stock_threshold',
            'low_stock_unit',
            'low_stock_email',
            'description',
            'responsible_person_id',
        ]);
        $updateData['name'] = $plant->name . ($plant->variety ? ' - ' . $plant->variety : '');
        $updateData['category'] = $plant->type?->name ?: $plant->name;

        $inventoryType->update($updateData);

        return redirect()->route('seed-stock.show', $inventoryType)
            ->with('success', 'Tipe bibit berhasil diperbarui.');
    }

    /**
     * Show form for stock adjustment (add/subtract).
     */
    public function showStockAdjustment(InventoryType $inventoryType, $action = 'add')
    {
        $inventoryType->load(['lots' => function($query) {
            $query->with(['warehouse', 'bin'])
                ->where('current_stock', '>', 0)
                ->orderBy('expiry_date');
        }, 'warehouses']);

        $warehouses = Warehouse::with('bins')->get();

        return view('warehouse.seed-stock.stock-adjustment', compact('inventoryType', 'action', 'warehouses'));
    }

    /**
     * Store stock adjustment.
     */
    public function storeStockAdjustment(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'action' => 'required|in:add,subtract',
            'quantity' => 'required|numeric|min:0.01',
            'warehouse_id' => 'required|exists:warehouses,warehouse_id',
            'bin_id' => 'nullable|exists:bins,bin_id',
            'inventory_lot_id' => 'nullable|exists:inventory_lots,inventory_lot_id',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $quantity = $request->quantity;
            $action = $request->action;
            $warehouseId = $request->warehouse_id;
            $binId = $request->bin_id;
            $lotId = $request->inventory_lot_id;

            // Get or create lot
            if ($lotId) {
                $lot = InventoryLot::findOrFail($lotId);
            } else {
                // Create new lot if adding stock
                if ($action === 'add') {
                    $lot = InventoryLot::create([
                        'inventory_type_id' => $inventoryType->inventory_type_id,
                        'warehouse_id' => $warehouseId,
                        'bin_id' => $binId,
                        'initial_stock' => $quantity,
                        'current_stock' => $quantity,
                        'stock_unit' => $inventoryType->unit,
                        'status' => 'tersedia',
                    ]);
                } else {
                    return back()->withErrors(['error' => 'Lot harus dipilih untuk mengurangi stok.']);
                }
            }

            // Update lot stock
            if ($action === 'add') {
                $lot->current_stock += $quantity;
                $lot->initial_stock = max($lot->initial_stock, $lot->current_stock);
                $transactionType = 'penyesuaian_tambah';
            } else {
                if ($lot->current_stock < $quantity) {
                    return back()->withErrors(['quantity' => 'Stok tidak mencukupi.']);
                }
                $lot->current_stock -= $quantity;
                $transactionType = 'penyesuaian_kurang';
            }

            $lot->updateStatus();
            $lot->save();

            // Create transaction record
            InventoryTransaction::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'inventory_lot_id' => $lot->inventory_lot_id,
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'unit' => $inventoryType->unit,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('seed-stock.show', $inventoryType)
                ->with('success', 'Stok berhasil ' . ($action === 'add' ? 'ditambahkan' : 'dikurangi') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyesuaikan stok.']);
        }
    }

    /**
     * Store a note for inventory type.
     */
    public function storeNote(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        InventoryNote::create([
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('seed-stock.show', $inventoryType)
            ->with('success', 'Catatan berhasil ditambahkan.');
    }

    /**
     * Store a photo for inventory type.
     */
    public function storePhoto(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'caption' => 'nullable|string|max:255',
        ]);

        $photoPath = $request->file('photo')->store('inventory-photos', 'public');

        InventoryPhoto::create([
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'photo_path' => $photoPath,
            'caption' => $request->caption,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('seed-stock.show', $inventoryType)
            ->with('success', 'Foto berhasil diunggah.');
    }

    /**
     * Display certified seeds (benih yang lulus sertifikasi)
     */
    public function certifiedSeeds(Request $request)
    {
        $query = CertificationReport::with([
            'certification.plant.type',
        ])
        ->where('conclusion', 'LULUS')
        ->whereNotNull('certified_seed_quantity')
        ->where('certified_seed_quantity', '>', 0)
        ->orderBy('report_date', 'desc');

        $certifiedSeeds = $query->paginate(15);

        return view('warehouse.seed-stock.certified-seeds', compact('certifiedSeeds'));
    }

    /**
     * Show detail of certified seed
     */
    public function showCertifiedSeedDetail(CertificationReport $certificationReport)
    {
        $certificationReport->load([
            'certification.plant.type',
            'certification.harvest'
        ]);

        return view('warehouse.seed-stock.detail-certified-seed', compact('certificationReport'));
    }

    /**
     * Get certifications by plant type (for adding stock)
     */
    public function getCertificationsByPlantType(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'plant_type_id' => 'required|exists:plant_types,plant_type_id',
        ]);

        // Get all certifications that are LULUS and have certified_seed_quantity
        $certifications = CertificationReport::with([
            'certification.plant.type',
            'certification.plantingLocation',
        ])
        ->whereHas('certification.plant', function($query) use ($request) {
            $query->where('plant_type_id', $request->plant_type_id);
        })
        ->where('conclusion', 'LULUS')
        ->whereNotNull('certified_seed_quantity')
        ->where('certified_seed_quantity', '>', 0)
        ->orderBy('report_date', 'desc')
        ->get();

        // Get already added certification report IDs for this inventory type
        $addedCertReportIds = $inventoryType->certificationReports->pluck('certification_report_id')->toArray();

        // Format response
        $formattedCertifications = $certifications->map(function($cert) use ($addedCertReportIds) {
            return [
                'id' => $cert->certification_report_id,
                'report_number_bpsb' => $cert->report_number_bpsb,
                'plant_id' => $cert->certification->plant_id,
                'plant_name' => $cert->certification->plant->name . ($cert->certification->plant->variety ? ' - ' . $cert->certification->plant->variety : ''),
                'planting_location_id' => $cert->certification->planting_location_id,
                'location_name' => $cert->certification->plantingLocation->name ?? '-',
                'report_date' => $cert->report_date ? $cert->report_date->format('d M Y') : '-',
                'certified_seed_quantity' => $cert->certified_seed_quantity,
                'certified_seed_unit' => $cert->certified_seed_unit ?? 'kg',
                'seed_unit' => $cert->seed_unit ?? 'kg',
                'estimated_sale_price_per_kg' => $cert->estimated_sale_price_per_kg,
                'expiry_date' => $cert->expiry_date ? $cert->expiry_date->format('Y-m-d') : null,
                'already_added' => in_array($cert->certification_report_id, $addedCertReportIds),
            ];
        })->filter(function($cert) {
            // Filter out already added certifications
            return !$cert['already_added'];
        })->values();

        return response()->json([
            'certifications' => $formattedCertifications,
        ]);
    }

    /**
     * Return suggested unique storage number for the add-certified-seed form (JSON).
     */
    public function suggestStorageNumber()
    {
        return response()->json([
            'suggested' => $this->generateUniqueStorageNumber(),
        ]);
    }

    /**
     * Ambil semua nomor penyimpanan yang sudah pernah dipakai (aktif + riwayat hapus).
     * Nomor dari stok yang dihapus tidak boleh dipakai lagi.
     */
    private function getAllUsedStorageNumbers(): array
    {
        $used = InventoryTypeSeed::whereNotNull('storage_number')
            ->where('storage_number', '!=', '')
            ->pluck('storage_number')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $deletedNumbers = SeedHistory::where('action', 'delete')
            ->whereNotNull('old_data')
            ->get()
            ->map(function ($h) {
                $old = is_array($h->old_data) ? $h->old_data : (array) json_decode($h->old_data ?? '{}', true);
                return isset($old['storage_number']) ? trim((string) $old['storage_number']) : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return array_values(array_unique(array_merge($used, $deletedNumbers)));
    }

    /**
     * Generate unique storage number (NOP-YYYY-NNNNNN).
     * Tidak boleh sama dengan nomor yang aktif maupun yang pernah dipakai stok yang dihapus.
     */
    private function generateUniqueStorageNumber(): string
    {
        $prefix = 'NOP-' . date('Y') . '-';
        $used = $this->getAllUsedStorageNumbers();
        $usedSet = array_flip(array_map('strtolower', $used));

        $next = 1;
        $maxAttempts = 999999;
        while ($next <= $maxAttempts) {
            $candidate = $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            if (!isset($usedSet[strtolower($candidate)])) {
                return $candidate;
            }
            $next++;
        }
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Tambah data stok benih dari sertifikasi.
     * Setiap kali simpan = data stok BARU dengan nomor penyimpanan sendiri (unik).
     * Data ini dapat ditambahkan ke gudang sebagai lot terpisah dari data stok yang sudah ada
     * (satu inventory type boleh punya banyak data stok / banyak lot).
     */
    public function addCertifiedSeed(Request $request, InventoryType $inventoryType)
    {
        // Nomor penyimpanan wajib unik; isi otomatis jika kosong
        if (blank($request->storage_number)) {
            $request->merge(['storage_number' => $this->generateUniqueStorageNumber()]);
        }

        $usedStorageNumbers = $this->getAllUsedStorageNumbers();
        $request->validate([
            'seed_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'seed_quantity' => 'required|numeric|min:0.01',
            'estimated_sale_price_per_kg' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'filled_by_user_id' => 'required|exists:users,user_id',
            'certification_report_id' => 'required|exists:certification_reports,certification_report_id',
            'storage_number' => [
                'required',
                'string',
                'max:50',
                'unique:inventory_type_seeds,storage_number',
                function ($attribute, $value, $fail) use ($usedStorageNumbers) {
                    $v = trim((string) $value);
                    if ($v === '') {
                        return;
                    }
                    foreach ($usedStorageNumbers as $used) {
                        if (strcasecmp($v, $used) === 0) {
                            $fail('Nomor penyimpanan sudah pernah dipakai (aktif atau dari stok yang pernah dihapus). Gunakan nomor lain.');
                            return;
                        }
                    }
                },
            ],
        ]);

        // Normalisasi data baru (tanpa jumlah per unit): total_seed_unit sama dengan seed_unit
        $totalSeedUnit = $request->seed_unit;
        // Use seed_quantity as the main quantity
        $quantity = $request->seed_quantity;

        // Get certification report and extract data from it
        $certificationReport = \App\Models\CertificationReport::with(['certification.plant', 'certification.plantingLocation'])->find($request->certification_report_id);
        if (!$certificationReport) {
            return back()->withErrors(['certification_report_id' => 'Sertifikasi tidak ditemukan.'])->withInput();
        }
        
        // Get plant_id and planting_location_id from certification
        $plantId = $certificationReport->certification->plant_id;
        $plantingLocationId = $certificationReport->certification->planting_location_id;
        $reportType = $certificationReport->report_type ?? 'Laporan Pemeriksaan Pertanaman';

        // Selalu buat record baru: satu data stok = satu nomor penyimpanan unik (tidak digabung dengan stok lama)
        $seed = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'plant_id' => $plantId,
            'planting_location_id' => $plantingLocationId,
            'certification_report_id' => $request->certification_report_id,
            'quantity' => $quantity,
            'seed_unit' => $request->seed_unit,
            'seed_unit_quantity' => $request->seed_quantity, // simpan sama dengan jumlah inventaris
            'seed_per_unit' => 1,
            'seed_per_unit_unit' => $request->seed_unit,
            'total_seed_quantity' => $request->seed_quantity,
            'total_seed_unit' => $totalSeedUnit,
            'estimated_sale_price_per_kg' => $request->estimated_sale_price_per_kg,
            'expiry_date' => $request->expiry_date,
            'filled_by_user_id' => $request->filled_by_user_id,
            'storage_number' => $request->storage_number,
            'report_type' => $reportType,
        ]);

        // Create history record
        SeedHistory::create([
            'inventory_type_seed_id' => $seed->inventory_type_seed_id,
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'action' => 'create',
            'description' => 'Benih baru ditambahkan',
            'new_data' => $seed->toArray(),
            'user_id' => auth()->user()->user_id,
        ]);

        // Attach certification report to inventory type via pivot table
        $certificationReport = \App\Models\CertificationReport::find($request->certification_report_id);
        if ($certificationReport) {
            // Check if relationship already exists (qualify column to avoid ambiguity with JOIN)
            if (!$inventoryType->certificationReports()->where('inventory_type_certification_reports.certification_report_id', $certificationReport->certification_report_id)->exists()) {
                // Pivot table uses custom PK inventory_type_certification_report_id (no default); generate unique ID
                do {
                    $pivotId = 'ICR-' . strtoupper(Str::random(8));
                } while (DB::table('inventory_type_certification_reports')->where('inventory_type_certification_report_id', $pivotId)->exists());

                $inventoryType->certificationReports()->attach($certificationReport->certification_report_id, [
                    'inventory_type_certification_report_id' => $pivotId,
                    'quantity' => $request->seed_quantity
                ]);
            } else {
                // Update quantity if relationship exists
                $inventoryType->certificationReports()->updateExistingPivot($certificationReport->certification_report_id, [
                    'quantity' => $request->seed_quantity
                ]);
            }
            
            // Refresh the relationship to ensure it's loaded correctly
            $certificationReport->load('inventoryTypes');
        }
        
        // Check if redirect to certification by-plant page is requested
        if ($request->has('redirect_to_certification_by_plant') && $request->redirect_to_certification_by_plant) {
            $plant = $certificationReport->certification->plant;
            // Reload plant with fresh relationships to ensure status is updated
            $plant->load('type');
            return redirect()->route('certifications.by-plant', $plant)
                ->with('success', 'Benih berhasil ditambahkan ke stok bibit dari sertifikasi. Status stok telah diupdate.');
        }
        
        // Check if redirect to certification page is requested
        if ($request->has('redirect_to_certification') && $request->redirect_to_certification) {
            $harvest = $certificationReport->certification->harvest;
            // Reload harvest with fresh relationships to ensure status is updated
            $harvest->load('certification.reports.inventoryTypes');
            return redirect()->route('certifications.show', $harvest)
                ->with('success', 'Benih berhasil ditambahkan ke stok bibit dari sertifikasi. Status stok telah diupdate.');
        }
        
        return redirect()->route('seed-stock.show', [
            'inventoryType' => $inventoryType->inventory_type_id,
            'tab' => 'certified-seeds'
        ])->with('success', 'Benih berhasil ditambahkan ke stok bibit dari sertifikasi.');
    }

    /**
     * Show detail of seed
     */
    public function showSeedDetail(Request $request, InventoryType $inventoryType, InventoryTypeSeed $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => 'Benih tidak ditemukan di stok bibit ini.'], 404);
            }
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $seed->load([
            'plant.type',
            'filledByUser',
            'editor',
            'histories.user'
        ]);

        // Find certification report from seed's certification_report_id
        $certificationReport = null;
        $certification = null;
        
        if ($seed->certification_report_id) {
            // Use the certification report directly linked to this seed
            $certificationReport = \App\Models\CertificationReport::with([
                'certification.plant.type', 
                'certification.plantingLocation', 
                'certification.harvest.planting',
                'certification.harvest.location'
            ])->find($seed->certification_report_id);
            
            if ($certificationReport) {
                $certification = $certificationReport->certification;
            }
        } else {
            // Fallback: Get certification reports linked to this inventory type and seed's plant
            $linkedReports = $inventoryType->certificationReports()
                ->whereHas('certification', function($query) use ($seed) {
                    $query->where('plant_id', $seed->plant_id);
                })
                ->where('conclusion', 'LULUS')
                ->orderBy('report_date', 'desc')
                ->first();
            
            if ($linkedReports) {
                $certificationReport = $linkedReports;
                $certificationReport->load([
                    'certification.plant.type', 
                    'certification.plantingLocation', 
                    'certification.harvest.planting',
                    'certification.harvest.location'
                ]);
                $certification = $certificationReport->certification;
            } else {
                // Fallback: try to find from certification by plant_id
                $certification = \App\Models\Certification::where('plant_id', $seed->plant_id)
                    ->with(['plant.type', 'plantingLocation', 'harvest', 'reports' => function($query) {
                        $query->where('conclusion', 'LULUS')->orderBy('report_date', 'desc');
                    }])
                    ->first();
                
                if ($certification && $certification->reports->isNotEmpty()) {
                    $certificationReport = $certification->reports->first();
                }
            }
        }

        // If AJAX request or expects JSON, return JSON
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'id' => $seed->inventory_type_seed_id,
                'total_seed_quantity' => $seed->total_seed_quantity ?? $seed->quantity ?? 0,
                'total_seed_unit' => $seed->total_seed_unit ?? $seed->seed_unit ?? 'kg',
                'seed_unit' => $seed->seed_unit ?? 'kg',
                'quantity' => $seed->quantity ?? 0,
            ]);
        }

        return view('warehouse.seed-stock.detail-seed', compact('inventoryType', 'seed', 'certificationReport', 'certification'));
    }

    /**
     * Update seed
     */
    public function updateSeed(Request $request, InventoryType $inventoryType, InventoryTypeSeed $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $request->validate([
            'seed_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'total_seed_quantity' => 'required|numeric|min:0.01',
            'estimated_sale_price_per_kg' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $oldData = $seed->toArray();

        // Update seed
        $seed->update([
            'quantity' => $request->total_seed_quantity,
            'seed_unit' => $request->seed_unit,
            'seed_unit_quantity' => $request->total_seed_quantity,
            'seed_per_unit' => 1,
            'seed_per_unit_unit' => $request->seed_unit,
            'total_seed_quantity' => $request->total_seed_quantity,
            'total_seed_unit' => $request->seed_unit,
            'estimated_sale_price_per_kg' => $request->estimated_sale_price_per_kg,
            'expiry_date' => $request->expiry_date,
            'edited_at' => now(),
            'edited_by' => auth()->user()->user_id,
        ]);

        // Create history record
        SeedHistory::create([
            'inventory_type_seed_id' => $seed->inventory_type_seed_id,
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'action' => 'update',
            'description' => 'Data benih diperbarui',
            'old_data' => $oldData,
            'new_data' => $seed->fresh()->toArray(),
            'user_id' => auth()->user()->user_id,
        ]);

        return redirect()->route('seed-stock.show-seed-detail', ['inventoryType' => $inventoryType, 'seed' => $seed])
            ->with('success', 'Data benih berhasil diperbarui.');
    }

    /**
     * Destroy seed - hapus record stok benih dan stok di gudang
     */
    public function destroySeed(Request $request, InventoryType $inventoryType, InventoryTypeSeed $seed)
    {
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $request->validate([
            'delete_reason' => 'nullable|string|max:500',
        ]);

        $oldData = $seed->toArray();
        $deleteReason = $request->delete_reason ?: 'Data stok benih dihapus oleh user';

        DB::beginTransaction();
        try {
            // 1. Hapus stok di gudang (inventory lots) yang terkait dengan seed ini - lot ikut dihapus agar tidak muncul lagi di Daftar Stok di Bin
            if ($seed->storage_number) {
                $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                    ->where(function ($q) use ($seed) {
                        $sn = trim((string) $seed->storage_number);
                        $q->where('production_id', $sn)->orWhereRaw('TRIM(production_id) = ?', [$sn]);
                    })
                    ->get();

                foreach ($lots as $lot) {
                    if ($lot->current_stock > 0) {
                        InventoryTransaction::create([
                            'inventory_type_id' => $lot->inventory_type_id,
                            'inventory_lot_id' => $lot->inventory_lot_id,
                            'transaction_type' => 'pengurangan',
                            'quantity' => -$lot->current_stock,
                            'unit' => $lot->stock_unit,
                            'warehouse_id' => $lot->warehouse_id,
                            'bin_id' => $lot->bin_id,
                            'reason' => 'Stok dihapus - record data stok benih dihapus',
                            'notes' => 'Data stok benih dihapus. Lot: ' . $lot->production_id,
                            'user_id' => auth()->user()->user_id,
                        ]);
                    }
                    // Hapus lot agar hilang dari Daftar Stok di Bin (transaksi tetap ada, inventory_lot_id di set null oleh DB)
                    $lot->delete();
                }
            }

            // 2. Buat riwayat sebelum hapus (pakai inventory_type_id agar tetap muncul setelah seed dihapus)
            SeedHistory::create([
                'inventory_type_seed_id' => $seed->inventory_type_seed_id,
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'action' => 'delete',
                'description' => 'Stok benih dihapus - Alasan: ' . $deleteReason,
                'old_data' => $oldData,
                'user_id' => auth()->user()->user_id,
            ]);

            // 3. Hapus record seed (histories tetap ada karena FK SET NULL)
            $seed->delete();

            DB::commit();
            return redirect()->route('seed-stock.show', $inventoryType)
                ->with('success', 'Data stok benih berhasil dihapus. Stok di gudang telah disesuaikan. Riwayat dapat dilihat di tab Riwayat > Riwayat Stok Benih.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Gagal menghapus: ' . $e->getMessage()]);
        }
    }

    /**
     * Reduce stock
     */
    public function reduceStock(Request $request, InventoryType $inventoryType, InventoryTypeSeed $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $request->validate([
            'reduce_quantity' => 'required|numeric|min:0.01|max:' . $seed->total_seed_quantity,
            'reduce_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $seed->toArray();
            $reduceQuantity = $request->reduce_quantity;

            // Update seed quantity
            $newTotalQuantity = max(0, $seed->total_seed_quantity - $reduceQuantity);
            
            $seed->update([
                'total_seed_quantity' => $newTotalQuantity,
                'quantity' => $newTotalQuantity, // For backward compatibility
                'edited_at' => now(),
                'edited_by' => auth()->user()->user_id,
            ]);

            // Update stock in inventory lots (bins) if storage_number matches production_id
            if ($seed->storage_number) {
                $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                    ->where('production_id', $seed->storage_number)
                    ->get();
                
                foreach ($lots as $lot) {
                    // Reduce the same quantity from lot if available
                    if ($lot->current_stock > 0) {
                        // Reduce the same amount from lot (or remaining lot stock if less)
                        $lotReduction = min($reduceQuantity, $lot->current_stock);
                        
                        if ($lotReduction > 0) {
                            $lot->current_stock = max(0, $lot->current_stock - $lotReduction);
                            $lot->updateStatus();
                            $lot->save();
                            
                            // Create transaction record for lot
                            InventoryTransaction::create([
                                'inventory_type_id' => $lot->inventory_type_id,
                                'inventory_lot_id' => $lot->inventory_lot_id,
                                'transaction_type' => 'pengurangan',
                                'quantity' => -$lotReduction,
                                'unit' => $lot->stock_unit,
                                'warehouse_id' => $lot->warehouse_id,
                                'bin_id' => $lot->bin_id,
                                'reason' => $request->reason . ' (Sinkronisasi dari pengurangan stok benih)',
                                'notes' => "Stok dikurangi dari record data stok benih - Lot: {$lot->production_id}",
                                'user_id' => auth()->user()->user_id,
                            ]);
                        }
                    }
                }
            }

            // Create history record
            SeedHistory::create([
                'inventory_type_seed_id' => $seed->inventory_type_seed_id,
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'action' => 'reduce_stock',
                'description' => 'Stok dikurangi: ' . $reduceQuantity . ' ' . $request->reduce_unit . ($request->reason ? ' - Alasan: ' . $request->reason : ''),
                'old_data' => $oldData,
                'new_data' => $seed->fresh()->toArray(),
                'user_id' => auth()->user()->user_id,
            ]);

            DB::commit();

            return redirect()->route('seed-stock.show', ['inventoryType' => $inventoryType, 'tab' => 'certified-seeds'])
                ->with('success', 'Stok benih berhasil dikurangi dan stok di bin penyimpanan telah diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat mengurangi stok: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show seed history
     */
    public function showSeedHistory(InventoryType $inventoryType, InventoryTypeSeed $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $seed->load([
            'plant.type',
            'histories.user'
        ]);

        $histories = $seed->histories()->with('user')->orderBy('created_at', 'desc')->get();

        return view('warehouse.seed-stock.seed-history', compact('inventoryType', 'seed', 'histories'));
    }

    /**
     * Show storage detail for a seed (where it is stored, when, transactions)
     */
    public function showSeedStorageDetail(InventoryType $inventoryType, InventoryTypeSeed $seed)
    {
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $seed->load('plant');
        $storageNumber = $seed->storage_number ? trim((string) $seed->storage_number) : null;
        $lots = collect();
        $transactionsByLot = [];

        if ($storageNumber !== null && $storageNumber !== '') {
            $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                ->where('production_id', $storageNumber)
                ->with(['warehouse', 'bin'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($lots as $lot) {
                $transactionsByLot[$lot->inventory_lot_id] = InventoryTransaction::where('inventory_lot_id', $lot->inventory_lot_id)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('warehouse.seed-stock.seed-storage-detail', compact('inventoryType', 'seed', 'lots', 'transactionsByLot'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryType $inventoryType)
    {
        try {
            // Check if inventory type has associated lots with stock
            $totalStock = $inventoryType->lots()->sum('current_stock');
            if ($totalStock > 0) {
                return redirect()->route('seed-stock.index')
                    ->withErrors(['error' => 'Tidak dapat menghapus tipe bibit yang masih memiliki stok. Silakan hapus atau pindahkan stok terlebih dahulu.']);
            }

            // Check if inventory type has associated sales
            $hasSales = $inventoryType->saleItems()->exists();
            if ($hasSales) {
                return redirect()->route('seed-stock.index')
                    ->withErrors(['error' => 'Tidak dapat menghapus tipe bibit yang sudah memiliki riwayat penjualan.']);
            }

            $name = $inventoryType->name;
            
            DB::beginTransaction();
            
            // Delete related data in correct order to avoid foreign key constraints
            // First delete seeds and their histories
            $seeds = $inventoryType->seeds()->get();
            foreach ($seeds as $seed) {
                $seed->histories()->delete();
            }
            $inventoryType->seeds()->delete();
            
            // Delete notes
            $inventoryType->notes()->delete();
            
            // Delete photos and their files
            $photos = $inventoryType->photos()->get();
            foreach ($photos as $photo) {
                if ($photo->file_path && Storage::exists($photo->file_path)) {
                    Storage::delete($photo->file_path);
                }
            }
            $inventoryType->photos()->delete();
            
            // Delete transactions
            $inventoryType->transactions()->delete();
            
            // Delete lots (should be empty if validation passed, but delete anyway)
            $inventoryType->lots()->delete();
            
            // Delete pivot table relationships
            $inventoryType->warehouses()->detach();
            $inventoryType->certificationReports()->detach();
            
            // Delete inventory type
            $inventoryType->delete();
            
            DB::commit();

            return redirect()->route('seed-stock.index')
                ->with('success', 'Tipe bibit "' . $name . '" berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('seed-stock.index')
                ->withErrors(['error' => 'Terjadi kesalahan saat menghapus tipe bibit: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete all inventory types (for development/testing purposes)
     */
    public function destroyAll()
    {
        try {
            DB::beginTransaction();

            $inventoryTypes = InventoryType::all();
            $totalCount = $inventoryTypes->count();
            
            if ($totalCount === 0) {
                return redirect()->route('seed-stock.index')
                    ->with('success', 'Tidak ada data stok bibit yang perlu dihapus.');
            }

            foreach ($inventoryTypes as $inventoryType) {
                // Delete seeds and their histories
                $seeds = $inventoryType->seeds()->get();
                foreach ($seeds as $seed) {
                    $seed->histories()->delete();
                }
                $inventoryType->seeds()->delete();

                // Delete notes
                $inventoryType->notes()->delete();

                // Delete photos and their files
                $photos = $inventoryType->photos()->get();
                foreach ($photos as $photo) {
                    if ($photo->file_path && Storage::exists($photo->file_path)) {
                        Storage::delete($photo->file_path);
                    }
                }
                $inventoryType->photos()->delete();

                // Delete transactions
                $inventoryType->transactions()->delete();

                // Delete lots
                $inventoryType->lots()->delete();

                // Delete pivot table relationships
                $inventoryType->warehouses()->detach();
                $inventoryType->certificationReports()->detach();

                // Delete inventory type
                $inventoryType->delete();
            }

            DB::commit();

            return redirect()->route('seed-stock.index')
                ->with('success', "Berhasil menghapus {$totalCount} tipe bibit beserta semua data terkait.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('seed-stock.index')
                ->withErrors(['error' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()]);
        }
    }

    /**
     * Add seed to warehouse and bin
     */
    public function addSeedToWarehouse(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'seed_type' => 'required|in:certified,seed',
            'warehouse_id' => 'required|exists:warehouses,warehouse_id',
            'bin_id' => 'required|exists:bins,bin_id',
            'production_id' => 'nullable|string|max:255',
            'seed_id' => 'nullable|exists:inventory_type_seeds,inventory_type_seed_id',
            'certification_report_id' => 'nullable|exists:certification_reports,certification_report_id',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $bin = Bin::findOrFail($request->bin_id);

        // Verify bin belongs to warehouse
        if ($bin->warehouse_id != $warehouse->warehouse_id) {
            return back()->withErrors(['bin_id' => 'Bin tidak sesuai dengan gudang yang dipilih.']);
        }

        DB::beginTransaction();
        try {
            $initialStock = 0;
            $stockUnit = $inventoryType->unit;
            $expiryDate = null;
            $certificationReport = null;
            $seed = null;

            if ($request->seed_type === 'certified') {
                // Handle certified seed
                $certificationReport = CertificationReport::findOrFail($request->certification_report_id);
                
                // Verify certification report is linked to this inventory type
                if (!$inventoryType->certificationReports->contains($certificationReport->certification_report_id)) {
                    return back()->withErrors(['certification_report_id' => 'Laporan sertifikasi tidak terkait dengan stok benih ini.']);
                }

                // Find the inventory type seed record for this certification (to link storage_number later)
                $seed = InventoryTypeSeed::where('inventory_type_id', $inventoryType->inventory_type_id)
                    ->where('certification_report_id', $certificationReport->certification_report_id)
                    ->first();

                $initialStock = $certificationReport->certified_seed_quantity ?? 0;
                $stockUnit = $certificationReport->certified_seed_unit ?? $inventoryType->unit;
                $expiryDate = $certificationReport->expiry_date;
            } else {
                // Handle non-certified seed
                $seed = InventoryTypeSeed::findOrFail($request->seed_id);
                
                // Verify seed belongs to inventory type
                if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
                    return back()->withErrors(['seed_id' => 'Data benih tidak sesuai dengan stok bibit yang dipilih.']);
                }

                $initialStock = $seed->total_seed_quantity ?? $seed->quantity ?? 0;
                $stockUnit = $seed->total_seed_unit ?? $inventoryType->unit;
                $expiryDate = $seed->expiry_date;
            }

            if ($initialStock <= 0) {
                return back()->withErrors(['seed_id' => 'Jumlah stok benih tidak valid.']);
            }

            // Data stok benih hanya dapat ditambahkan sekali ke satu gudang dan satu bin
            if (isset($seed) && $seed) {
                $sn = $seed->storage_number ? trim((string) $seed->storage_number) : null;
                if ($sn) {
                    $alreadyInWarehouse = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                        ->where(function ($q) use ($sn) {
                            $q->where('production_id', $sn)
                                ->orWhereRaw('TRIM(production_id) = ?', [$sn]);
                        })
                        ->exists();
                    if ($alreadyInWarehouse) {
                        return back()->withErrors(['seed_id' => 'Data stok benih ini sudah ditambahkan ke gudang. Gunakan "Lihat data stok benih di gudang" untuk melihat lokasi penyimpanan.']);
                    }
                }
            }

            // Nomor penyimpanan: dari request, atau dari data stok benih (seed), atau generate
            $productionId = trim((string) ($request->production_id ?? ''));
            if ($productionId === '' && isset($seed) && $seed && $seed->storage_number) {
                $productionId = trim($seed->storage_number);
            }
            if ($productionId === '') {
                $year = date('Y');
                $count = InventoryLot::whereYear('created_at', $year)->count() + 1;
                $productionId = 'LOT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            // Create inventory lot
            $lot = InventoryLot::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'production_id' => $productionId,
                'initial_stock' => $initialStock,
                'current_stock' => $initialStock,
                'stock_unit' => $stockUnit,
                'warehouse_id' => $warehouse->warehouse_id,
                'bin_id' => $bin->bin_id,
                'expiry_date' => $expiryDate,
            ]);

            // Update lot status based on expiry date
            $lot->updateStatus();

            // Create transaction record
            $seedName = $request->seed_type === 'certified' 
                ? ($certificationReport->certification->plant->name ?? 'Benih Bersertifikasi')
                : ($seed->plant->name ?? 'Benih');

            InventoryTransaction::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'inventory_lot_id' => $lot->inventory_lot_id,
                'transaction_type' => 'stok_masuk',
                'quantity' => $initialStock,
                'unit' => $stockUnit,
                'warehouse_id' => $warehouse->warehouse_id,
                'bin_id' => $bin->bin_id,
                'reason' => 'Stok masuk ke bin',
                'notes' => 'Stok ditambahkan ke bin ' . $bin->name . ' dari data benih: ' . $seedName,
                'user_id' => auth()->user()->user_id,
            ]);

            // Sinkronkan seed.storage_number dengan lot production_id agar "Lokasi Penyimpanan" terisi (hanya jika beda)
            if (isset($seed) && $seed) {
                $currentSn = $seed->storage_number ? trim((string) $seed->storage_number) : '';
                if ($currentSn !== $productionId) {
                    $seed->update(['storage_number' => $productionId]);
                }
            }

            DB::commit();

            return redirect()->route('seed-stock.show', $inventoryType)
                ->with('success', 'Benih berhasil ditambahkan ke gudang ' . $warehouse->name . ' (Bin: ' . $bin->name . ').');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menambahkan benih ke gudang: ' . $e->getMessage()]);
        }
    }
}

