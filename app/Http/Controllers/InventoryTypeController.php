<?php

namespace App\Http\Controllers;

use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use App\Models\InventoryNote;
use App\Models\InventoryPhoto;
use App\Models\Warehouse;
use App\Models\Bin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventoryTypes = InventoryType::withCount('lots')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15);

        // Calculate total stock for each type
        foreach ($inventoryTypes as $type) {
            $type->total_stock_calculated = $type->lots()->sum('current_stock');
        }

        return view('warehouse.seed-stock.index', compact('inventoryTypes'));
    }

    /**
     * Show the form for creating a new resource (Step 1).
     */
    public function create()
    {
        return view('warehouse.seed-stock.create-step1');
    }

    /**
     * Store step 1 data in session and proceed to step 2.
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:inventory_types,sku',
            'electronic_id' => 'nullable|string|max:255',
            'unit' => 'required|string|in:kg,ton,kantong,unit,polybag,pcs',
            'estimated_value_per_unit' => 'nullable|numeric|min:0',
            'estimated_kg_per_unit' => 'nullable|numeric|min:0',
            'track_individual_lots' => 'boolean',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'low_stock_unit' => 'nullable|string|in:kg,ton,kantong,unit,polybag,pcs',
            'low_stock_email' => 'nullable|email',
            'description' => 'nullable|string',
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
        if (!$request->session()->has('inventory_type_step1') || !$request->session()->has('inventory_type_step2')) {
            return redirect()->route('seed-stock.create');
        }

        $step1Data = $request->session()->get('inventory_type_step1');
        $step2Data = $request->session()->get('inventory_type_step2');

        DB::beginTransaction();
        try {
            // Create inventory type
            $inventoryType = InventoryType::create($step1Data);

            // Attach warehouses and bins
            foreach ($step2Data['warehouses'] as $warehouseData) {
                $warehouseId = $warehouseData['warehouse_id'];
                $warehouseOnly = $warehouseData['warehouse_only'] ?? false;

                if ($warehouseOnly) {
                    // Store only in warehouse (no bin)
                    $inventoryType->warehouses()->attach($warehouseId, [
                        'bin_id' => null,
                        'warehouse_only' => true,
                    ]);
                } else {
                    // Store in specific bins
                    $binIds = $warehouseData['bin_ids'] ?? [];
                    foreach ($binIds as $binId) {
                        $inventoryType->warehouses()->attach($warehouseId, [
                            'bin_id' => $binId,
                            'warehouse_only' => false,
                        ]);
                    }
                }
            }

            DB::commit();

            // Clear session data
            $request->session()->forget(['inventory_type_step1', 'inventory_type_step2']);

            return redirect()->route('seed-stock.index')
                ->with('success', 'Tipe inventaris "' . $inventoryType->name . '" berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryType $inventoryType)
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
            'warehouses'
        ]);

        // Update lot statuses
        foreach ($inventoryType->lots as $lot) {
            $lot->updateStatus();
        }

        // Calculate stock summary by location
        $stockSummary = $inventoryType->lots()
            ->select('warehouse_id', 'bin_id', DB::raw('SUM(current_stock) as total_stock'))
            ->groupBy('warehouse_id', 'bin_id')
            ->with(['warehouse', 'bin'])
            ->get();

        return view('warehouse.seed-stock.show', compact('inventoryType', 'stockSummary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryType $inventoryType)
    {
        return view('warehouse.seed-stock.edit', compact('inventoryType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:inventory_types,sku,' . $inventoryType->id,
            'electronic_id' => 'nullable|string|max:255',
            'unit' => 'required|string|in:kg,ton,kantong,unit,polybag,pcs',
            'estimated_value_per_unit' => 'nullable|numeric|min:0',
            'estimated_kg_per_unit' => 'nullable|numeric|min:0',
            'track_individual_lots' => 'boolean',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'low_stock_unit' => 'nullable|string|in:kg,ton,kantong,unit,polybag,pcs',
            'low_stock_email' => 'nullable|email',
            'description' => 'nullable|string',
        ]);

        $inventoryType->update($request->all());

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
            'warehouse_id' => 'required|exists:warehouses,id',
            'bin_id' => 'nullable|exists:bins,id',
            'inventory_lot_id' => 'nullable|exists:inventory_lots,id',
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
                        'inventory_type_id' => $inventoryType->id,
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
                'inventory_type_id' => $inventoryType->id,
                'inventory_lot_id' => $lot->id,
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
            'inventory_type_id' => $inventoryType->id,
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
            'inventory_type_id' => $inventoryType->id,
            'photo_path' => $photoPath,
            'caption' => $request->caption,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('seed-stock.show', $inventoryType)
            ->with('success', 'Foto berhasil diunggah.');
    }
}

