<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use App\Models\InventoryTypeSeed;
use App\Models\SeedHistory;
use App\Models\User;
use App\Models\Certification;
use App\Models\CertificationReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::withCount('bins')->paginate(10);
        
        return view('warehouse.locations.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('warehouse.locations.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255|unique:warehouses,internal_id',
            'tracking_type' => 'required|in:bin_separated,warehouse_only',
            'description' => 'nullable|string',
            'responsible_person_id' => 'nullable|exists:users,user_id',
        ]);

        Warehouse::create($request->all());

        return redirect()->route('warehouse-locations.index')
            ->with('success', 'Lokasi gudang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        $warehouse->load(['bins.inventoryLots.inventoryType']);
        
        // Get all inventory types for dropdown
        $inventoryTypes = InventoryType::orderBy('name')->get();
        
        return view('warehouse.locations.show', compact('warehouse', 'inventoryTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        $users = User::orderBy('name')->get();
        return view('warehouse.locations.edit', compact('warehouse', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255|unique:warehouses,internal_id,' . $warehouse->warehouse_id,
            'tracking_type' => 'required|in:bin_separated,warehouse_only',
            'description' => 'nullable|string',
            'responsible_person_id' => 'nullable|exists:users,user_id',
        ]);

        $warehouse->update($request->all());

        return redirect()->route('warehouse-locations.index')
            ->with('success', 'Lokasi gudang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        $warehouse->delete();

        return redirect()->route('warehouse-locations.index')
            ->with('success', 'Lokasi gudang berhasil dihapus.');
    }

    /**
     * Store a new bin for the warehouse
     */
    public function storeBin(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255',
            'max_capacity' => 'required|numeric|min:0',
            'capacity_unit' => 'required|string|in:quantity,kg,ton',
            'description' => 'nullable|string',
        ]);

        // Check if internal_id already exists for this warehouse
        $existingBin = Bin::where('warehouse_id', $warehouse->warehouse_id)
            ->where('internal_id', $request->internal_id)
            ->first();

        if ($existingBin) {
            return back()->withErrors(['internal_id' => 'ID Internal sudah digunakan di gudang ini.']);
        }

        $warehouse->bins()->create($request->all());

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Tempat penyimpanan berhasil ditambahkan.');
    }

    /**
     * Update a bin
     */
    public function updateBin(Request $request, Warehouse $warehouse, Bin $bin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255',
            'max_capacity' => 'required|numeric|min:0',
            'capacity_unit' => 'required|string|in:quantity,kg,ton',
            'description' => 'nullable|string',
        ]);

        // Check if internal_id already exists for this warehouse (excluding current bin)
        $existingBin = Bin::where('warehouse_id', $warehouse->warehouse_id)
            ->where('internal_id', $request->internal_id)
            ->where('id', '!=', $bin->bin_id)
            ->first();

        if ($existingBin) {
            return back()->withErrors(['internal_id' => 'ID Internal sudah digunakan di gudang ini.']);
        }

        $bin->update($request->all());

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Tempat penyimpanan berhasil diperbarui.');
    }

    /**
     * Delete a bin
     */
    public function destroyBin(Warehouse $warehouse, Bin $bin)
    {
        $bin->delete();

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Tempat penyimpanan berhasil dihapus.');
    }

    /**
     * Store inventory lot (bibit) to a bin
     */
    public function storeInventoryLot(Request $request, Warehouse $warehouse, Bin $bin)
    {
        $request->validate([
            'inventory_type_id' => 'required|exists:inventory_types,inventory_type_id',
            'seed_id' => 'required|exists:inventory_type_seeds,inventory_type_seed_id',
            'expiry_date' => 'required|date',
            'production_id' => 'nullable|string|max:255',
        ]);

        $inventoryType = InventoryType::findOrFail($request->inventory_type_id);
        $seed = InventoryTypeSeed::findOrFail($request->seed_id);
        
        // Verify seed belongs to inventory type
        if ($seed->inventory_type_id != $inventoryType->inventory_type_id) {
            return back()->withErrors(['seed_id' => 'Data benih tidak sesuai dengan stok bibit yang dipilih.']);
        }

        // Data stok benih hanya dapat ditambahkan sekali ke satu gudang dan satu bin
        $sn = $seed->storage_number ? trim((string) $seed->storage_number) : null;
        if ($sn) {
            $alreadyInWarehouse = InventoryLot::where('inventory_type_id', $inventoryType->inventory_type_id)
                ->where(function ($q) use ($sn) {
                    $q->where('production_id', $sn)
                        ->orWhereRaw('TRIM(production_id) = ?', [$sn]);
                })
                ->exists();
            if ($alreadyInWarehouse) {
                return back()->withErrors(['seed_id' => 'Data stok benih ini sudah ditambahkan ke gudang. Satu data stok benih hanya dapat disimpan di satu gudang dan satu bin.']);
            }
        }

        // Get quantity and unit from seed
        $initialStock = $seed->total_seed_quantity ?? $seed->quantity;
        $stockUnit = $seed->total_seed_unit ?? $inventoryType->unit;

        // production_id lot = nomor penyimpanan data stok (agar satu data stok = satu lot di gudang, data berbeda dari stok lain)
        $productionId = trim((string) ($request->production_id ?? ''));
        if ($productionId === '' && $sn) {
            $productionId = $sn;
        }
        if ($productionId === '') {
            $year = date('Y');
            $count = InventoryLot::whereYear('created_at', $year)->count() + 1;
            $productionId = 'LOT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        // Use provided expiry date (now required)
        $expiryDate = $request->expiry_date;

        // Buat lot baru (data stok ini jadi data terpisah di gudang, tidak digabung dengan lot lain)
        $lot = InventoryLot::create([
            'inventory_type_id' => $request->inventory_type_id,
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
        InventoryTransaction::create([
            'inventory_type_id' => $request->inventory_type_id,
            'inventory_lot_id' => $lot->inventory_lot_id,
            'transaction_type' => 'stok_masuk',
            'quantity' => $initialStock,
            'unit' => $stockUnit,
            'warehouse_id' => $warehouse->warehouse_id,
            'bin_id' => $bin->bin_id,
            'reason' => 'Stok masuk ke bin',
            'notes' => 'Stok ditambahkan ke bin ' . ($bin->name ?? 'bin') . ' dari data benih: ' . ($seed->plant?->name ?? 'Benih'),
            'user_id' => auth()->user()->user_id,
        ]);

        // Sinkronkan seed.storage_number dengan lot production_id agar "Lokasi Penyimpanan" terisi (jika beda, update seed)
        $currentSeedSn = $seed->storage_number ? trim((string) $seed->storage_number) : '';
        if ($currentSeedSn !== $productionId) {
            $seed->update(['storage_number' => $productionId]);
        }

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Bibit berhasil ditambahkan ke bin.');
    }

    /**
     * Get inventory lots for a specific bin (for AJAX)
     */
    public function getBinStocks(Warehouse $warehouse, Bin $bin)
    {
        $bin->load(['inventoryLots.inventoryType']);
        
        return response()->json([
            'bin' => [
                'id' => $bin->bin_id,
                'name' => $bin->name,
                'internal_id' => $bin->internal_id,
            ],
            'lots' => $bin->inventoryLots->map(function($lot) {
                $inventoryType = $lot->inventoryType;
                
                // Find BPSB number and seed/certification report info
                $bpsbNumber = '-';
                $seedId = null;
                $certificationReportId = null;
                $seedType = null;
                
                // Try to find from certification reports first
                $certificationReport = $inventoryType->certificationReports()
                    ->where('conclusion', 'LULUS')
                    ->orderBy('report_date', 'desc')
                    ->first();
                
                if ($certificationReport) {
                    $bpsbNumber = $certificationReport->report_number_bpsb ?: '-';
                    $certificationReportId = $certificationReport->certification_report_id;
                    $seedType = 'certified';
                } else {
                    // Try to find from seeds
                    $seed = $inventoryType->seeds()->first();
                    if ($seed) {
                        $seedId = $seed->inventory_type_seed_id;
                        $seedType = 'seed';
                        
                        // Try to find certification report from plant
                        if ($seed->plant_id) {
                            $certification = \App\Models\Certification::where('plant_id', $seed->plant_id)->first();
                            if ($certification) {
                                $certReport = \App\Models\CertificationReport::where('certification_id', $certification->certification_id)
                                    ->where('conclusion', 'LULUS')
                                    ->orderBy('report_date', 'desc')
                                    ->first();
                                if ($certReport) {
                                    $bpsbNumber = $certReport->report_number_bpsb ?: '-';
                                }
                            }
                        }
                    }
                }
                
                return [
                    'id' => $lot->inventory_lot_id,
                    'inventory_type_name' => $inventoryType->name,
                    'inventory_type_id' => $inventoryType->inventory_type_id,
                    'production_id' => $lot->production_id,
                    'current_stock' => $lot->current_stock,
                    'stock_unit' => $lot->stock_unit,
                    'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d M Y') : '-',
                    'status' => $lot->status,
                    'status_label' => $lot->status_label,
                    'status_color' => $lot->status_color,
                    'bpsb_number' => $bpsbNumber,
                    'seed_id' => $seedId,
                    'certification_report_id' => $certificationReportId,
                    'seed_type' => $seedType,
                ];
            }),
        ]);
    }

    /**
     * Get transaction history for a lot (untuk tombol Riwayat di Daftar Stok di Bin)
     */
    public function getLotTransactions(Warehouse $warehouse, Bin $bin, InventoryLot $lot)
    {
        if ($lot->bin_id != $bin->bin_id || $lot->warehouse_id != $warehouse->warehouse_id) {
            return response()->json(['success' => false, 'message' => 'Lot tidak ditemukan.'], 404);
        }

        $transactions = InventoryTransaction::where('inventory_lot_id', $lot->inventory_lot_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $inventoryType = $lot->inventoryType;

        return response()->json([
            'success' => true,
            'lot' => [
                'production_id' => $lot->production_id,
                'inventory_type_name' => $inventoryType->name ?? '-',
                'current_stock' => $lot->current_stock,
                'stock_unit' => $lot->stock_unit ?? 'kg',
            ],
            'transactions' => $transactions->map(function ($tx) {
                return [
                    'date' => $tx->created_at ? $tx->created_at->format('d M Y H:i') : '-',
                    'type' => $tx->transaction_type,
                    'type_label' => $tx->transaction_type === 'stok_masuk' ? 'Stok Masuk' : ($tx->transaction_type === 'pengurangan' ? 'Pengurangan' : ($tx->transaction_type === 'penghapusan' ? 'Penghapusan' : ($tx->transaction_type === 'penyesuaian_tambah' ? 'Penyesuaian (+)' : ($tx->transaction_type === 'penyesuaian_kurang' ? 'Penyesuaian (-)' : $tx->transaction_type)))),
                    'quantity' => $tx->quantity,
                    'unit' => $tx->unit ?? 'kg',
                    'reason' => $tx->reason ?? '-',
                    'notes' => $tx->notes ?? '-',
                    'user' => $tx->user->name ?? '-',
                ];
            }),
        ]);
    }

    /**
     * Delete inventory lot from bin
     */
    public function destroyInventoryLot(Request $request, Warehouse $warehouse, Bin $bin, InventoryLot $lot)
    {
        // Verify lot belongs to this bin
        if ($lot->bin_id != $bin->bin_id || $lot->warehouse_id != $warehouse->warehouse_id) {
            return response()->json([
                'success' => false,
                'message' => 'Lot tidak ditemukan di bin ini.'
            ], 404);
        }

        $request->validate([
            'delete_reason' => 'required|string|max:500',
        ]);

        // Create transaction record before deletion
        InventoryTransaction::create([
            'inventory_type_id' => $lot->inventory_type_id,
            'inventory_lot_id' => $lot->inventory_lot_id,
            'transaction_type' => 'penghapusan',
            'quantity' => $lot->current_stock,
            'unit' => $lot->stock_unit,
            'warehouse_id' => $warehouse->warehouse_id,
            'bin_id' => $bin->bin_id,
            'reason' => $request->delete_reason,
            'notes' => "Lot {$lot->production_id} dihapus dari bin {$bin->name} - Alasan: {$request->delete_reason}",
            'user_id' => auth()->user()->user_id,
        ]);

        // Delete the lot
        $lot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil dihapus.'
        ]);
    }

    /**
     * Reduce stock from inventory lot
     */
    public function reduceStock(Request $request, Warehouse $warehouse, Bin $bin, InventoryLot $lot)
    {
        // Verify lot belongs to this bin
        if ($lot->bin_id != $bin->bin_id || $lot->warehouse_id != $warehouse->warehouse_id) {
            return response()->json([
                'success' => false,
                'message' => 'Lot tidak ditemukan di bin ini.'
            ], 404);
        }

        $request->validate([
            'reduce_quantity' => 'required|numeric|min:0.01|max:' . $lot->current_stock,
            'reduce_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $reduceQuantity = $request->reduce_quantity;
            
            // Check if reducing more than available
            if ($reduceQuantity > $lot->current_stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pengurangan melebihi stok yang tersedia.'
                ], 422);
            }

            // Reduce stock in lot
            $oldLotStock = $lot->current_stock;
            $lot->current_stock -= $reduceQuantity;
            $lot->updateStatus();
            $lot->save();

            // Update stock in inventory type seed if production_id matches storage_number
            if ($lot->production_id) {
                $seed = \App\Models\InventoryTypeSeed::where('inventory_type_id', $lot->inventory_type_id)
                    ->where('storage_number', $lot->production_id)
                    ->first();
                
                if ($seed && $seed->total_seed_quantity > 0) {
                    // Reduce the same quantity from seed (or remaining seed stock if less)
                    $seedReduction = min($reduceQuantity, $seed->total_seed_quantity);
                    $newSeedQuantity = max(0, $seed->total_seed_quantity - $seedReduction);
                    
                    $oldSeedData = $seed->toArray();
                    
                    $seed->update([
                        'total_seed_quantity' => $newSeedQuantity,
                        'quantity' => $newSeedQuantity, // For backward compatibility
                        'edited_at' => now(),
                        'edited_by' => auth()->user()->user_id,
                    ]);
                    
                    // Create history record for seed
                    \App\Models\SeedHistory::create([
                        'inventory_type_seed_id' => $seed->inventory_type_seed_id,
                        'inventory_type_id' => $seed->inventory_type_id,
                        'action' => 'reduce_stock',
                        'description' => 'Stok dikurangi: ' . number_format($seedReduction, 2) . ' kg - Alasan: ' . $request->reduce_reason . ' (Sinkronisasi dari pengurangan stok di bin)',
                        'old_data' => $oldSeedData,
                        'new_data' => $seed->fresh()->toArray(),
                        'user_id' => auth()->user()->user_id,
                    ]);
                }
            }

            // Create transaction record
            InventoryTransaction::create([
                'inventory_type_id' => $lot->inventory_type_id,
                'inventory_lot_id' => $lot->inventory_lot_id,
                'transaction_type' => 'pengurangan',
                'quantity' => -$reduceQuantity, // Negative for reduction
                'unit' => $lot->stock_unit,
                'warehouse_id' => $warehouse->warehouse_id,
                'bin_id' => $bin->bin_id,
                'reason' => $request->reduce_reason,
                'notes' => "Stok dikurangi dari lot {$lot->production_id} di bin {$bin->name}",
                'user_id' => auth()->user()->user_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil dikurangi dan stok di record data stok benih telah diupdate.',
                'remaining_stock' => $lot->current_stock,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengurangi stok: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stock data for an inventory lot
     */
    public function updateStock(Request $request, Warehouse $warehouse, Bin $bin, InventoryLot $lot)
    {
        // Verify lot belongs to this bin
        if ($lot->bin_id != $bin->bin_id || $lot->warehouse_id != $warehouse->warehouse_id) {
            return response()->json([
                'success' => false,
                'message' => 'Lot tidak ditemukan di bin ini.'
            ], 404);
        }

        $request->validate([
            'update_reason' => 'required|string|max:500',
            'inventory_type_id' => 'required|exists:inventory_types,inventory_type_id',
            'seed_id' => 'required|exists:inventory_type_seeds,inventory_type_seed_id',
            'expiry_date' => 'required|date',
            'production_id' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Get the selected seed
            $seed = InventoryTypeSeed::with(['plant.type', 'plantingLocation'])->findOrFail($request->seed_id);
            
            // Verify seed belongs to the selected inventory type
            if ($seed->inventory_type_id != $request->inventory_type_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data benih tidak sesuai dengan tipe inventaris yang dipilih.'
                ], 422);
            }

            // Store old data for audit log
            $oldData = [
                'production_id' => $lot->production_id,
                'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('Y-m-d') : null,
                'current_stock' => $lot->current_stock,
                'status' => $lot->status,
                'inventory_type_id' => $lot->inventory_type_id,
            ];

            // Get new stock quantity and unit from seed
            $newStock = $seed->total_seed_quantity ?? $seed->quantity;
            $stockUnit = $seed->total_seed_unit ?? 'kg';

            // Generate production_id if not provided
            $productionId = $request->production_id;
            if (!$productionId) {
                $year = date('Y');
                $count = InventoryLot::whereYear('created_at', $year)->count() + 1;
                $productionId = 'LOT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            // Use seed expiry date if not provided
            $expiryDate = $request->expiry_date ?: $seed->expiry_date;

            // Update inventory lot
            $lot->update([
                'inventory_type_id' => $request->inventory_type_id,
                'production_id' => $productionId,
                'current_stock' => $newStock,
                'initial_stock' => $newStock, // Update initial stock as well
                'stock_unit' => $stockUnit,
                'expiry_date' => $expiryDate,
            ]);

            // Update lot status based on expiry date
            $lot->updateStatus();

            // Store new data for audit log
            $newData = [
                'production_id' => $lot->production_id,
                'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('Y-m-d') : null,
                'current_stock' => $lot->current_stock,
                'status' => $lot->status,
                'inventory_type_id' => $lot->inventory_type_id,
            ];

            // Create transaction record for audit log
            InventoryTransaction::create([
                'inventory_type_id' => $lot->inventory_type_id,
                'inventory_lot_id' => $lot->inventory_lot_id,
                'transaction_type' => 'penyesuaian_tambah', // Using existing type
                'quantity' => $newStock - $oldData['current_stock'], // Difference
                'unit' => $stockUnit,
                'warehouse_id' => $warehouse->warehouse_id,
                'bin_id' => $bin->bin_id,
                'reason' => 'Update Stok Benih',
                'notes' => 'Update stok benih: ' . $request->update_reason . '. Data lama: ' . json_encode($oldData) . '. Data baru: ' . json_encode($newData),
                'user_id' => auth()->user()->user_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data benih berhasil diupdate.',
                'lot' => [
                    'id' => $lot->inventory_lot_id,
                    'production_id' => $lot->production_id,
                    'current_stock' => $lot->current_stock,
                    'status' => $lot->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data benih: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get seeds for a specific inventory type (for AJAX)
     */
    public function getInventoryTypeSeeds(InventoryType $inventoryType)
    {
        $seeds = InventoryTypeSeed::where('inventory_type_id', $inventoryType->inventory_type_id)
            ->with(['plant.type', 'plantingLocation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'seeds' => $seeds->map(function($seed) {
                // Find BPSB number from certification report
                $bpsbNumber = '-';
                if ($seed->certification_report_id) {
                    $certReport = CertificationReport::where('certification_report_id', $seed->certification_report_id)->first();
                    if ($certReport) {
                        $bpsbNumber = $certReport->report_number_bpsb ?: '-';
                    }
                } elseif ($seed->plant_id) {
                    $certification = Certification::where('plant_id', $seed->plant_id)->first();
                    if ($certification) {
                        $certReport = CertificationReport::where('certification_id', $certification->certification_id)
                            ->where('conclusion', 'LULUS')
                            ->orderBy('report_date', 'desc')
                            ->first();
                        if ($certReport) {
                            $bpsbNumber = $certReport->report_number_bpsb ?: '-';
                        }
                    }
                }
                
                $plant = $seed->plant;
                $location = $seed->plantingLocation;
                $plantName = $plant->name ?? 'Benih';
                $plantVariety = $plant->variety ?? null;
                $locationName = $location->name ?? '-';
                $qty = $seed->total_seed_quantity ?? $seed->quantity ?? 0;
                $unit = $seed->total_seed_unit ?? 'kg';
                $displayText = $plantName . ($plantVariety ? ' - ' . $plantVariety : '') . ' (' . number_format($qty, 2) . ' ' . $unit . ')';
                
                return [
                    'id' => $seed->inventory_type_seed_id,
                    'storage_number' => $seed->storage_number ?: '-',
                    'plant_name' => $plantName,
                    'variety' => $plantVariety ?: '-',
                    'location' => $locationName,
                    'total_seed_quantity' => $qty,
                    'total_seed_unit' => $unit,
                    'seed_unit' => $seed->seed_unit,
                    'seed_unit_quantity' => $seed->seed_unit_quantity,
                    'seed_per_unit' => $seed->seed_per_unit,
                    'seed_per_unit_unit' => $seed->seed_per_unit_unit,
                    'expiry_date' => $seed->expiry_date ? $seed->expiry_date->format('Y-m-d') : null,
                    'bpsb_number' => $bpsbNumber,
                    'display_text' => $displayText,
                ];
            }),
        ]);
    }
}

