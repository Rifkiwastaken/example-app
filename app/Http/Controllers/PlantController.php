<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantType;
use App\Models\PlantingLocation;
use App\Models\Planting;
use App\Models\Harvest;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    public function index(Request $request)
    {
        $query = Plant::with(['type', 'plantingLocation']);

        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }
        if ($request->filled('plant_type_id')) {
            $query->where('plant_type_id', $request->plant_type_id);
        }

        $plants = $query->orderBy('name')->paginate(15);
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        return view('planting/plants/index', compact('plants', 'types', 'locations'));
    }

    public function create()
    {
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        return view('planting/plants/create', compact('types', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plant_type_id' => 'nullable|exists:plant_types,id',
            'variety' => 'nullable|string|max:255',
            'planting_location_id' => 'nullable|exists:planting_locations,id',
        ]);
        
        // Generate name from plant type and variety
        $name = '';
        if ($request->filled('plant_type_id')) {
            $plantType = PlantType::find($request->plant_type_id);
            if ($plantType) {
                $name = ($plantType->category ? $plantType->category . ' - ' : '') . $plantType->name;
                if ($request->filled('variety')) {
                    $name .= ' ' . $request->variety;
                }
            }
        } else {
            // If no plant type, use variety as name
            $name = $request->variety ?? 'Tanaman Baru';
        }
        
        // Ensure name is not empty
        if (empty(trim($name))) {
            $name = 'Tanaman Baru';
        }
        
        $data['name'] = trim($name);
        $data['status'] = 'perencanaan';
        $data['progress'] = 0;
        
        $plant = Plant::create($data);
        return redirect()->route('plants.show', $plant)->with('success', 'Tanaman ditambahkan');
    }

    public function show(Plant $plant)
    {
        $plant->load(['type', 'plantingLocation', 'plantings', 'harvests']);
        return view('planting/plants/show', compact('plant'));
    }

    public function edit(Plant $plant)
    {
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        return view('planting/plants/edit', compact('plant', 'types', 'locations'));
    }

    public function update(Request $request, Plant $plant)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'plant_type_id' => 'nullable|exists:plant_types,id',
            'variety' => 'nullable|string|max:255',
            'status' => 'required|in:perencanaan,ditanam,dipanen,selesai',
            'progress' => 'nullable|integer|min:0|max:100',
            'planting_location_id' => 'nullable|exists:planting_locations,id',
        ]);
        $plant->update($data);
        return redirect()->route('plants.show', $plant)->with('success', 'Tanaman diperbarui');
    }

    /**
     * Show current plantings for a plant
     */
    public function currentPlantings(Plant $plant)
    {
        $plant->load(['type']);
        
        // Get all active plantings for this plant
        // Active means not yet harvested (no harvest record linked)
        $currentPlantings = Planting::where('plant_id', $plant->id)
            ->with(['location.baseLocation'])
            ->whereDoesntHave('harvest')
            ->get();
        
        // Group by location for better display
        $plantingsByLocation = $currentPlantings->groupBy('planting_location_id');
        
        return view('planting.plants.current-plantings', compact('plant', 'currentPlantings', 'plantingsByLocation'));
    }

    /**
     * Show harvest history for a plant
     */
    public function harvestsIndex(Plant $plant, Request $request)
    {
        $plant->load(['type']);
        
        $query = Harvest::where('plant_id', $plant->id)
            ->with(['planting.location.baseLocation', 'location.baseLocation'])
            ->orderBy('harvested_at', 'desc');
        
        // Filter by year if provided
        if ($request->filled('year')) {
            $query->whereYear('harvested_at', $request->year);
        }
        
        // Filter by location if provided
        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }
        
        $harvests = $query->paginate(15);
        $locations = PlantingLocation::whereHas('plantings', function($q) use ($plant) {
            $q->where('plant_id', $plant->id);
        })->orderBy('name')->get();
        
        // Get available years for filter
        $years = Harvest::where('plant_id', $plant->id)
            ->selectRaw('YEAR(harvested_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('planting.plants.harvests', compact('plant', 'harvests', 'locations', 'years'));
    }

    public function destroy(Plant $plant)
    {
        $plant->delete();
        return redirect()->route('plants.index')->with('success', 'Tanaman berhasil dihapus');
    }
}







