<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

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
        return view('warehouse.locations.create');
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
        return view('warehouse.locations.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255|unique:warehouses,internal_id,' . $warehouse->id,
            'tracking_type' => 'required|in:bin_separated,warehouse_only',
            'description' => 'nullable|string',
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
        $existingBin = Bin::where('warehouse_id', $warehouse->id)
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
        $existingBin = Bin::where('warehouse_id', $warehouse->id)
            ->where('internal_id', $request->internal_id)
            ->where('id', '!=', $bin->id)
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
            'inventory_type_id' => 'required|exists:inventory_types,id',
            'initial_stock' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'production_id' => 'nullable|string|max:255',
        ]);

        $inventoryType = InventoryType::findOrFail($request->inventory_type_id);

        // Generate production_id if not provided
        $productionId = $request->production_id;
        if (!$productionId) {
            $year = date('Y');
            $count = InventoryLot::whereYear('created_at', $year)->count() + 1;
            $productionId = 'LOT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        // Create inventory lot
        $lot = InventoryLot::create([
            'inventory_type_id' => $request->inventory_type_id,
            'production_id' => $productionId,
            'initial_stock' => $request->initial_stock,
            'current_stock' => $request->initial_stock,
            'stock_unit' => $inventoryType->unit,
            'warehouse_id' => $warehouse->id,
            'bin_id' => $bin->id,
            'expiry_date' => $request->expiry_date,
        ]);

        // Update lot status based on expiry date
        $lot->updateStatus();

        // Create transaction record
        InventoryTransaction::create([
            'inventory_type_id' => $request->inventory_type_id,
            'inventory_lot_id' => $lot->id,
            'transaction_type' => 'stok_masuk',
            'quantity' => $request->initial_stock,
            'unit' => $inventoryType->unit,
            'warehouse_id' => $warehouse->id,
            'bin_id' => $bin->id,
            'reason' => 'Stok masuk ke bin',
            'notes' => 'Stok ditambahkan ke bin ' . $bin->name,
            'user_id' => auth()->id(),
        ]);

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
                'id' => $bin->id,
                'name' => $bin->name,
                'internal_id' => $bin->internal_id,
            ],
            'lots' => $bin->inventoryLots->map(function($lot) {
                return [
                    'id' => $lot->id,
                    'inventory_type_name' => $lot->inventoryType->name,
                    'production_id' => $lot->production_id,
                    'current_stock' => $lot->current_stock,
                    'stock_unit' => $lot->stock_unit,
                    'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d M Y') : '-',
                    'status' => $lot->status,
                    'status_label' => $lot->status_label,
                    'status_color' => $lot->status_color,
                ];
            }),
        ]);
    }
}

