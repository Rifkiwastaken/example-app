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
            $query->forPlant($request->plant_id);
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
        try {
            $data = $request->validate([
                'plant_id' => 'required|exists:plant_varieties,seed_varieties_id',
                'planting_location_id' => 'required|exists:planting_locations,planting_location_id',
                'bed_label' => 'nullable|string|max:255',
                'planted_at' => 'nullable|date',
                'planting_batch_number' => 'nullable|string|max:255',
                'estimated_harvest_date' => 'nullable|date',
                'area_ha' => 'nullable|numeric|min:0',
                'planting_format' => 'nullable|string|max:255',
                'planting_format_custom' => 'nullable|string|max:255',
                'planting_amount' => 'nullable|numeric|min:0',
                'progress' => 'nullable|integer|min:0|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            $planting = Planting::create($data);
            
            return redirect()->route('plants.show', $planting->plant)
                ->with('success', 'Data penanaman berhasil ditambahkan');
        } catch (\Exception $e) {
            \Log::error('Error creating planting: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['_token'])
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data penanaman: ' . $e->getMessage())
                ->withInput();
        }
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
            'plant_id' => 'required|exists:plant_varieties,seed_varieties_id',
            'planting_location_id' => 'required|exists:planting_locations,planting_location_id',
            'bed_label' => 'nullable|string|max:255',
            'planted_at' => 'nullable|date',
            'planting_batch_number' => 'nullable|string|max:255',
            'estimated_harvest_date' => 'nullable|date',
            'area_ha' => 'nullable|numeric|min:0',
            'planting_format' => 'nullable|string|max:255',
            'planting_format_custom' => 'nullable|string|max:255',
            'planting_amount' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
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
















