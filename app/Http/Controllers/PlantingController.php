<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;

class PlantingController extends Controller
{
    public function index(Request $request)
    {
        $query = Planting::with(['plant', 'location']);

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }
        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }

        $plantings = $query->orderBy('planted_at', 'desc')->paginate(15);
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        
        return view('planting/plantings/index', compact('plantings', 'plants', 'locations'));
    }

    public function create(Request $request)
    {
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $selectedPlant = $request->get('plant_id') ? Plant::find($request->get('plant_id')) : null;
        
        return view('planting/plantings/create', compact('plants', 'locations', 'selectedPlant'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'planting_location_id' => 'required|exists:planting_locations,id',
            'bed_label' => 'nullable|string|max:255',
            'days_to_emerge' => 'nullable|integer|min:0',
            'spacing_between_plants' => 'nullable|numeric|min:0',
            'spacing_between_rows' => 'nullable|numeric|min:0',
            'sowing_depth' => 'nullable|numeric|min:0',
            'avg_height' => 'nullable|numeric|min:0',
            'start_method' => 'nullable|string|max:255',
            'germination_stage' => 'nullable|string|max:255',
            'seeds_per_hole' => 'nullable|integer|min:1',
            'light_profile' => 'nullable|string|max:255',
            'soil_condition' => 'nullable|string|max:255',
            'planting_detail' => 'nullable|string',
            'pruning_detail' => 'nullable|string',
            'perennial' => 'boolean',
            'days_to_flower' => 'nullable|integer|min:0',
            'days_to_harvest' => 'nullable|integer|min:0',
            'harvest_window_days' => 'nullable|integer|min:0',
            'expected_loss_rate' => 'nullable|numeric|min:0|max:100',
            'harvest_unit' => 'nullable|string|max:255',
            'expected_yield_per_hectare' => 'nullable|numeric|min:0',
            'quantity_planted' => 'nullable|numeric|min:0',
            'planted_at' => 'nullable|date',
        ]);

        $planting = Planting::create($data);
        
        return redirect()->route('plants.show', $planting->plant)
            ->with('success', 'Data penanaman berhasil ditambahkan');
    }

    public function show(Planting $planting)
    {
        $planting->load(['plant', 'location']);
        return view('planting/plantings/show', compact('planting'));
    }

    public function edit(Planting $planting)
    {
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        
        return view('planting/plantings/edit', compact('planting', 'plants', 'locations'));
    }

    public function update(Request $request, Planting $planting)
    {
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'planting_location_id' => 'required|exists:planting_locations,id',
            'bed_label' => 'nullable|string|max:255',
            'days_to_emerge' => 'nullable|integer|min:0',
            'spacing_between_plants' => 'nullable|numeric|min:0',
            'spacing_between_rows' => 'nullable|numeric|min:0',
            'sowing_depth' => 'nullable|numeric|min:0',
            'avg_height' => 'nullable|numeric|min:0',
            'start_method' => 'nullable|string|max:255',
            'germination_stage' => 'nullable|string|max:255',
            'seeds_per_hole' => 'nullable|integer|min:1',
            'light_profile' => 'nullable|string|max:255',
            'soil_condition' => 'nullable|string|max:255',
            'planting_detail' => 'nullable|string',
            'pruning_detail' => 'nullable|string',
            'perennial' => 'boolean',
            'days_to_flower' => 'nullable|integer|min:0',
            'days_to_harvest' => 'nullable|integer|min:0',
            'harvest_window_days' => 'nullable|integer|min:0',
            'expected_loss_rate' => 'nullable|numeric|min:0|max:100',
            'harvest_unit' => 'nullable|string|max:255',
            'expected_yield_per_hectare' => 'nullable|numeric|min:0',
            'quantity_planted' => 'nullable|numeric|min:0',
            'planted_at' => 'nullable|date',
        ]);

        $planting->update($data);
        
        return redirect()->route('plants.show', $planting->plant)
            ->with('success', 'Data penanaman berhasil diperbarui');
    }

    public function destroy(Planting $planting)
    {
        $plant = $planting->plant;
        $planting->delete();
        
        return redirect()->route('plants.show', $plant)
            ->with('success', 'Data penanaman berhasil dihapus');
    }
}













