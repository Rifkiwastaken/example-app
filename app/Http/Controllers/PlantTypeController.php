<?php

namespace App\Http\Controllers;

use App\Models\PlantType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plant_commodities', 'name'),
            ],
            'category' => 'nullable|string|max:255',
            'category_custom' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama tanaman ini sudah ada. Gunakan nama yang berbeda.',
        ]);

        if ($request->input('category') === 'lainnya') {
            $data['category'] = $request->input('category_custom');
        }
        unset($data['category_custom']);

        $plantType = PlantType::create($data);
        
        // If request is AJAX, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipe tanaman ditambahkan',
                'plant_type' => $plantType
            ]);
        }
        
        return redirect()->route('plant-types.index')->with('success', 'Tipe tanaman ditambahkan');
    }

    /**
     * Return validation error (JSON for AJAX, redirect for form)
     */
    private function validationError(Request $request, string $field, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 422);
        }
        return back()->withErrors([$field => $message])->withInput();
    }

    public function edit(PlantType $plantType)
    {
        return view('planting/types/edit', ['type' => $plantType]);
    }

    public function update(Request $request, PlantType $plantType)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plant_commodities', 'name')->ignore($plantType->seed_commodity_id, 'seed_commodity_id'),
            ],
            'category' => 'nullable|string|max:255',
            'category_custom' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama tanaman ini sudah ada. Gunakan nama yang berbeda.',
        ]);

        if ($request->input('category') === 'lainnya') {
            $data['category'] = $request->input('category_custom');
        }
        unset($data['category_custom']);

        $plantType->update($data);
        
        // If request is AJAX, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipe tanaman diperbarui',
                'plant_type' => $plantType
            ]);
        }
        
        return redirect()->route('plant-types.index')->with('success', 'Tipe tanaman diperbarui');
    }

    public function destroy(PlantType $plantType)
    {
        $plantType->delete();
        return back()->with('success', 'Tipe tanaman dihapus');
    }

    /**
     * Get variety by plant type ID (API endpoint).
     * Varietas tidak lagi disimpan di plant_types; dikembalikan kosong untuk kompatibilitas.
     */
    public function getVariety($id)
    {
        $plantType = PlantType::where('seed_commodity_id', $id)->first();
        if (!$plantType) {
            return response()->json(['success' => false, 'message' => 'Tipe tanaman tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'variety' => null]);
    }
}


















