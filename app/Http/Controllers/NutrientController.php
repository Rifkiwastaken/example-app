<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\Nutrient;
use Illuminate\Http\Request;

class NutrientController extends Controller
{
    public function index(PlantingLocation $plantingLocation)
    {
        $nutrients = $plantingLocation->nutrients()->orderBy('application_date', 'desc')->paginate(15);
        return view('planting/planting-locations/nutrients/index', compact('plantingLocation', 'nutrients'));
    }

    public function create(PlantingLocation $plantingLocation)
    {
        return view('planting/planting-locations/nutrients/create', compact('plantingLocation'));
    }

    public function store(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'product_applied' => 'required|string|max:255',
            'amount_applied' => 'required|numeric|min:0',
            'application_method' => 'required|string|max:255',
            'application_date' => 'required|date',
            'nitrogen_n' => 'nullable|numeric|min:0',
            'phosphorus_p' => 'nullable|numeric|min:0',
            'potassium_k' => 'nullable|numeric|min:0',
            'magnesium_mg' => 'nullable|numeric|min:0',
            'sulfur_s' => 'nullable|numeric|min:0',
            'calcium_ca' => 'nullable|numeric|min:0',
            'boron_b' => 'nullable|numeric|min:0',
            'copper_cu' => 'nullable|numeric|min:0',
            'iron_fe' => 'nullable|numeric|min:0',
            'manganese_mn' => 'nullable|numeric|min:0',
            'zinc_zn' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $data['planting_location_id'] = $plantingLocation->id;
        Nutrient::create($data);
        
        return redirect()->route('planting-locations.nutrients.index', $plantingLocation)
            ->with('success', 'Data nutrisi berhasil ditambahkan');
    }

    public function show(PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        return view('planting/planting-locations/nutrients/show', compact('plantingLocation', 'nutrient'));
    }

    public function edit(PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        return view('planting/planting-locations/nutrients/edit', compact('plantingLocation', 'nutrient'));
    }

    public function update(Request $request, PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        $data = $request->validate([
            'product_applied' => 'required|string|max:255',
            'amount_applied' => 'required|numeric|min:0',
            'application_method' => 'required|string|max:255',
            'application_date' => 'required|date',
            'nitrogen_n' => 'nullable|numeric|min:0',
            'phosphorus_p' => 'nullable|numeric|min:0',
            'potassium_k' => 'nullable|numeric|min:0',
            'magnesium_mg' => 'nullable|numeric|min:0',
            'sulfur_s' => 'nullable|numeric|min:0',
            'calcium_ca' => 'nullable|numeric|min:0',
            'boron_b' => 'nullable|numeric|min:0',
            'copper_cu' => 'nullable|numeric|min:0',
            'iron_fe' => 'nullable|numeric|min:0',
            'manganese_mn' => 'nullable|numeric|min:0',
            'zinc_zn' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $nutrient->update($data);
        
        return redirect()->route('planting-locations.nutrients.index', $plantingLocation)
            ->with('success', 'Data nutrisi berhasil diperbarui');
    }

    public function destroy(PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        $nutrient->delete();
        
        return redirect()->route('planting-locations.nutrients.index', $plantingLocation)
            ->with('success', 'Data nutrisi berhasil dihapus');
    }
}













