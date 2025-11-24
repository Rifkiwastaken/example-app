<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Planting;
use App\Models\Harvest;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;

class HarvestController extends Controller
{
    public function index(Request $request)
    {
        $query = Harvest::with(['plant', 'planting', 'location']);

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }
        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }

        $harvests = $query->orderBy('harvested_at', 'desc')->paginate(15);
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        
        return view('planting/harvests/index', compact('harvests', 'plants', 'locations'));
    }

    public function create(Request $request)
    {
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $selectedPlant = $request->get('plant_id') ? Plant::find($request->get('plant_id')) : null;
        $selectedPlanting = $request->get('planting_id') ? Planting::find($request->get('planting_id')) : null;
        
        return view('planting/harvests/create', compact('plants', 'locations', 'selectedPlant', 'selectedPlanting'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'planting_id' => 'nullable|exists:plantings,id',
            'planting_location_id' => 'required|exists:planting_locations,id',
            'harvested_at' => 'required|date',
            'batch_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'source' => 'required|string|max:255',
            'quality' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'loss_quantity' => 'nullable|numeric|min:0',
        ]);

        $harvest = Harvest::create($data);
        
        // Redirect based on source
        if ($request->filled('planting_location_id') && $request->filled('from_planting_location')) {
            // From planting location detail page
            return redirect()->route('planting-locations.show', $harvest->planting_location_id)
                ->with('success', 'Data panen berhasil ditambahkan');
        } elseif ($request->filled('planting_id')) {
            // From current plantings page
            return redirect()->route('plants.current-plantings', $harvest->plant)
                ->with('success', 'Data panen berhasil ditambahkan');
        } else {
            // From harvests index page
            return redirect()->route('plants.harvests.index', $harvest->plant)
                ->with('success', 'Data panen berhasil ditambahkan');
        }
    }

    public function show(Harvest $harvest)
    {
        $harvest->load(['plant', 'planting', 'location']);
        return view('planting/harvests/show', compact('harvest'));
    }

    public function edit(Harvest $harvest)
    {
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $plantings = Planting::where('plant_id', $harvest->plant_id)->get();
        
        return view('planting/harvests/edit', compact('harvest', 'plants', 'locations', 'plantings'));
    }

    public function update(Request $request, Harvest $harvest)
    {
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'planting_id' => 'nullable|exists:plantings,id',
            'planting_location_id' => 'required|exists:planting_locations,id',
            'harvested_at' => 'required|date',
            'batch_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'source' => 'required|string|max:255',
            'quality' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'loss_quantity' => 'nullable|numeric|min:0',
        ]);

        $harvest->update($data);
        
        return redirect()->route('plants.show', $harvest->plant)
            ->with('success', 'Data panen berhasil diperbarui');
    }

    public function destroy(Harvest $harvest)
    {
        $plant = $harvest->plant;
        $harvest->delete();
        
        return redirect()->route('plants.show', $plant)
            ->with('success', 'Data panen berhasil dihapus');
    }
}





