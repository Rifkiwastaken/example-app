<?php

namespace App\Http\Controllers;

use App\Models\PlantingField;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlantingFieldController extends Controller
{
    public function index(PlantingLocation $plantingLocation)
    {
        $fields = $plantingLocation->fields()->orderBy('kode_lahan')->paginate(15);

        return view('planting.planting-locations.fields.index', compact('plantingLocation', 'fields'));
    }

    public function create(PlantingLocation $plantingLocation)
    {
        return view('planting.planting-locations.fields.create', compact('plantingLocation'));
    }

    public function store(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $this->validatedData($request, $plantingLocation);
        $data['planting_location_id'] = $plantingLocation->planting_location_id;

        PlantingField::create($data);

        return redirect()->route('planting-locations.fields.index', $plantingLocation)
            ->with('success', 'Lahan berhasil ditambahkan');
    }

    public function show(PlantingLocation $plantingLocation, PlantingField $field)
    {
        $this->ensureBelongsToLocation($plantingLocation, $field);

        return view('planting.planting-locations.fields.show', compact('plantingLocation', 'field'));
    }

    public function edit(PlantingLocation $plantingLocation, PlantingField $field)
    {
        $this->ensureBelongsToLocation($plantingLocation, $field);

        return view('planting.planting-locations.fields.edit', compact('plantingLocation', 'field'));
    }

    public function update(Request $request, PlantingLocation $plantingLocation, PlantingField $field)
    {
        $this->ensureBelongsToLocation($plantingLocation, $field);

        $field->update($this->validatedData($request, $plantingLocation, $field));

        return redirect()->route('planting-locations.fields.index', $plantingLocation)
            ->with('success', 'Lahan berhasil diperbarui');
    }

    public function destroy(PlantingLocation $plantingLocation, PlantingField $field)
    {
        $this->ensureBelongsToLocation($plantingLocation, $field);

        $field->delete();

        return redirect()->route('planting-locations.fields.index', $plantingLocation)
            ->with('success', 'Lahan berhasil dihapus');
    }

    protected function validatedData(Request $request, PlantingLocation $plantingLocation, ?PlantingField $field = null): array
    {
        $data = $request->validate([
            'kode_lahan' => [
                'required',
                'string',
                'max:20',
                Rule::unique('planting_fields', 'kode_lahan')
                    ->where('planting_location_id', $plantingLocation->planting_location_id)
                    ->ignore($field?->id),
            ],
            'luas_ha' => 'required|numeric|min:0.01|max:999.99',
            'panjang_m' => 'nullable|numeric|min:0|max:99999.99',
            'lebar_m' => 'nullable|numeric|min:0|max:99999.99',
            'koordinat_gps' => ['nullable', 'string', 'max:100', 'regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/'],
            'peta_lahan' => 'nullable|string',
            'status_lahan' => 'required|in:Digunakan,Bera,Persiapan',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ], [
            'kode_lahan.required' => 'Kode lahan wajib diisi.',
            'kode_lahan.unique' => 'Kode lahan ini sudah dipakai di lokasi ini.',
            'luas_ha.required' => 'Luas lahan wajib diisi.',
            'koordinat_gps.regex' => 'Koordinat GPS harus berformat latitude,longitude. Contoh: -0.9471,100.4172',
            'status_lahan.required' => 'Status lahan wajib dipilih.',
        ]);

        $data['kode_lahan'] = strtoupper(trim($data['kode_lahan']));
        if (empty($data['koordinat_gps'])) {
            $data['koordinat_gps'] = null;
        } else {
            $parts = array_map('trim', explode(',', $data['koordinat_gps']));
            $data['koordinat_gps'] = $parts[0] . ',' . $parts[1];
        }

        $data['peta_lahan'] = $this->normalizedPolygon($data['peta_lahan'] ?? null);
        $data['description'] = $data['description'] ?? null;
        unset($data['file']);
        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $data['file_path'] = $uploaded->store('planting-fields', 'public');
            $data['file_name'] = $uploaded->getClientOriginalName();
        }

        return $data;
    }

    protected function normalizedPolygon(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $points = json_decode($raw, true);
        if (! is_array($points)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'peta_lahan' => 'Data peta lahan tidak valid.',
            ]);
        }

        $normalized = [];
        foreach ($points as $point) {
            $lat = is_array($point) ? ($point['lat'] ?? $point[0] ?? null) : null;
            $lng = is_array($point) ? ($point['lng'] ?? $point[1] ?? null) : null;
            if (! is_numeric($lat) || ! is_numeric($lng)) {
                continue;
            }
            $lat = (float) $lat;
            $lng = (float) $lng;
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }
            $normalized[] = [
                'lat' => round($lat, 6),
                'lng' => round($lng, 6),
            ];
        }

        if (count($normalized) === 0) {
            return null;
        }

        if (count($normalized) !== 4) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'peta_lahan' => 'Peta lahan harus terdiri dari tepat 4 titik batas.',
            ]);
        }

        return $normalized;
    }

    protected function ensureBelongsToLocation(PlantingLocation $plantingLocation, PlantingField $field): void
    {
        if ($field->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404);
        }
    }
}
