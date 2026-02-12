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
            'category_custom' => 'nullable|string|max:255',
            'variety' => 'required|string',
        ]);

        // Parse varieties (split by newline or comma), trim, and filter empty
        $varieties = collect(preg_split('/[\n,]+/', $data['variety']))
            ->map(fn($v) => trim($v))
            ->filter(fn($v) => $v !== '')
            ->values();

        // Cek duplikat dalam input yang sama
        $duplicatesInInput = $varieties->duplicates()->values()->unique();
        if ($duplicatesInInput->isNotEmpty()) {
            return $this->validationError($request, 'variety', 'Nama varietas tidak boleh duplikat: ' . $duplicatesInInput->implode(', '));
        }

        // Cek duplikat dengan varietas yang sudah ada di database
        $existingVarieties = PlantType::whereNotNull('variety')
            ->pluck('variety')
            ->flatMap(fn($v) => collect(preg_split('/[\n,]+/', $v))->map(fn($x) => trim($x))->filter(fn($x) => $x !== ''))
            ->map(fn($v) => strtolower($v))
            ->unique()
            ->values();

        $duplicatesInDb = $varieties->filter(fn($v) => $existingVarieties->contains(strtolower($v)));
        if ($duplicatesInDb->isNotEmpty()) {
            return $this->validationError($request, 'variety', 'Nama varietas sudah digunakan: ' . $duplicatesInDb->implode(', '));
        }
        
        // If category is "lainnya", use category_custom value
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
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'category_custom' => 'nullable|string|max:255',
            'variety' => 'required|string',
        ]);

        // Parse varieties (split by newline or comma), trim, filter empty
        $varieties = collect(preg_split('/[\n,]+/', $data['variety']))
            ->map(fn($v) => trim($v))
            ->filter(fn($v) => $v !== '')
            ->values();

        // Cek duplikat dalam input yang sama
        $duplicatesInInput = $varieties->duplicates()->values()->unique();
        if ($duplicatesInInput->isNotEmpty()) {
            return $this->validationError($request, 'variety', 'Nama varietas tidak boleh duplikat: ' . $duplicatesInInput->implode(', '));
        }

        // Cek duplikat dengan varietas di plant type lain (exclude plant type yang sedang diedit)
        $existingVarieties = PlantType::whereNotNull('variety')
            ->where('plant_type_id', '!=', $plantType->plant_type_id)
            ->pluck('variety')
            ->flatMap(fn($v) => collect(preg_split('/[\n,]+/', $v))->map(fn($x) => trim($x))->filter(fn($x) => $x !== ''))
            ->map(fn($v) => strtolower($v))
            ->unique()
            ->values();

        $duplicatesInDb = $varieties->filter(fn($v) => $existingVarieties->contains(strtolower($v)));
        if ($duplicatesInDb->isNotEmpty()) {
            return $this->validationError($request, 'variety', 'Nama varietas sudah digunakan: ' . $duplicatesInDb->implode(', '));
        }
        
        // If category is "lainnya", use category_custom value
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
     * Get variety by plant type ID (API endpoint)
     */
    public function getVariety($id)
    {
        $plantType = PlantType::where('plant_type_id', $id)->first();
        
        if (!$plantType) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe tanaman tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'variety' => $plantType->variety
        ]);
    }
}


















