<?php

namespace App\Http\Controllers;

use App\Models\SeedUnit;
use Illuminate\Http\Request;

class SeedUnitController extends Controller
{
    public function index()
    {
        SeedUnit::ensureFixed();
        $units = SeedUnit::orderByDesc('is_fixed')->orderBy('name')->get();

        return view('planting.plants.seed-units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:seed_units,code',
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'code.required' => 'Kode satuan wajib diisi.',
            'code.unique' => 'Kode satuan sudah dipakai.',
        ]);

        SeedUnit::create([
            'name' => $data['name'],
            'code' => strtolower(trim($data['code'])),
            'is_fixed' => false,
        ]);

        return redirect()->route('seed-units.index')->with('success', 'Satuan benih berhasil ditambahkan');
    }
}
