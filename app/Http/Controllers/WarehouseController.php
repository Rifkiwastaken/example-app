<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\User;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('warehouses')) {
            return view('warehouse.locations.index', [
                'warehouses' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
            ]);
        }

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
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255|unique:warehouses,internal_id',
            'tipe_lokasi' => 'required|in:gudang,lapangan',
            'description' => 'nullable|string',
            'responsible_person_id' => 'nullable|exists:users,user_id',
        ]);

        Warehouse::create($data);

        return redirect()->route('warehouse-locations.index')
            ->with('success', 'Lokasi penyimpanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        $warehouse->load(['bins.packagings.stock.plant']);
        $seedUnits = \App\Models\SeedUnit::orderBy('name')->get();

        return view('warehouse.locations.show', compact('warehouse', 'seedUnits'));
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
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'internal_id' => 'required|string|max:255|unique:warehouses,internal_id,' . $warehouse->warehouse_id,
            'tipe_lokasi' => 'required|in:gudang,lapangan',
            'description' => 'nullable|string',
            'responsible_person_id' => 'nullable|exists:users,user_id',
        ]);

        $warehouse->update($data);

        return redirect()->route('warehouse-locations.index')
            ->with('success', 'Lokasi penyimpanan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($warehouse_location)
    {
        $warehouse = $warehouse_location instanceof Warehouse ? $warehouse_location : Warehouse::findOrFail($warehouse_location);
        $warehouse->delete();

        return redirect()->route('warehouse-locations.index')
            ->with('success', 'Lokasi penyimpanan berhasil dihapus.');
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
            'capacity_unit' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        // Check if internal_id already exists for this warehouse
        $existingBin = Bin::where('warehouse_id', $warehouse->warehouse_id)
            ->where('internal_id', $request->internal_id)
            ->first();

        if ($existingBin) {
            return back()->withErrors(['internal_id' => 'ID Internal sudah digunakan di lokasi ini.']);
        }

        $warehouse->bins()->create($request->all());

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Blok penyimpanan berhasil ditambahkan.');
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
            'capacity_unit' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        // Check if internal_id already exists for this warehouse (excluding current bin)
        $existingBin = Bin::where('warehouse_id', $warehouse->warehouse_id)
            ->where('internal_id', $request->internal_id)
            ->where('warehouse_bin_id', '!=', $bin->warehouse_bin_id)
            ->first();

        if ($existingBin) {
            return back()->withErrors(['internal_id' => 'ID Internal sudah digunakan di lokasi ini.']);
        }

        $bin->update($request->all());

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Blok penyimpanan berhasil diperbarui.');
    }

    /**
     * Delete a bin
     */
    public function destroyBin(Warehouse $warehouse, Bin $bin)
    {
        $bin->delete();

        return redirect()->route('warehouse-locations.show', $warehouse)
            ->with('success', 'Blok penyimpanan berhasil dihapus.');
    }

}
