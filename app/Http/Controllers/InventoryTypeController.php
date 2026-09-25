<?php

namespace App\Http\Controllers;

use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryNote;
use App\Models\InventoryPhoto;
use App\Models\StockHistory;
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
     * Temukan atau buat tipe stok benih berdasarkan laporan sertifikasi.
     * - Satu komoditas (plant) memiliki satu tipe stok utama.
     */
    private function findOrCreateInventoryTypeForCertification(CertificationReport $report, string $unit, ?float $estimatedValuePerUnit): InventoryType
    {
        $harvest = $report->harvest;
        $plant = $harvest?->plant;

        if ($plant) {
            $existing = InventoryType::whereHas('certificationReports.harvest', function ($q) use ($plant) {
                $q->whereHas('planting', function ($plantingQuery) use ($plant) {
                    $plantingQuery->forPlant($plant->plant_id);
                });
            })->first();
            if ($existing) {
                // Pastikan unit terisi; jika kosong, isi dari sertifikasi
                if (empty($existing->unit)) {
                    $existing->unit = $unit;
                    $existing->save();
                }
                if ($estimatedValuePerUnit !== null && $existing->estimated_value_per_unit === null) {
                    $existing->estimated_value_per_unit = $estimatedValuePerUnit;
                    $existing->save();
                }
                if ($existing->low_stock_threshold === null) {
                    $existing->low_stock_threshold = 1000;
                    $existing->low_stock_unit = $existing->unit ?: $unit;
                    $existing->save();
                }
                return $existing;
            }
        }

        // Data dasar dari plant & sertifikasi
        $plantName = $plant?->name ?? 'Benih Tanpa Nama';
        $variety = $plant?->variety ? ' - ' . $plant->variety : '';
        $typeName = $plant?->type?->name ?? $plantName;

        $sku = 'SKU-' . date('Y') . '-' . str_pad((string) (InventoryType::count() + 1), 4, '0', STR_PAD_LEFT);

        return InventoryType::create([
            'category' => $typeName,
            'name' => $plantName . $variety,
            'sku' => $sku,
            'unit' => $unit,
            'estimated_value_per_unit' => $estimatedValuePerUnit,
            'estimated_kg_per_unit' => null,
            'track_individual_lots' => true,
            'low_stock_threshold' => 1000,
            'low_stock_unit' => $unit,
            'low_stock_email' => null,
            'description' => 'Tipe stok benih otomatis dari sertifikasi',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua tanaman ("Tanaman Saya") sebagai basis data stok benih
        $plants = Plant::with('type')->orderBy('name')->get();

        // Ambil tipe stok benih dan kelompokkan berdasarkan plant dari laporan sertifikasi terbaru.
        $inventoryTypes = InventoryType::with([
            'seeds.harvest.plant',
            'latestCertificationReport.harvest.plant',
        ])->get();
        $inventoryTypesByPlant = $inventoryTypes->keyBy(function ($type) {
            return $type->latestCertificationReport?->harvest?->plant_id ?? $type->plant_id ?? $type->inventory_type_id;
        });

        // Lot per tipe (untuk hitung stok tampilan = ikuti gudang)
        $lotsByType = InventoryLot::whereIn('inventory_type_id', $inventoryTypesByPlant->pluck('inventory_type_id'))
            ->whereNotNull('warehouse_bin_id')
            ->get()
            ->groupBy('inventory_type_id');

        foreach ($inventoryTypesByPlant as $type) {
            $type->total_stock_from_seeds = (float) ($type->certification_stock_total ?? 0);
            // Total stok tampilan: sama dengan halaman detail (ikuti stok gudang jika seed sudah di gudang)
            $lots = $lotsByType->get($type->inventory_type_id, collect());
            $displayTotal = 0;
            foreach ($type->seeds as $seed) {
                $sn = $seed->storage_number ? trim((string) $seed->storage_number) : null;
                $matchedLots = $lots->filter(function ($lot) use ($sn) {
                    $pid = $lot->production_id ? trim((string) $lot->production_id) : null;
                    return $sn && $pid && $pid === $sn;
                });
                if ($matchedLots->isNotEmpty()) {
                    $displayTotal += (float) $matchedLots->filter(fn ($l) => $l->isActiveForOperationalStock())->sum('current_stock');
                } else {
                    $displayTotal += (float) ($seed->total_seed_quantity ?? $seed->quantity ?? $seed->certified_seed_quantity ?? 0);
                }
            }
            $type->display_total_stock = $displayTotal > 0 ? $displayTotal : (float) ($type->certification_stock_total ?? 0);
        }

        return view('warehouse.seed-stock.index', compact('plants', 'inventoryTypesByPlant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('seed-stock.index')
            ->with('error', 'Penambahan tipe benih manual sudah dihapus. Tipe stok dibuat otomatis dari sertifikasi.');
    }

    /**
     * Store step 1 data in session and proceed to step 2.
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'plant_id' => 'required|exists:plant_varieties,seed_varieties_id',
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

        return redirect()->route('seed-stock.index')
            ->with('error', 'Penambahan tipe benih manual sudah dihapus. Tipe stok dibuat otomatis dari sertifikasi.');
    }

    /**
     * Show step 2 form (warehouse selection).
     */
    public function createStep2(Request $request)
    {
        return redirect()->route('seed-stock.index')
            ->with('error', 'Penambahan tipe benih manual sudah dihapus. Tipe stok dibuat otomatis dari sertifikasi.');
    }

    /**
     * Store step 2 data and proceed to step 3.
     */
    public function storeStep2(Request $request)
    {
        return redirect()->route('seed-stock.index')
            ->with('error', 'Penambahan tipe benih manual sudah dihapus. Tipe stok dibuat otomatis dari sertifikasi.');
    }

    /**
     * Show step 3 (completion).
     */
    public function createStep3(Request $request)
    {
        return redirect()->route('seed-stock.index')
            ->with('error', 'Penambahan tipe benih manual sudah dihapus. Tipe stok dibuat otomatis dari sertifikasi.');
    }

    /**
     * Store the complete inventory type.
     */
    public function store(Request $request)
    {
        return redirect()->route('seed-stock.index')
            ->with('error', 'Penambahan tipe benih manual sudah dihapus. Tipe stok dibuat otomatis dari sertifikasi.');
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
        // Otomatis bersihkan benih yang sudah melewati masa edar:
        // - hilangkan dari data stok benih
        // - sesuaikan stok lot gudang terkait
        $this->autoRemoveExpiredSeeds($inventoryType);
        $inventoryType->refresh();

        $inventoryType->load([
            'lots' => function($query) {
                $query->with(['warehouse', 'bin'])->orderBy('expiry_date');
            },
            'transactions' => function($query) {
                $query->with(['user', 'inventoryLot.warehouse', 'inventoryLot.bin'])->orderBy('created_at', 'desc')->limit(50);
            },
            'notes' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'photos' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'certificationReports' => function($query) {
                $query->with([
                    'harvest.plant.type',
                    'harvest.location'
                ])->orderBy('report_date', 'desc');
            },
            'seeds' => function($query) {
                $query->with([
                    'harvest.plant.type',
                    'harvest.location'
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
            return ($lot->warehouse_id ?? '') . '|' . ($lot->warehouse_bin_id ?? '');
        })->map(function ($group) {
            $first = $group->first();
            $activeSum = $group->filter(fn ($lot) => $lot->isActiveForOperationalStock())->sum('current_stock');

            return (object) [
                'warehouse' => $first->warehouse,
                'bin' => $first->bin,
                'total_stock' => $activeSum,
            ];
        })->values();

        $operationalWarehouseCount = $lots
            ->filter(fn ($lot) => $lot->isActiveForOperationalStock() && $lot->warehouse_id)
            ->pluck('warehouse_id')
            ->unique()
            ->count();

        // Get available certified seeds (not yet added to this inventory type)
        $addedCertReportIds = $inventoryType->certificationReports->pluck('certification_report_id')->toArray();
        $availableCertifiedSeeds = CertificationReport::with([
            'harvest.plant.type',
            'harvest.location',
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
                'harvest.plant.type',
                'harvest.location',
            ])->find($request->certification_report_id);
            
            if ($certificationReport && $certificationReport->conclusion === 'LULUS') {
                $harvest = $certificationReport->harvest;
                $prefillData = [
                    'plant_id' => $harvest?->plant_id,
                    'planting_location_id' => $harvest?->planting_location_id,
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
        $warehouses = \Illuminate\Support\Facades\Schema::hasTable('warehouses')
            ? Warehouse::with('bins')->get()
            : collect();

        // Reload seeds from DB so storage_number is fresh (e.g. after add to warehouse)
        $inventoryType->unsetRelation('seeds');
        $inventoryType->load([
            'seeds' => function($query) {
                $query->with([
                    'harvest.plant.type',
                    'harvest.location'
                ])->orderBy('created_at', 'desc');
            }
        ]);

        // Tidak menyinkronkan semua seed ke satu lot: masing-masing data stok benih punya nomor penyimpanan
        // dan lokasi sendiri; hanya seed yang benar-benar ditambahkan ke gudang yang punya lot yang cocok.

        // Lokasi per seed untuk tabel Data Stok Benih (hanya lot yang production_id = storage_number seed tersebut)
        $lotsForLocations = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
            ->whereNotNull('warehouse_bin_id')
            ->with(['warehouse', 'bin'])
            ->get();
        // Sertakan lot yang punya bin; warehouse diturunkan dari relasi bin
        $allLotsWithLocation = $lotsForLocations->filter(fn ($l) => (string) $l->warehouse_bin_id !== '');
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
                $binName = $lot->bin?->name ?? 'Bin (ID: ' . ($lot->warehouse_bin_id ?? '-') . ')';
                return [
                    'warehouse_lot_id' => $lot->warehouse_lot_id,
                    'warehouse' => $warehouseName,
                    'bin' => $binName,
                    'warehouse_id' => $lot->warehouse_id,
                    'bin_id' => $lot->warehouse_bin_id,
                ];
            })->unique(fn ($item) => ($item['warehouse_id'] ?? '') . '|' . ($item['bin_id'] ?? ''))->values();

            // Jika seed sudah di gudang, jumlah inventaris yang ditampilkan = stok aktif lot (habis/kadaluarsa tidak dihitung)
            $firstLot = $matched->first();
            if ($firstLot !== null) {
                $activeQty = $matched->filter(fn ($l) => $l->isActiveForOperationalStock())->sum('current_stock');
                $seedDisplayStock[$seedId] = [
                    'quantity' => $activeQty,
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

        // Riwayat per benih: gabungkan event dari sertifikasi + lot gudang.
        $allStockHistories = StockHistory::with(['user', 'inventoryLot.bin.warehouse'])
            ->where('inventory_type_id', $inventoryType->inventory_type_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $seedHistories = [];
        foreach ($inventoryType->seeds as $seed) {
            $seedId = (string) $seed->inventory_type_seed_id;
            $certificationReportId = (string) ($seed->certification_report_id ?? '');
            $lotIds = collect($seedLocations[$seedId] ?? [])->pluck('warehouse_lot_id')->filter()->values()->all();

            $seedHistories[$seedId] = $allStockHistories
                ->filter(function ($history) use ($lotIds, $certificationReportId) {
                    $matchByLot = !empty($lotIds) && in_array($history->warehouse_lot_id, $lotIds, true);
                    $newCertId = (string) data_get($history->new_data, 'certification_report_id', '');
                    $oldCertId = (string) data_get($history->old_data, 'certification_report_id', '');
                    $matchByCertification = $certificationReportId !== '' && ($newCertId === $certificationReportId || $oldCertId === $certificationReportId);
                    return $matchByLot || $matchByCertification;
                })
                ->values();
        }

        // Arsip benih yang sudah tidak ada di stok aktif: otomatis karena masa edar, atau dihapus manual oleh user.
        // Ditampilkan di sub-tab "Riwayat Benih" agar user tetap bisa melihat datanya.
        $archivedRemovedSeeds = $allStockHistories
            ->filter(function ($history) {
                $certId = (string) data_get($history->old_data, 'certification_report_id', '');
                if ($certId === '') {
                    return false;
                }
                $type = (string) ($history->transaction_type ?? '');
                $action = (string) ($history->action ?? '');
                $reason = strtolower((string) ($history->reason ?? ''));
                $desc = strtolower((string) ($history->description ?? ''));

                $isAutoExpiryRemoval = ($type === 'penghapusan' || $action === 'delete')
                    && (str_contains($reason, 'masa edar') || str_contains($desc, 'masa edar'));

                // Penghapusan manual: action delete (biasanya transaction_type penghapusan) tanpa teks masa edar otomatis.
                $isManualSeedDelete = $action === 'delete'
                    && !str_contains($reason, 'masa edar')
                    && !str_contains($desc, 'masa edar');

                return $isAutoExpiryRemoval || $isManualSeedDelete;
            })
            ->sortByDesc('created_at')
            ->unique(function ($history) {
                return (string) data_get($history->old_data, 'certification_report_id');
            })
            ->map(function ($history) use (&$seedHistories) {
                $old = (array) ($history->old_data ?? []);
                $certId = (string) ($old['certification_report_id'] ?? '');
                $storageNumber = trim((string) ($old['planting_batch_number'] ?? $old['harvest_batch_number'] ?? ''));
                $qty = (float) ($old['quantity_added_to_stock'] ?? $old['certified_seed_quantity'] ?? 0);
                $reasonLower = strtolower((string) ($history->reason ?? ''));
                $descLower = strtolower((string) ($history->description ?? ''));
                $isAutoExpiryRemoval = str_contains($reasonLower, 'masa edar') || str_contains($descLower, 'masa edar');
                $archiveKind = $isAutoExpiryRemoval ? 'expired_auto' : 'manual_delete';

                $seedObject = (object) [
                    'inventory_type_seed_id' => $certId,
                    'certification_report_id' => $certId,
                    'report_number_bpsb' => $old['report_number_bpsb'] ?? '-',
                    'report_type' => $old['report_type'] ?? 'Laporan Pemeriksaan Pertanaman',
                    'storage_number' => $storageNumber !== '' ? $storageNumber : '-',
                    'total_seed_quantity' => $qty,
                    'total_seed_unit' => $old['seed_unit'] ?? $old['certified_seed_unit'] ?? 'kg',
                    'quantity' => $qty,
                    'created_at' => $history->created_at,
                    'expiry_date' => !empty($old['expiry_date']) ? \Carbon\Carbon::parse($old['expiry_date']) : null,
                ];

                $seedHistories[$certId] = $this->collectSeedHistoriesFromAll($certId, $seedHistories[$certId] ?? collect());

                return [
                    'id' => ($archiveKind === 'expired_auto' ? 'expired-' : 'removed-') . $certId,
                    'seed' => $seedObject,
                    'plant_name' => '-',
                    'variety' => '-',
                    'location' => '-',
                    'quantity' => $qty,
                    'expiry_date' => $seedObject->expiry_date,
                    'is_expired' => true,
                    'is_near_expiry' => false,
                    'archive_kind' => $archiveKind,
                    'plant_id' => null,
                    'harvest_id' => null,
                ];
            })
            ->values();

        return view('warehouse.seed-stock.show', compact('inventoryType', 'stockSummary', 'availableCertifiedSeeds', 'plants', 'plantingLocations', 'users', 'prefillData', 'certificationReport', 'warehouses', 'plantTypes', 'seedLocations', 'seedDisplayStock', 'displayTotalQuantity', 'seedHistories', 'archivedRemovedSeeds', 'operationalWarehouseCount'));
    }

    private function collectSeedHistoriesFromAll(string $certificationReportId, $existing = null)
    {
        $existing = $existing ?? collect();
        $histories = StockHistory::with(['user', 'inventoryLot.bin.warehouse'])
            ->where(function ($q) use ($certificationReportId) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(new_data, '$.certification_report_id')) = ?", [$certificationReportId])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(old_data, '$.certification_report_id')) = ?", [$certificationReportId]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $existing->merge($histories)->unique('stock_history_id')->sortByDesc('created_at')->values();
    }

    /**
     * Otomatis hapus data stok benih yang sudah melewati masa edar.
     * Berlaku saat membuka halaman detail stok benih.
     */
    private function autoRemoveExpiredSeeds(InventoryType $inventoryType): void
    {
        $expiredSeeds = CertificationReport::where('inventory_type_id', $inventoryType->inventory_type_id)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->get();

        if ($expiredSeeds->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($expiredSeeds as $seed) {
                $oldSeedData = $seed->toArray();
                $storageNumber = trim((string) ($seed->storage_number ?? ''));

                if ($storageNumber !== '') {
                    $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                        ->where(function ($q) use ($storageNumber) {
                            $q->where('production_id', $storageNumber)
                                ->orWhereRaw('TRIM(production_id) = ?', [$storageNumber]);
                        })
                        ->get();

                    foreach ($lots as $lot) {
                        $oldLotStock = (float) $lot->current_stock;
                        if ($oldLotStock > 0) {
                            StockHistory::create([
                                'inventory_type_id' => $lot->inventory_type_id,
                                'warehouse_lot_id' => $lot->warehouse_lot_id,
                                'transaction_type' => 'penghapusan',
                                'quantity' => -$oldLotStock,
                                'unit' => $lot->stock_unit,
                                'reason' => 'Masa edar terlewati (otomatis)',
                                'notes' => "Lot {$lot->production_id} dihapus otomatis karena benih melewati masa edar",
                                'old_data' => ['current_stock' => $oldLotStock],
                                'new_data' => ['current_stock' => 0],
                                'user_id' => auth()->id(),
                            ]);
                        }

                        $lot->delete();
                    }
                }

                StockHistory::create([
                    'inventory_type_id' => $inventoryType->inventory_type_id,
                    'action' => 'delete',
                    'transaction_type' => 'penghapusan',
                    'quantity' => -((float) ($seed->quantity_added_to_stock ?? $seed->certified_seed_quantity ?? 0)),
                    'unit' => $seed->seed_unit ?? $seed->certified_seed_unit ?? $inventoryType->unit,
                    'reason' => 'Masa edar terlewati (otomatis)',
                    'description' => 'Data stok benih dihapus otomatis karena masa edar sudah terlewati',
                    'old_data' => $oldSeedData,
                    'new_data' => [
                        'inventory_type_id' => null,
                        'quantity_added_to_stock' => 0,
                    ],
                    'user_id' => auth()->id(),
                ]);

                $seed->inventory_type_id = null;
                $seed->quantity_added_to_stock = 0;
                $seed->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }
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
            'plant_id' => 'required|exists:plant_varieties,seed_varieties_id',
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
        }]);

        $warehouses = \Illuminate\Support\Facades\Schema::hasTable('warehouses')
            ? Warehouse::with('bins')->get()
            : collect();

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
            'bin_id' => 'required_without:inventory_lot_id|exists:warehouse_bins,warehouse_bin_id',
            'inventory_lot_id' => 'nullable|exists:warehouse_lots,warehouse_lot_id',
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
                    if (!$binId) {
                        return back()->withErrors(['bin_id' => 'Bin wajib dipilih untuk menambahkan stok.']);
                    }
                    $lot = InventoryLot::create([
                        'inventory_type_id' => $inventoryType->inventory_type_id,
                        'warehouse_bin_id' => $binId,
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

            StockHistory::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'warehouse_lot_id' => $lot->warehouse_lot_id,
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'unit' => $inventoryType->unit,
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
            'harvest.plant.type',
            'harvest.location',
        ])
        ->where('conclusion', 'LULUS')
        ->whereNotNull('certified_seed_quantity')
        ->where('certified_seed_quantity', '>', 0)
        ->orderBy('report_date', 'desc');

        $certifiedSeeds = $query->paginate(15);

        return view('warehouse.seed-stock.certified-seeds', compact('certifiedSeeds'));
    }

    /**
     * Show detail of certified seed (rute dengan satu parameter: certified-seeds/{certificationReport}).
     */
    public function showCertifiedSeedDetail(CertificationReport $certificationReport)
    {
        return $this->showCertifiedSeedDetailView($certificationReport, null);
    }

    /**
     * Show detail of certified seed (rute dari Daftar Stok di Bin: {inventoryType}/certified-seeds/{certificationReport}).
     * Parameter inventoryType dipakai agar route model binding urutan parameter benar.
     */
    public function showCertifiedSeedDetailWithType(InventoryType $inventoryType, CertificationReport $certificationReport)
    {
        return $this->showCertifiedSeedDetailView($certificationReport, $inventoryType);
    }

    private function showCertifiedSeedDetailView(CertificationReport $certificationReport, ?InventoryType $inventoryType = null)
    {
        $inventoryType = $inventoryType ?? $certificationReport->inventoryType;
        $certificationReport->load([
            'harvest.plant.type',
            'harvest.location',
            'inventoryType',
        ]);
        return view('warehouse.seed-stock.detail-certified-seed', compact('certificationReport', 'inventoryType'));
    }

    /**
     * Get certifications by plant type (for adding stock)
     */
    public function getCertificationsByPlantType(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'plant_type_id' => 'required|exists:plant_commodities,seed_commodity_id',
        ]);

        // Get all certification reports that are LULUS and have certified_seed_quantity
        $certifications = CertificationReport::with([
            'harvest.plant.type',
            'harvest.location',
        ])
        ->whereHas('harvest.plant', function($query) use ($request) {
            $query->where('seed_commodity_id', $request->plant_type_id);
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
            $harvest = $cert->harvest;
            $plant = $harvest?->plant;
            $location = $harvest?->location;
            return [
                'id' => $cert->certification_report_id,
                'report_number_bpsb' => $cert->report_number_bpsb,
                'plant_id' => $harvest?->plant_id,
                'plant_name' => $plant ? ($plant->name . ($plant->variety ? ' - ' . $plant->variety : '')) : '-',
                'planting_location_id' => $harvest?->planting_location_id,
                'location_name' => $location?->name ?? '-',
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
        $used = CertificationReport::query()
            ->whereNotNull('inventory_type_id')
            ->where(function ($q) {
                $q->whereNotNull('planting_batch_number')->where('planting_batch_number', '!=', '')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('harvest_batch_number')->where('harvest_batch_number', '!=', '');
                    });
            })
            ->get()
            ->map(function (CertificationReport $r) {
                return trim((string) ($r->planting_batch_number ?: $r->harvest_batch_number ?: ''));
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $deletedNumbers = StockHistory::where('action', 'delete')
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
        $usedStorageNumbers = $this->getAllUsedStorageNumbers();
        $request->validate([
            'seed_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'seed_quantity' => 'required|numeric|min:0.01',
            'estimated_sale_price_per_kg' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'filled_by_user_id' => 'required|exists:users,user_id',
            'certification_report_id' => 'required|exists:certification_reports,certification_report_id',
            'storage_number' => ['nullable', 'string', 'max:50'],
        ]);

        // Use seed_quantity as the main quantity
        $quantity = $request->seed_quantity;

        // Get certification report and extract data from it
        $certificationReport = \App\Models\CertificationReport::with(['harvest.plant', 'harvest.location'])->find($request->certification_report_id);
        if (!$certificationReport) {
            return back()->withErrors(['certification_report_id' => 'Sertifikasi tidak ditemukan.'])->withInput();
        }
        
        // Production ID stok wajib mengikuti data sertifikasi.
        $productionId = $certificationReport->production_id;
        if (!$productionId) {
            return back()->withErrors(['certification_report_id' => 'Production ID pada data sertifikasi wajib diisi.'])->withInput();
        }
        $request->merge(['storage_number' => $productionId]);

        foreach ($usedStorageNumbers as $used) {
            if (strcasecmp($productionId, (string) $used) === 0) {
                return back()->withErrors([
                    'storage_number' => "Production ID {$productionId} sudah pernah dipakai.",
                ])->withInput();
            }
        }

        // Temukan atau buat tipe stok benih utama untuk plant ini (abaikan tipe yang dipilih manual)
        $unit = $request->seed_unit;
        $estimatedValue = $request->estimated_sale_price_per_kg ?? $certificationReport->estimated_sale_price_per_kg;
        $inventoryType = $this->findOrCreateInventoryTypeForCertification($certificationReport, $unit, $estimatedValue);

        $reportType = $certificationReport->report_type ?? 'Laporan Pemeriksaan Pertanaman';
        
        // Satu inventory type dapat menyimpan banyak stok sertifikasi, tetapi production_id harus unik.
        $duplicateProduction = CertificationReport::where('inventory_type_id', $inventoryType->inventory_type_id)
            ->where('certification_report_id', '!=', $certificationReport->certification_report_id)
            ->whereRaw("TRIM(COALESCE(planting_batch_number, harvest_batch_number)) = ?", [$productionId])
            ->exists();
        if ($duplicateProduction) {
            return back()->withErrors([
                'storage_number' => "Production ID {$productionId} sudah terdaftar di inventory type ini.",
            ])->withInput();
        }

        // Update data stok langsung pada certification_reports (tanpa tabel inventory_type_seeds).
        $certificationReport->inventory_type_id = $inventoryType->inventory_type_id;
        $certificationReport->quantity_added_to_stock = $request->seed_quantity;
        $certificationReport->seed_unit = $request->seed_unit;
        $certificationReport->seed_unit_quantity = $request->seed_quantity;
        $certificationReport->package_content_per_pack = 1;
        $certificationReport->harvest_per_unit_unit = $request->seed_unit;
        $certificationReport->estimated_sale_price_per_kg = $request->estimated_sale_price_per_kg;
        $certificationReport->expiry_date = $request->expiry_date;
        $certificationReport->planting_batch_number = $productionId;
        $certificationReport->report_type = $reportType;
        $certificationReport->reporter_name = auth()->user()->name;
        $certificationReport->save();

        // Create history record
        StockHistory::create([
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'transaction_type' => 'mendaftarkan_stok',
            'quantity' => $quantity,
            'unit' => $request->seed_unit,
            'reason' => 'Tambah dari sertifikasi',
            'action' => 'create',
            'description' => 'Benih baru ditambahkan',
            'new_data' => $certificationReport->toArray(),
            'user_id' => auth()->user()->user_id,
        ]);
        
        // Check if redirect to certification by-plant page is requested
        if ($request->has('redirect_to_certification_by_plant') && $request->redirect_to_certification_by_plant) {
            $plant = $certificationReport->harvest?->plant;
            if (!$plant) {
                return redirect()->route('seed-stock.show', [
                    'inventoryType' => $inventoryType->inventory_type_id,
                    'tab' => 'certified-seeds'
                ])->with('success', 'Benih berhasil ditambahkan ke stok bibit dari sertifikasi.');
            }
            // Reload plant with fresh relationships to ensure status is updated
            $plant->load('type');
            return redirect()->route('certifications.by-plant', $plant)
                ->with('success', 'Benih berhasil ditambahkan ke stok bibit dari sertifikasi. Status stok telah diupdate.');
        }
        
        // Check if redirect to certification page is requested
        if ($request->has('redirect_to_certification') && $request->redirect_to_certification) {
            $harvest = $certificationReport->harvest;
            if (!$harvest) {
                return redirect()->route('seed-stock.show', [
                    'inventoryType' => $inventoryType->inventory_type_id,
                    'tab' => 'certified-seeds'
                ])->with('success', 'Benih berhasil ditambahkan ke stok bibit dari sertifikasi.');
            }
            // Reload harvest with fresh relationships to ensure status is updated
            $harvest->load('certification.reports.inventoryType');
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
    public function showSeedDetail(Request $request, InventoryType $inventoryType, CertificationReport $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => 'Benih tidak ditemukan di stok bibit ini.'], 404);
            }
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $seed->load([
            'harvest.plant.type',
            'harvest.planting',
            'harvest.location',
        ]);

        $certificationReport = $seed;
        $certification = $seed->certification;

        // Hitung stok tampilan: jika benih sudah memiliki lot di gudang, gunakan current_stock lot,
        // jika belum, gunakan total_seed_quantity/quantity dari record seed.
        $displayQuantity = (float) ($seed->total_seed_quantity ?? $seed->quantity ?? 0);
        $displayUnit = $seed->total_seed_unit ?? $seed->seed_unit ?? 'kg';

        $storageNumber = $seed->storage_number ? trim((string) $seed->storage_number) : null;
        if ($storageNumber !== null && $storageNumber !== '') {
            $lotsForSeed = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                ->where('production_id', $storageNumber)
                ->get();

            if ($lotsForSeed->isNotEmpty()) {
                $displayQuantity = (float) $lotsForSeed->filter(fn ($l) => $l->isActiveForOperationalStock())->sum('current_stock');
                $firstLot = $lotsForSeed->first();
                if ($firstLot && $firstLot->stock_unit) {
                    $displayUnit = $firstLot->stock_unit;
                }
            }
        }

        // If AJAX request or expects JSON, return JSON
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'id' => $seed->certification_report_id,
                'total_seed_quantity' => $displayQuantity,
                'total_seed_unit' => $displayUnit,
                'seed_unit' => $seed->seed_unit ?? 'kg',
                'quantity' => $seed->quantity ?? 0,
            ]);
        }

        return view('warehouse.seed-stock.detail-seed', compact('inventoryType', 'seed', 'certificationReport', 'certification'));
    }

    /**
     * Update seed
     */
    public function updateSeed(Request $request, InventoryType $inventoryType, CertificationReport $seed)
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

        $oldData = array_merge($seed->toArray(), [
            'certification_report_id' => $seed->certification_report_id,
        ]);

        $seed->seed_unit = $request->seed_unit;
        $seed->seed_unit_quantity = $request->total_seed_quantity;
        $seed->quantity_added_to_stock = $request->total_seed_quantity;
        $seed->estimated_sale_price_per_kg = $request->estimated_sale_price_per_kg;
        $seed->expiry_date = $request->expiry_date;
        $seed->save();

        // Create history record
        StockHistory::create([
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'action' => 'update',
            'description' => 'Data benih diperbarui',
            'old_data' => $oldData,
            'new_data' => array_merge($seed->fresh()->toArray(), [
                'certification_report_id' => $seed->certification_report_id,
            ]),
            'user_id' => auth()->user()->user_id,
        ]);

        return redirect()->route('seed-stock.show-seed-detail', ['inventoryType' => $inventoryType, 'seed' => $seed])
            ->with('success', 'Data benih berhasil diperbarui.');
    }

    /**
     * Destroy seed - hapus record stok benih dan stok di gudang
     */
    public function destroySeed(Request $request, InventoryType $inventoryType, CertificationReport $seed)
    {
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $request->validate([
            'delete_reason' => 'nullable|string|max:500',
        ]);

        $oldData = array_merge($seed->toArray(), [
            'certification_report_id' => $seed->certification_report_id,
            'delete_reason' => $request->filled('delete_reason') ? trim((string) $request->delete_reason) : null,
        ]);
        $deleteReason = $request->filled('delete_reason')
            ? trim((string) $request->delete_reason)
            : 'Data stok benih dihapus oleh user';

        DB::beginTransaction();
        try {
            // Jumlah stok yang hilang dari agregat "stok saat ini" (sama logika tampilan: ikut lot jika ada, selain itu dari laporan).
            $removedFromLots = 0.0;
            $lots = collect();
            if ($seed->storage_number) {
                $sn = trim((string) $seed->storage_number);
                $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                    ->where(function ($q) use ($sn) {
                        $q->where('production_id', $sn)->orWhereRaw('TRIM(production_id) = ?', [$sn]);
                    })
                    ->get();
                foreach ($lots as $lot) {
                    $removedFromLots += (float) $lot->current_stock;
                }
            }
            $removedFromReport = (float) ($seed->quantity_added_to_stock ?? $seed->certified_seed_quantity ?? 0);
            $removedQty = $removedFromLots > 0 ? $removedFromLots : $removedFromReport;
            $stockUnit = $inventoryType->unit
                ?? ($lots->isNotEmpty() ? ($lots->first()->stock_unit ?? null) : null)
                ?? $seed->seed_unit
                ?? $seed->certified_seed_unit
                ?? 'kg';

            // 1. Hapus lot di gudang terkait (tanpa entri pengurangan terpisah — agar riwayat agregat tidak double-count;
            //    satu baris penghapusan stok benih mencatat total pengurangan).
            foreach ($lots as $lot) {
                $lot->delete();
            }

            // 2. Riwayat penghapusan stok benih (quantity negatif supaya kolom "Stok Sebelum/Sesudah" di tab Riwayat konsisten).
            StockHistory::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
            'action' => 'delete',
                'transaction_type' => 'penghapusan',
                'quantity' => -$removedQty,
                'unit' => $stockUnit,
                'reason' => $deleteReason,
                'notes' => $deleteReason,
                'description' => 'Data stok benih dihapus',
            'old_data' => $oldData,
                'new_data' => [
                    'certification_report_id' => $seed->certification_report_id,
                    'report_number_bpsb' => $seed->report_number_bpsb,
                    'quantity_added_to_stock' => 0,
                    'inventory_type_id' => null,
                ],
                'user_id' => auth()->user()->user_id,
            ]);

            // 3. Lepaskan relasi report dari inventory type (record report tetap ada)
            CertificationReport::where('certification_report_id', $seed->certification_report_id)
                ->update([
                    'inventory_type_id' => null,
                    'quantity_added_to_stock' => null,
                ]);

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
    public function reduceStock(Request $request, InventoryType $inventoryType, CertificationReport $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        // Hitung stok maksimum yang boleh dikurangi:
        // - Jika benih sudah memiliki lot di gudang (storage_number terhubung ke lot),
        //   gunakan jumlah current_stock dari lot tersebut (stok aktual di gudang).
        // - Jika belum ada lot di gudang, gunakan total_seed_quantity dari record seed.
        $maxAvailable = (float) ($seed->total_seed_quantity ?? 0);
        $storageNumber = $seed->storage_number ? trim((string) $seed->storage_number) : null;
        $lotStock = null;

        if ($storageNumber !== null && $storageNumber !== '') {
            $lotsForSeed = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                ->where(function ($q) use ($storageNumber) {
                    $q->where('production_id', $storageNumber)
                        ->orWhereRaw('TRIM(production_id) = ?', [$storageNumber]);
                })
                ->get();

            if ($lotsForSeed->isNotEmpty()) {
                $lotStock = (float) $lotsForSeed->sum('current_stock');
                $maxAvailable = $lotStock;
            }
        }

        $request->validate([
            'reduce_quantity' => 'required|numeric|min:0.01|max:' . $maxAvailable,
            'reduce_unit' => 'required|string|in:kg,ton,gram,butir,pcs,batang',
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $report = $seed;
            $oldData = array_merge($report->toArray(), [
                'certification_report_id' => $report->certification_report_id,
            ]);
            $reduceQuantity = $request->reduce_quantity;

            // Update seed quantity:
            // Jika stok di gudang tersedia (lotStock !== null), jadikan sumber kebenaran
            // untuk menghitung stok baru; jika tidak, gunakan total_seed_quantity.
            if ($lotStock !== null) {
                $newTotalQuantity = max(0, $lotStock - $reduceQuantity);
            } else {
                $newTotalQuantity = max(0, (float) ($report->quantity_added_to_stock ?? $seed->total_seed_quantity ?? 0) - $reduceQuantity);
            }
            
            $report->quantity_added_to_stock = $newTotalQuantity;
            $report->seed_unit_quantity = $newTotalQuantity;
            $report->save();

            // Update stock in inventory lots (bins) if storage_number matches production_id
            if ($storageNumber !== null && $storageNumber !== '') {
                $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                    ->where(function ($q) use ($storageNumber) {
                        $q->where('production_id', $storageNumber)
                            ->orWhereRaw('TRIM(production_id) = ?', [$storageNumber]);
                    })
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
                            StockHistory::create([
                                'inventory_type_id' => $lot->inventory_type_id,
                                'warehouse_lot_id' => $lot->warehouse_lot_id,
                                'transaction_type' => 'pengurangan',
                                'quantity' => -$lotReduction,
                                'unit' => $lot->stock_unit,
                                'reason' => $request->reason . ' (Sinkronisasi dari pengurangan stok benih)',
                                'notes' => "Stok dikurangi dari record data stok benih - Lot: {$lot->production_id}",
                                'user_id' => auth()->user()->user_id,
                            ]);
                        }
                    }
                }
            }

            // Create history record
            StockHistory::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'action' => 'reduce_stock',
                'description' => 'Stok dikurangi: ' . $reduceQuantity . ' ' . $request->reduce_unit . ($request->reason ? ' - Alasan: ' . $request->reason : ''),
                'old_data' => $oldData,
                'new_data' => array_merge($report->fresh()->toArray(), [
                    'certification_report_id' => $report->certification_report_id,
                ]),
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
    public function showSeedHistory(InventoryType $inventoryType, CertificationReport $seed)
    {
        // Verify that this seed belongs to this inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $seed->load([
            'harvest.plant.type',
            'harvest.location',
        ]);

        $certId = $seed->certification_report_id;
        $histories = StockHistory::with('user')
            ->where(function ($q) use ($certId) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(new_data, '$.certification_report_id')) = ?", [$certId])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(old_data, '$.certification_report_id')) = ?", [$certId]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('warehouse.seed-stock.seed-history', compact('inventoryType', 'seed', 'histories'));
    }

    /**
     * Show storage detail for a seed (where it is stored, when, transactions)
     */
    public function showSeedStorageDetail(InventoryType $inventoryType, CertificationReport $seed)
    {
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            abort(404, 'Benih tidak ditemukan di stok bibit ini.');
        }

        $seed->load(['harvest.plant.type', 'harvest.location']);
        $storageNumber = $seed->storage_number ? trim((string) $seed->storage_number) : null;
        $lots = collect();
        $transactionsByLot = [];

        if ($storageNumber !== null && $storageNumber !== '') {
            $lots = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                ->where(function ($q) use ($storageNumber) {
                    $q->where('production_id', $storageNumber)
                        ->orWhereRaw('TRIM(production_id) = ?', [$storageNumber]);
                })
                ->with(['warehouse', 'bin'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($lots as $lot) {
                $transactionsByLot[$lot->warehouse_lot_id] = StockHistory::where('warehouse_lot_id', $lot->warehouse_lot_id)
                    ->whereNotNull('transaction_type')
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
            $totalStock = (float) $inventoryType->lots()->activeForOperationalStock()->sum('current_stock');
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
            StockHistory::where('inventory_type_id', $inventoryType->inventory_type_id)->delete();
            
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
            
            // Delete lots (should be empty if validation passed, but delete anyway)
            $inventoryType->lots()->delete();
            
            // Unlink certification reports from this inventory type
            $inventoryType->certificationReports()->update(['inventory_type_id' => null, 'quantity_added_to_stock' => null]);
            
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
                StockHistory::where('inventory_type_id', $inventoryType->inventory_type_id)->delete();

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

                // Delete lots
                $inventoryType->lots()->delete();

                $inventoryType->certificationReports()->update(['inventory_type_id' => null, 'quantity_added_to_stock' => null]);

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
            'bin_id' => 'required|exists:warehouse_bins,warehouse_bin_id',
            'production_id' => 'nullable|string|max:255',
            'seed_id' => 'nullable|exists:certification_reports,certification_report_id',
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
            $sourceReport = null;

            if ($request->seed_type === 'certified') {
                // Handle certified seed
                $certificationReport = CertificationReport::findOrFail($request->certification_report_id);
                
                // Verify certification report is linked to this inventory type
                if (!$inventoryType->certificationReports->contains($certificationReport->certification_report_id)) {
                    return back()->withErrors(['certification_report_id' => 'Laporan sertifikasi tidak terkait dengan stok benih ini.']);
                }

                $sourceReport = $certificationReport;

                // Jumlah inventaris yang dimasukkan ke stok benih (form tambah dari sertifikasi), bukan total sertifikasi mentah.
                $initialStock = (float) ($certificationReport->quantity_added_to_stock ?? $certificationReport->certified_seed_quantity ?? 0);
                $stockUnit = $certificationReport->seed_unit ?? $certificationReport->certified_seed_unit ?? $inventoryType->unit;
                $expiryDate = $certificationReport->expiry_date;

                $productionFromCertification = $certificationReport->production_id;
                if (!$productionFromCertification) {
                    return back()->withErrors([
                        'certification_report_id' => 'Production ID pada data sertifikasi wajib diisi sebelum ditambahkan ke gudang.',
                    ]);
                }
            } else {
                // Handle seed from Data Stok Benih (sumber: certification_reports)
                $sourceReport = CertificationReport::findOrFail($request->seed_id);

                if ((string) $sourceReport->inventory_type_id !== (string) $inventoryType->inventory_type_id) {
                    return back()->withErrors(['seed_id' => 'Data benih tidak terkait dengan stok benih ini.']);
                }

                $initialStock = (float) ($sourceReport->quantity_added_to_stock ?? $sourceReport->certified_seed_quantity ?? 0);
                $stockUnit = $sourceReport->seed_unit ?? $sourceReport->certified_seed_unit ?? $inventoryType->unit;
                $expiryDate = $sourceReport->expiry_date;
            }

            if ($initialStock <= 0) {
                return back()->withErrors(['seed_id' => 'Jumlah stok benih tidak valid.']);
            }

            // Data stok benih hanya dapat ditambahkan sekali ke satu gudang dan satu bin
            if ($sourceReport) {
                $sn = $sourceReport->storage_number ? trim((string) $sourceReport->storage_number) : null;
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
            if ($productionId === '' && $sourceReport && $sourceReport->storage_number) {
                $productionId = trim((string) $sourceReport->storage_number);
            }
            if ($request->seed_type === 'certified' && !empty($productionFromCertification)) {
                $productionId = trim((string) $productionFromCertification);
            }
            if ($productionId === '') {
                $year = date('Y');
                $count = InventoryLot::whereYear('created_at', $year)->count() + 1;
                $productionId = 'LOT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            // Satu stok sertifikasi hanya boleh tersimpan di satu lot.
            $alreadyUsedLot = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                ->where(function ($q) use ($productionId) {
                    $q->where('production_id', $productionId)
                        ->orWhereRaw('TRIM(production_id) = ?', [$productionId]);
                })
                ->exists();
            if ($alreadyUsedLot) {
                return back()->withErrors([
                    'production_id' => "Production ID {$productionId} sudah tersimpan di lot lain.",
                ]);
            }

            // Create inventory lot
            $lot = InventoryLot::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'production_id' => $productionId,
                'initial_stock' => $initialStock,
                'current_stock' => $initialStock,
                'stock_unit' => $stockUnit,
                'warehouse_bin_id' => $bin->warehouse_bin_id,
                'expiry_date' => $expiryDate,
            ]);

            // Update lot status based on expiry date
            $lot->updateStatus();

            // Create transaction record
            $seedName = $request->seed_type === 'certified' 
                ? ($certificationReport->harvest?->plant?->name ?? 'Benih Bersertifikasi')
                : ($sourceReport?->harvest?->plant?->name ?? 'Benih');

            StockHistory::create([
                'inventory_type_id' => $inventoryType->inventory_type_id,
                'warehouse_lot_id' => $lot->warehouse_lot_id,
                'transaction_type' => 'stok_masuk',
                'quantity' => $initialStock,
                'unit' => $stockUnit,
                'reason' => 'Stok masuk ke bin',
                'notes' => 'Stok ditambahkan ke bin ' . $bin->name . ' dari data benih: ' . $seedName,
                'user_id' => auth()->user()->user_id,
            ]);

            // Sinkronkan seed.storage_number dengan lot production_id agar "Lokasi Penyimpanan" terisi (hanya jika beda)
            if ($sourceReport) {
                $reportForSync = CertificationReport::find($sourceReport->certification_report_id);
                if ($reportForSync) {
                    $currentSn = trim((string) ($reportForSync->planting_batch_number ?? ''));
                    if ($currentSn !== $productionId) {
                        $reportForSync->planting_batch_number = $productionId;
                        $reportForSync->save();
                    }
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

