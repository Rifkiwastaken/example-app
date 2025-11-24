<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\Treatment;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    public function index(PlantingLocation $plantingLocation)
    {
        $treatments = $plantingLocation->treatments()->orderBy('treatment_date', 'desc')->paginate(15);
        return view('planting/planting-locations/treatments/index', compact('plantingLocation', 'treatments'));
    }

    public function create(PlantingLocation $plantingLocation)
    {
        return view('planting/planting-locations/treatments/create', compact('plantingLocation'));
    }

    public function store(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'treatment_type' => 'required|string|max:255',
            'product_detail' => 'nullable|string|max:255',
            'opt_institution' => 'nullable|string|max:255',
            'application_method' => 'required|string|max:255',
            'withholding_period_days' => 'nullable|integer|min:0',
            'technician' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'treatment_date' => 'required|date',
            'treatment_location' => 'nullable|string|max:255',
            'amount_applied' => 'nullable|numeric|min:0',
            'unit_measurement' => 'nullable|string|max:255',
            'total_cost' => 'nullable|numeric|min:0',
            'record_expense' => 'boolean',
            'keywords' => 'nullable|string|max:255',
        ]);

        $data['planting_location_id'] = $plantingLocation->id;
        Treatment::create($data);
        
        return redirect()->route('planting-locations.treatments.index', $plantingLocation)
            ->with('success', 'Data perawatan berhasil ditambahkan');
    }

    public function show(PlantingLocation $plantingLocation, Treatment $treatment)
    {
        return view('planting/planting-locations/treatments/show', compact('plantingLocation', 'treatment'));
    }

    public function edit(PlantingLocation $plantingLocation, Treatment $treatment)
    {
        return view('planting/planting-locations/treatments/edit', compact('plantingLocation', 'treatment'));
    }

    public function update(Request $request, PlantingLocation $plantingLocation, Treatment $treatment)
    {
        $data = $request->validate([
            'treatment_type' => 'required|string|max:255',
            'product_detail' => 'nullable|string|max:255',
            'opt_institution' => 'nullable|string|max:255',
            'application_method' => 'required|string|max:255',
            'withholding_period_days' => 'nullable|integer|min:0',
            'technician' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'treatment_date' => 'required|date',
            'treatment_location' => 'nullable|string|max:255',
            'amount_applied' => 'nullable|numeric|min:0',
            'unit_measurement' => 'nullable|string|max:255',
            'total_cost' => 'nullable|numeric|min:0',
            'record_expense' => 'boolean',
            'keywords' => 'nullable|string|max:255',
        ]);

        $treatment->update($data);
        
        return redirect()->route('planting-locations.treatments.index', $plantingLocation)
            ->with('success', 'Data perawatan berhasil diperbarui');
    }

    public function destroy(PlantingLocation $plantingLocation, Treatment $treatment)
    {
        $treatment->delete();
        
        return redirect()->route('planting-locations.treatments.index', $plantingLocation)
            ->with('success', 'Data perawatan berhasil dihapus');
    }
}













