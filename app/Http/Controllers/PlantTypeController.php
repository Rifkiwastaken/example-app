<?php

namespace App\Http\Controllers;

use App\Models\PlantType;
use Illuminate\Http\Request;

class PlantTypeController extends Controller
{
    public function index()
    {
        $types = PlantType::orderBy('category')->orderBy('name')->paginate(15);
        return view('planting/types/index', compact('types'));
    }

    public function create()
    {
        return view('planting/types/create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);
        PlantType::create($data);
        return redirect()->route('plant-types.index')->with('success', 'Tipe tanaman ditambahkan');
    }

    public function edit(PlantType $plantType)
    {
        return view('planting/types/edit', ['type' => $plantType]);
    }

    public function update(Request $request, PlantType $plantType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);
        $plantType->update($data);
        return redirect()->route('plant-types.index')->with('success', 'Tipe tanaman diperbarui');
    }

    public function destroy(PlantType $plantType)
    {
        $plantType->delete();
        return back()->with('success', 'Tipe tanaman dihapus');
    }
}















