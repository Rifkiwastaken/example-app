<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLoss;
use App\Models\Task;
use App\Models\PlantingLocationNote;
use App\Jobs\SendTaskNotificationJob;
use App\Jobs\SendNoteNotificationJob;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\Expense;
use App\Models\Attachment;
use App\Models\User;
use App\Models\PlantingField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlantingLocationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PlantingLocation::with(['assignedUsers', 'landWorkerUsers', 'plantings.seedSource.plant.type']);
        
        // Filter: Admin melihat semua; non-admin hanya lokasi penempatan
        if (!$user->isAdmin()) {
            if ($user->placement_location_id) {
                $query->where('planting_location_id', $user->placement_location_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        // Filter by assignment (user) - for admin filter dropdown
        if ($request->filled('assignment')) {
            $userId = $request->input('assignment');
            $query->whereHas('assignedUsers', function($q) use ($userId) {
                $q->where('users.user_id', $userId);
            });
        }
        
        $plantingLocations = $query->orderBy('name')->paginate(15)->withQueryString();
        
        $assignedUsers = User::whereNotNull('placement_location_id')->orderBy('name')->get();
        
        return view('planting/planting-locations/index', compact('plantingLocations', 'assignedUsers'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Only admin and kepala_satuan_tugas can create
        if (!$user->isAdmin() && $user->role !== 'kepala_satuan_tugas') {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan lokasi penanaman.');
        }
        
        $users = User::orderBy('name')->get();
        return view('planting/planting-locations/create', compact('users'));
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Only admin and kepala_satuan_tugas can create
            if (!$user->isAdmin() && $user->role !== 'kepala_satuan_tugas') {
                abort(403, 'Anda tidak memiliki izin untuk menambahkan lokasi penanaman.');
            }
            
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'location_summary' => 'required|string|max:255',
                'province' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'village' => 'required|string|max:100',
                'koordinat_gps' => ['nullable', 'string', 'max:100', 'regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/'],
                'administrative_address' => 'nullable|string',
                'google_maps_link' => 'nullable|url|max:255',
                'primary_photo' => 'nullable|image|max:5120',
                'location_type' => 'required|in:lapangan,sawah,greenhouse,grow_room,padang_rumput,petak_ternak,lainnya',
                'location_type_custom' => 'nullable|string|max:255',
                'planting_format' => 'nullable|in:ditanam_dalam_petak,cover_crop,row_crop,lainnya',
                'planting_format_custom' => 'nullable|string|max:255',
                'num_beds' => 'nullable|integer|min:0',
                'bed_length_m' => 'nullable|numeric|min:0',
                'bed_width_m' => 'nullable|numeric|min:0',
                'map_size' => 'required|string|max:255',
                'light_condition' => 'nullable|string|max:255',
                'light_condition_custom' => 'nullable|string|max:255',
                'land_status' => 'nullable|string|max:255',
                'land_status_custom' => 'nullable|string|max:255',
                'ownership_status' => 'nullable|string|max:255',
                'ownership_status_custom' => 'nullable|string|max:255',
                'water_source' => 'nullable|string|max:255',
                'water_source_custom' => 'nullable|string|max:255',
                'soil_type' => 'nullable|string|max:255',
                'soil_type_custom' => 'nullable|string|max:255',
                'elevation_masl' => 'nullable|integer',
                'description' => 'nullable|string',
                'land_worker_user_ids' => 'nullable|array',
                'land_worker_user_ids.*' => 'nullable|exists:users,user_id',
                'land_worker_roles' => 'nullable|array',
                'land_worker_roles.*' => 'nullable|in:petugas_lapangan,penangkar',
                'fields' => 'nullable|array',
                'fields.*.kode_lahan' => 'nullable|string|max:20',
                'fields.*.luas_ha' => 'nullable|numeric|min:0.01|max:999.99',
                'fields.*.panjang_m' => 'nullable|numeric|min:0|max:99999.99',
                'fields.*.lebar_m' => 'nullable|numeric|min:0|max:99999.99',
                'fields.*.status_lahan' => 'nullable|in:Digunakan,Bera,Persiapan',
                'fields.*.koordinat_gps' => ['nullable', 'string', 'max:100', 'regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/'],
                'fields.*.peta_lahan' => 'nullable|string',
            ], [
                'name.required' => 'Nama lokasi penanaman wajib diisi.',
                'location_summary.required' => 'Alamat wajib diisi.',
                'map_size.required' => 'Luas lokasi penanaman wajib diisi.',
                'koordinat_gps.regex' => 'Koordinat GPS harus berformat latitude,longitude. Contoh: -0.9471,100.4172',
                'fields.*.koordinat_gps.regex' => 'Koordinat GPS lahan harus berformat latitude,longitude.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            if (($data['planting_format'] ?? null) === 'lainnya') {
                $customFormat = trim((string) $request->input('planting_format_custom'));
                if ($customFormat === '') {
                    return back()
                        ->withErrors(['planting_format_custom' => 'Format penanaman lainnya wajib diisi.'])
                        ->withInput();
                }
                $data['planting_format_custom'] = $customFormat;
            } else {
                $data['planting_format_custom'] = null;
            }

            if ($data['location_type'] === 'lainnya') {
                $customLocationType = trim((string) $request->input('location_type_custom'));
                if ($customLocationType === '') {
                    return back()
                        ->withErrors(['location_type_custom' => 'Tipe lahan lainnya wajib diisi.'])
                        ->withInput();
                }
                $data['location_type_custom'] = $customLocationType;
            } else {
                $data['location_type_custom'] = null;
            }

            $data['land_status'] = $this->resolveSelectValue($request, 'land_status');
            $data['ownership_status'] = $this->resolveSelectValue($request, 'ownership_status');
            $data['water_source'] = $this->resolveSelectValue($request, 'water_source');
            $data['soil_type'] = $this->resolveSelectValue($request, 'soil_type');
            $data['light_condition'] = $this->resolveSelectValue($request, 'light_condition', $data['light_condition'] ?? null);
            $data['elevation_masl'] = $request->filled('elevation_masl') ? $data['elevation_masl'] : null;
            $data['administrative_address'] = $this->composedAdministrativeAddress($data);

            unset(
                $data['land_status_custom'],
                $data['ownership_status_custom'],
                $data['water_source_custom'],
                $data['soil_type_custom'],
                $data['light_condition_custom'],
                $data['primary_photo'],
                $data['land_worker_user_ids'],
                $data['land_worker_roles'],
                $data['fields']
            );

            $data['koordinat_gps'] = $this->normalizedGps($data['koordinat_gps'] ?? null);
            if (empty($data['google_maps_link']) && !empty($data['koordinat_gps'])) {
                $data['google_maps_link'] = 'https://www.google.com/maps?q=' . $data['koordinat_gps'];
            }

            if ($request->hasFile('primary_photo')) {
                $data['primary_photo_path'] = $request->file('primary_photo')->store('planting-location', 'public');
            }

            $loc = PlantingLocation::create($data);

            $this->storeNestedFields($request, $loc);

            return redirect()->route('planting-locations.show', $loc)->with('success', 'Lokasi penanaman berhasil ditambahkan');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error creating planting location: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['primary_photo', '_token']),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data lokasi penanaman: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(PlantingLocation $plantingLocation, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this planting location
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Load planting location with relationships
        $plantingLocation->load(['landWorkerUsers']);
        
        return view('planting/planting-locations/show', compact(
            'plantingLocation'
        ));
    }

    public function edit(PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access and can manage
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canManagePlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit lokasi penanaman ini.');
        }
        
        $users = User::orderBy('name')->get();
        $plantingLocation->load(['landWorkerUsers', 'fields']);

        return view('planting/planting-locations/edit', compact('plantingLocation', 'users'));
    }

    public function update(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access and can manage
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canManagePlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengupdate lokasi penanaman ini.');
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location_summary' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'village' => 'required|string|max:100',
            'koordinat_gps' => ['nullable', 'string', 'max:100', 'regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/'],
            'administrative_address' => 'nullable|string',
            'google_maps_link' => 'nullable|url|max:255',
            'primary_photo' => 'nullable|image|max:5120',
            'location_type' => 'required|in:lapangan,sawah,greenhouse,grow_room,padang_rumput,petak_ternak,lainnya',
            'location_type_custom' => 'nullable|string|max:255',
            'planting_format' => 'nullable|in:ditanam_dalam_petak,cover_crop,row_crop,lainnya',
            'planting_format_custom' => 'nullable|string|max:255',
            'num_beds' => 'nullable|integer|min:0',
            'bed_length_m' => 'nullable|numeric|min:0',
            'bed_width_m' => 'nullable|numeric|min:0',
            'map_size' => 'required|string|max:255',
            'light_condition' => 'nullable|string|max:255',
            'light_condition_custom' => 'nullable|string|max:255',
            'land_status' => 'nullable|string|max:255',
            'land_status_custom' => 'nullable|string|max:255',
            'ownership_status' => 'nullable|string|max:255',
            'ownership_status_custom' => 'nullable|string|max:255',
            'water_source' => 'nullable|string|max:255',
            'water_source_custom' => 'nullable|string|max:255',
            'soil_type' => 'nullable|string|max:255',
            'soil_type_custom' => 'nullable|string|max:255',
            'elevation_masl' => 'nullable|integer',
            'description' => 'nullable|string',
            'land_worker_user_ids' => 'nullable|array',
            'land_worker_user_ids.*' => 'exists:users,user_id',
                'land_worker_roles' => 'nullable|array',
                'land_worker_roles.*' => 'nullable|in:petugas_lapangan,penangkar',
                'fields' => 'nullable|array',
                'fields.*.kode_lahan' => 'nullable|string|max:20',
                'fields.*.luas_ha' => 'nullable|numeric|min:0.01|max:999.99',
                'fields.*.panjang_m' => 'nullable|numeric|min:0|max:99999.99',
                'fields.*.lebar_m' => 'nullable|numeric|min:0|max:99999.99',
                'fields.*.status_lahan' => 'nullable|in:Digunakan,Bera,Persiapan',
                'fields.*.koordinat_gps' => ['nullable', 'string', 'max:100', 'regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/'],
                'fields.*.peta_lahan' => 'nullable|string',
            ], [
                'name.required' => 'Nama lokasi penanaman wajib diisi.',
                'location_summary.required' => 'Alamat wajib diisi.',
                'map_size.required' => 'Luas lokasi penanaman wajib diisi.',
            ]);

        if (($data['planting_format'] ?? null) === 'lainnya') {
            $customFormat = trim((string) $request->input('planting_format_custom'));
            if ($customFormat === '') {
                return back()
                    ->withErrors(['planting_format_custom' => 'Format penanaman lainnya wajib diisi.'])
                    ->withInput();
            }
            $data['planting_format_custom'] = $customFormat;
        } else {
            $data['planting_format_custom'] = null;
        }

        if ($data['location_type'] === 'lainnya') {
            $customLocationType = trim((string) $request->input('location_type_custom'));
            if ($customLocationType === '') {
                return back()
                    ->withErrors(['location_type_custom' => 'Tipe lahan lainnya wajib diisi.'])
                    ->withInput();
            }
            $data['location_type_custom'] = $customLocationType;
        } else {
            $data['location_type_custom'] = null;
        }

        $data['land_status'] = $this->resolveSelectValue($request, 'land_status');
        $data['ownership_status'] = $this->resolveSelectValue($request, 'ownership_status');
        $data['water_source'] = $this->resolveSelectValue($request, 'water_source');
        $data['soil_type'] = $this->resolveSelectValue($request, 'soil_type');
        $data['light_condition'] = $this->resolveSelectValue($request, 'light_condition', $data['light_condition'] ?? null);
        $data['elevation_masl'] = $request->filled('elevation_masl') ? $data['elevation_masl'] : null;
        $data['administrative_address'] = $this->composedAdministrativeAddress($data);

        unset(
            $data['land_status_custom'],
            $data['ownership_status_custom'],
            $data['water_source_custom'],
            $data['soil_type_custom'],
            $data['light_condition_custom'],
            $data['primary_photo'],
            $data['land_worker_user_ids'],
            $data['land_worker_roles'],
            $data['fields']
        );

        $data['koordinat_gps'] = $this->normalizedGps($data['koordinat_gps'] ?? null);
        if (empty($data['google_maps_link']) && !empty($data['koordinat_gps'])) {
            $data['google_maps_link'] = 'https://www.google.com/maps?q=' . $data['koordinat_gps'];
        }

        if ($request->hasFile('primary_photo')) {
            if ($plantingLocation->primary_photo_path) {
                Storage::disk('public')->delete($plantingLocation->primary_photo_path);
            }

            $data['primary_photo_path'] = $request->file('primary_photo')->store('planting-location', 'public');
        }

        $plantingLocation->update($data);

        $this->storeNestedFields($request, $plantingLocation);

        return redirect()->route('planting-locations.show', $plantingLocation)->with('success', 'Lokasi penanaman diperbarui');
    }

    public function destroy(PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access and can manage
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canManagePlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus lokasi penanaman ini.');
        }
        
        $plantingLocation->delete();
        return back()->with('success', 'Lokasi penanaman dihapus');
    }

    protected function resolveSelectValue(Request $request, string $field, ?string $default = null): ?string
    {
        $value = $request->input($field);

        if ($value === '_custom') {
            $custom = trim((string) $request->input("{$field}_custom"));

            return $custom !== '' ? $custom : null;
        }

        return $value ?? $default;
    }

    protected function normalizedGps(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $parts = array_map('trim', explode(',', $value));
        if (count($parts) < 2) {
            return null;
        }

        return $parts[0] . ',' . $parts[1];
    }

    protected function composedAdministrativeAddress(array $data): ?string
    {
        $parts = array_filter([
            ($data['village'] ?? null) ? 'Desa/Kelurahan ' . $data['village'] : null,
            ($data['district'] ?? null) ? 'Kec. ' . $data['district'] : null,
            $data['province'] ?? null,
        ]);

        return $parts === [] ? ($data['administrative_address'] ?? null) : implode(', ', $parts);
    }

    protected function storeNestedFields(Request $request, PlantingLocation $location): void
    {
        $rows = $request->input('fields', []);
        if (! is_array($rows) || $rows === []) {
            return;
        }

        $errors = [];
        $seenCodes = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $kode = strtoupper(trim((string) ($row['kode_lahan'] ?? '')));
            $luas = $row['luas_ha'] ?? null;
            $status = $row['status_lahan'] ?? '';
            $gps = $row['koordinat_gps'] ?? null;
            $peta = $row['peta_lahan'] ?? null;

            $isEmpty = $kode === ''
                && ($luas === null || $luas === '')
                && $status === ''
                && (empty($gps) || trim((string) $gps) === '')
                && (empty($peta) || trim((string) $peta) === '');

            if ($isEmpty) {
                continue;
            }

            if ($kode === '') {
                $errors["fields.$index.kode_lahan"] = 'Kode lahan wajib diisi.';
            }
            if ($luas === null || $luas === '') {
                $errors["fields.$index.luas_ha"] = 'Luas lahan wajib diisi.';
            }
            if ($status === '') {
                $errors["fields.$index.status_lahan"] = 'Status lahan wajib dipilih.';
            }

            if ($kode !== '') {
                if (isset($seenCodes[$kode])) {
                    $errors["fields.$index.kode_lahan"] = 'Kode lahan tidak boleh duplikat pada form ini.';
                }
                $seenCodes[$kode] = true;

                $exists = PlantingField::where('planting_location_id', $location->planting_location_id)
                    ->where('kode_lahan', $kode)
                    ->exists();
                if ($exists) {
                    $errors["fields.$index.kode_lahan"] = 'Kode lahan ini sudah dipakai di lokasi ini.';
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $kode = strtoupper(trim((string) ($row['kode_lahan'] ?? '')));
            $luas = $row['luas_ha'] ?? null;
            $status = $row['status_lahan'] ?? '';
            if ($kode === '' || $luas === null || $luas === '' || $status === '') {
                continue;
            }

            PlantingField::create([
                'planting_location_id' => $location->planting_location_id,
                'kode_lahan' => $kode,
                'luas_ha' => $luas,
                'panjang_m' => ($row['panjang_m'] ?? '') === '' ? null : $row['panjang_m'],
                'lebar_m' => ($row['lebar_m'] ?? '') === '' ? null : $row['lebar_m'],
                'status_lahan' => $status,
                'koordinat_gps' => $this->normalizedGps($row['koordinat_gps'] ?? null),
                'peta_lahan' => $this->normalizedNestedPolygon($row['peta_lahan'] ?? null, 'peta_lahan'),
            ]);
        }
    }

    protected function normalizedNestedPolygon(?string $raw, string $field): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $points = json_decode($raw, true);
        if (! is_array($points)) {
            throw ValidationException::withMessages([
                $field => 'Data peta lahan tidak valid.',
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
            throw ValidationException::withMessages([
                $field => 'Peta lahan harus terdiri dari tepat 4 titik batas.',
            ]);
        }

        return $normalized;
    }

    // Store new planting in this location
    public function storePlanting(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access and can manage
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canManagePlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan penanaman.');
        }
        
        try {
            $data = $request->validate([
                'plant_id' => 'required|exists:plant_varieties,seed_varieties_id',
                'seed_source_id' => 'required|exists:plant_seed_source,seed_source_id',
                'planting_field_id' => 'required|exists:planting_fields,id',
                'planting_batch_number' => 'required|string|max:255|unique:planting_production,planting_batch_number',
                'planted_at' => 'required|date',
                'planting_amount' => 'required|numeric|min:0.01',
                'estimated_harvest_date' => 'required|date|after_or_equal:planted_at',
                'target_kelas' => 'nullable|in:BS,FS,SS,ES',
                'description' => 'nullable|string',
                'file' => 'nullable|file|max:10240',
            ], [
                'planting_batch_number.required' => 'Nomor batch tanam wajib diisi.',
                'planting_batch_number.unique' => 'Nomor batch tanaman sudah digunakan.',
                'planting_amount.required' => 'Jumlah benih yang ditanam wajib diisi.',
            ]);

        $field = \App\Models\PlantingField::findOrFail($data['planting_field_id']);
        if ($field->planting_location_id !== $plantingLocation->planting_location_id) {
            return back()->withErrors(['planting_field_id' => 'Lahan tidak termasuk lokasi penanaman ini.'])->withInput();
        }
        $seedSource = \App\Models\SeedSource::findOrFail($data['seed_source_id']);
        if ($seedSource->seed_varieties_id !== $data['plant_id']) {
            return back()->withErrors(['seed_source_id' => 'Benih sumber tidak sesuai dengan tanaman yang dipilih.'])->withInput();
        }
        if ((float) $seedSource->quantity_kg <= 0) {
            return back()->withErrors(['seed_source_id' => 'Benih sumber ini tidak tersedia.'])->withInput();
        }
        if ((float) $seedSource->quantity_kg < (float) $data['planting_amount']) {
            return back()->withErrors(['planting_amount' => 'Jumlah benih yang ditanam melebihi sisa benih sumber.'])->withInput();
        }
        $plant = \App\Models\Plant::with('satuanPanen')->find($data['plant_id']);
        $data['satuan_panen_id'] = $plant?->satuan_panen_id;
        unset($data['plant_id']);
        $file = $request->file('file');
        if ($file) {
            $data['file_path'] = $file->store('planting-production', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        }
        $data['status'] = Planting::STATUS_PERENCANAAN;
        $data['is_completed'] = false;

        $planting = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $seedSource) {
            $seedSource->decrement('quantity_kg', $data['planting_amount']);
            return Planting::create($data);
        });
            
            // Redirect to current plantings page
            return redirect()->route('planting-locations.plantings.index', $plantingLocation)
                ->with('success', 'Produksi penanaman berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
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

    // Store loss for a planting
    public function storeLoss(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Only kepala_satuan_tugas can add loss data
        if (!$user->canManagePlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan data kehilangan.');
        }
        
        $data = $request->validate([
            'planting_id' => 'required|exists:planting_production,planting_production_id',
            'loss_date' => 'required|date',
            'loss_amount' => 'required|numeric|min:0.01',
            'loss_reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $planting = Planting::findOrFail($data['planting_id']);

        try {
            $loss = PlantingLoss::create($data);
            
            // Redirect to current plantings page to show updated data
            return redirect()->route('planting-locations.plantings.index', $plantingLocation)
                ->with('success', 'Kehilangan berhasil dicatat. Penanaman tetap aktif dan dapat dilanjutkan.');
        } catch (\Exception $e) {
            \Log::error('Error creating planting loss: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['_token'])
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data kehilangan: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Store task for this location
    public function storeTask(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Both kepala_satuan_tugas and penangkar can add tasks (in pelaporan)
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan laporan.');
        }
        
        $actionType = $request->input('action_type', 'create');
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_report' => 'nullable|string',
            'checklist' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'planting_id' => 'nullable',
            'new_status' => 'required|in:selesai,dalam_progress,tidak_selesai',
            'assigned_to' => 'nullable',
            'created_by' => 'nullable|exists:users,user_id',
            'new_priority' => 'required|in:tertinggi,tinggi,medium,rendah,sangat_rendah',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'task_color' => 'nullable|string|max:7',
            'collaborators' => 'nullable|array',
            'repeats' => 'nullable|string',
            'hours_spent' => 'nullable|numeric|min:0',
        ]);

        // Handle checklist - convert from JSON string to array
        if ($request->filled('checklist')) {
            $checklist = json_decode($request->checklist, true);
            $data['checklist'] = is_array($checklist) ? $checklist : [];
        } else {
            $data['checklist'] = [];
        }

        // Handle planting_id and association
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            // Validate that planting_id exists and belongs to this location
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            if ($planting) {
                $data['planting_id'] = $planting->planting_id;
            } else {
                $data['planting_id'] = null; // Set to null if not found (umum)
            }
        } else {
            // "Umum" or empty = null (applies to all plantings in this location)
            $data['planting_id'] = null;
        }
        
        $data['association'] = 'penanaman';

        // Siapkan daftar user di lokasi (pekerja lahan) + admin
        $landWorkers = $plantingLocation->landWorkerUsers;
        $locationUsers = $landWorkers->unique('user_id');
        $adminUsers = \App\Models\User::where('role', 'admin')->get();
        $allUsers = $locationUsers->merge($adminUsers)->unique('user_id');

        // Handle assigned_to:
        // - Jika dipilih user tertentu -> hanya user itu yang ditugaskan
        // - Jika dipilih "semua_user" -> semua user di lokasi + admin
        // - Jika dikosongkan -> otomatis dianggap "semua user"
        if ($request->filled('assigned_to') && $request->assigned_to !== 'semua_user') {
            // Penugasan ke satu user
            $data['assigned_to'] = $request->assigned_to;
            $data['collaborators'] = null;
        } else {
            // "semua_user" atau kosong -> semua user
            $data['assigned_to'] = null;
            $data['collaborators'] = $allUsers->pluck('user_id')->toArray();
        }

        // Handle created_by - default to current user if not provided
        if (!$request->filled('created_by')) {
            $data['created_by'] = auth()->user()->user_id;
        }

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('task-attachments', 'public');
            }
            $data['attachments'] = $attachments;
        }

        // If saving as template
        if ($actionType === 'save_template') {
            $templateData = [
                'name' => $data['title'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'association' => 'penanaman',
                'is_active' => true,
                'checklist' => isset($data['checklist']) && is_array($data['checklist']) ? $data['checklist'] : [],
                'attachments' => isset($data['attachments']) && is_array($data['attachments']) ? $data['attachments'] : [],
            ];

            try {
                if (! \Illuminate\Support\Facades\Schema::hasTable('task_templates')) {
                    return redirect()->back()->with('error', 'Template tugas tidak tersedia.');
                }
                \App\Models\TaskTemplate::create($templateData);
                return redirect()->route('planting-locations.show', $plantingLocation)
                    ->with('success', 'Template laporan berhasil disimpan');
            } catch (\Throwable $e) {
                \Log::error('Save task as template failed', [
                    'planting_location' => $plantingLocation->planting_location_id,
                    'data' => $templateData,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Gagal menyimpan template: ' . $e->getMessage()]);
            }
        }
        
        $task = Task::create($data);
        
        // Send email notifications to assigned users
        if ($task) {
            $userIds = [];
            if (!empty($data['assigned_to'])) {
                $userIds = [$data['assigned_to']];
            } elseif (!empty($data['collaborators'])) {
                $userIds = $data['collaborators'];
            }
            if (!empty($userIds)) {
                SendTaskNotificationJob::dispatch($task, $userIds);
            }
        }
        
        // If saving and filling report immediately
        if ($actionType === 'save_and_fill_report') {
            $fromPlantingReports = $request->input('from_planting_reports', false);
            $plantingId = $request->input('planting_id_for_redirect');
            
            if ($fromPlantingReports && $plantingId) {
                $planting = \App\Models\Planting::find($plantingId);
                if ($planting) {
                    return redirect()->route('planting-locations.plantings.reports', [$plantingLocation, $planting])
                        ->with('success', 'Tugas berhasil ditambahkan')
                        ->with('fill_task_id', $task->planting_task_id);
                }
            }
            
            return redirect()->route('planting-locations.show', $plantingLocation)
                ->with('success', 'Tugas berhasil ditambahkan')
                ->with('fill_task_id', $task->planting_task_id);
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect()->route('planting-locations.plantings.reports', [$plantingLocation, $planting])
                    ->with('success', 'Tugas berhasil ditambahkan');
            }
        }
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Tugas berhasil ditambahkan');
    }

    // API: Get task template
    public function getTaskTemplate($templateId)
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('task_templates')) {
            abort(404);
        }
        $template = \App\Models\TaskTemplate::findOrFail($templateId);
        return response()->json($template);
    }

    // Update task template
    public function updateTaskTemplate(Request $request, $templateId)
    {
        $template = \App\Models\TaskTemplate::findOrFail($templateId);
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $template->update($data);

        return redirect()->back()
            ->with('success', 'Template berhasil diperbarui');
    }

    // Delete task template
    public function deleteTaskTemplate($templateId)
    {
        $template = \App\Models\TaskTemplate::findOrFail($templateId);
        $template->delete();

        return redirect()->back()
            ->with('success', 'Template berhasil dihapus');
    }

    // Update task status
    public function updateTaskStatus(Request $request, PlantingLocation $plantingLocation, Task $task)
    {
        $request->validate([
            'new_status' => 'required|in:selesai,dalam_progress,tidak_selesai',
        ]);

        // Verify task belongs to this planting location via planting
        if (!$task->planting_id || !$task->planting || $task->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            return redirect()->route('planting-locations.show', $plantingLocation)
                ->with('error', 'Tugas tidak ditemukan.');
        }

        $task->update(['new_status' => $request->new_status]);

        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Status tugas berhasil diperbarui.');
    }

    // View task details
    public function viewTask(PlantingLocation $plantingLocation, Task $task)
    {
        try {
            $user = auth()->user();
            
            // Check if user has access
            if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
                return response()->json([
                    'error' => 'Anda tidak memiliki akses ke lokasi penanaman ini.'
                ], 403);
            }

            // Verify task belongs to this planting location via planting
            if (!$task->planting_id || !$task->planting || $task->planting->planting_location_id !== $plantingLocation->planting_location_id) {
                return response()->json([
                    'error' => 'Tugas tidak ditemukan.'
                ], 404);
            }

            // Load relationships, but handle assigned_to carefully since it might be an array
            $task->load(['createdByUser', 'lastEditedByUser', 'plantingLocation', 'planting.plant']);
            
            // Only load assignedUser if assigned_to is not an array
            $assignedUser = null;
            if ($task->assigned_to && !is_array($task->assigned_to)) {
                try {
                    $task->load('assignedUser');
                    $assignedUser = $task->assignedUser ? ['id' => $task->assignedUser->user_id, 'name' => $task->assignedUser->name] : null;
                } catch (\Exception $e) {
                    // If relationship fails, set to null
                    \Log::warning('Failed to load assignedUser for task ' . $task->planting_task_id . ': ' . $e->getMessage());
                    $assignedUser = null;
                }
            }

            // Handle start_time format
            $startTime = null;
            if ($task->start_time) {
                if (is_string($task->start_time)) {
                    $startTime = $task->start_time;
                } elseif ($task->start_time instanceof \DateTime || $task->start_time instanceof \Carbon\Carbon) {
                    $startTime = $task->start_time->format('H:i');
                } else {
                    try {
                        $startTime = \Carbon\Carbon::parse($task->start_time)->format('H:i');
                    } catch (\Exception $e) {
                        $startTime = null;
                    }
                }
            }

            return response()->json([
                'task' => [
                    'id' => $task->planting_task_id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'task_report' => $task->task_report,
                    'new_status' => $task->new_status,
                    'status_label' => $task->status_label,
                    'new_priority' => $task->new_priority,
                    'priority_label' => $task->priority_label,
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                    'start_time' => $startTime,
                    'checklist' => $task->checklist ?? [],
                    'attachments' => $task->attachments ?? [],
                    'assigned_to' => $task->assigned_to,
                    'created_by' => $task->created_by,
                    'planting_id' => $task->planting_id,
                    'last_edited_at' => $task->last_edited_at ? $task->last_edited_at->toDateTimeString() : null,
                    'assigned_user' => $assignedUser,
                    'created_by_user' => $task->createdByUser ? ['id' => $task->createdByUser->user_id, 'name' => $task->createdByUser->name] : null,
                    'last_edited_by_user' => $task->lastEditedByUser ? ['id' => $task->lastEditedByUser->user_id, 'name' => $task->lastEditedByUser->name] : null,
                    'planting' => $task->planting ? [
                        'id' => $task->planting->planting_id,
                        'plant_id' => $task->planting->plant_id,
                        'bed_label' => $task->planting->bed_label,
                        'plant' => $task->planting->plant ? ['id' => $task->planting->plant->plant_id, 'name' => $task->planting->plant->name] : null,
                    ] : null,
                ],
                'can_edit' => $user->isAdmin() || $user->canManageDataInPelaporan($plantingLocation),
                'can_fill' => $user->isAdmin() || $user->canAddDataInPelaporan($plantingLocation),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in viewTask: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Gagal memuat data laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Edit task
    public function editTask(PlantingLocation $plantingLocation, Task $task)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }

        // Only admin and kepala_satuan_tugas can edit
        if (!$user->isAdmin() && !$user->canManageDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit laporan ini.');
        }

        // Verify task belongs to this planting location via planting
        if (!$task->planting_id || !$task->planting || $task->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Tugas tidak ditemukan.');
        }

        try {
            $task->load(['assignedUser', 'createdByUser', 'lastEditedByUser', 'planting.plant']);

            return response()->json([
                'task' => [
                    'id' => $task->planting_task_id,
                    'title' => $task->title ?? '',
                    'description' => $task->description ?? '',
                    'task_report' => $task->task_report ?? '',
                    'new_status' => $task->new_status ?? 'dalam_progress',
                    'new_priority' => $task->new_priority ?? 'medium',
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    'assigned_to' => $task->assigned_to,
                    'planting_id' => $task->planting_id,
                    'planting' => $task->planting ? [
                        'id' => $task->planting->planting_id,
                        'plant_id' => $task->planting->plant_id ?? null,
                    ] : null,
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            \Log::error('Error in editTask: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat data laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update task
    public function updateTask(Request $request, PlantingLocation $plantingLocation, Task $task)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }

        // Only admin and kepala_satuan_tugas can edit
        if (!$user->isAdmin() && !$user->canManageDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit laporan ini.');
        }

        // Verify task belongs to this planting location via planting
        if (!$task->planting_id || !$task->planting || $task->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Tugas tidak ditemukan.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_report' => 'nullable|string',
            'checklist' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'planting_id' => 'nullable',
            'new_status' => 'required|in:selesai,dalam_progress,tidak_selesai',
            'assigned_to' => 'nullable|exists:users,user_id',
            'new_priority' => 'required|in:tertinggi,tinggi,medium,rendah,sangat_rendah',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'task_color' => 'nullable|string|max:7',
            'collaborators' => 'nullable|array',
            'repeats' => 'nullable|string',
            'hours_spent' => 'nullable|numeric|min:0',
        ]);

        // Handle checklist
        if ($request->filled('checklist')) {
            $checklist = json_decode($request->checklist, true);
            $data['checklist'] = is_array($checklist) ? $checklist : [];
        } else {
            $data['checklist'] = [];
        }

        // Handle planting_id and "umum" option
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $data['planting_id'] = $planting ? $planting->planting_id : null;
            } else {
            $data['planting_id'] = null; // "Umum" = applies to all plantings
        }

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('task-attachments', 'public');
            }
            $data['attachments'] = $attachments;
        }

        // Update last edited info
        $data['last_edited_at'] = now();
        $data['last_edited_by'] = $user->user_id;

        $task->update($data);

        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#laporan-subtab')
                    ->with('success', 'Laporan berhasil diperbarui.');
            }
        }

        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    // Delete task
    public function deleteTask(PlantingLocation $plantingLocation, Task $task)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }

        // Only admin and kepala_satuan_tugas can delete
        if (!$user->isAdmin() && !$user->canManageDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus laporan ini.');
        }

        // Verify task belongs to this planting location via planting
        if (!$task->planting_id || !$task->planting || $task->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Tugas tidak ditemukan.');
        }

        $task->delete();

        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Laporan berhasil dihapus.');
    }

    // Fill task report (for penangkar)
    public function fillTaskReport(Request $request, PlantingLocation $plantingLocation, Task $task)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengisi laporan ini.');
        }

        // Both admin, kepala_satuan_tugas, and penangkar can fill report (dengan batasan penugasan)
        if (!$user->isAdmin() && !$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengisi laporan ini.');
        }

        // Verify task belongs to this planting location via planting
        if (!$task->planting_id || !$task->planting || $task->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Tugas tidak ditemukan.');
        }

        // Jika tugas ditugaskan ke user tertentu, hanya user tersebut (atau admin) yang boleh mengisi laporan
        if (!empty($task->assigned_to) && $user->user_id !== $task->assigned_to && !$user->isAdmin()) {
            abort(403, 'Laporan ini hanya dapat diisi oleh user yang ditugaskan.');
        }

        $data = $request->validate([
            'task_report' => 'required|string',
            'new_status' => 'required|in:selesai,dalam_progress,tidak_selesai',
            'checklist' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        // Handle checklist - convert from JSON string to array
        if ($request->filled('checklist')) {
            $checklist = json_decode($request->checklist, true);
            $data['checklist'] = is_array($checklist) ? $checklist : [];
        } else {
            // Keep existing checklist if not provided
            $data['checklist'] = $task->checklist ?? [];
        }

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $existingAttachments = $task->attachments ?? [];
            $newAttachments = [];
            foreach ($request->file('attachments') as $file) {
                $newAttachments[] = $file->store('task-attachments', 'public');
            }
            // Merge with existing attachments
            $data['attachments'] = array_merge($existingAttachments, $newAttachments);
        } else {
            // Keep existing attachments if not provided
            $data['attachments'] = $task->attachments ?? [];
        }

        // Prepare update data (task_report, start_date, start_time wajib diisi)
        $updateData = [
            'task_report' => $data['task_report'],
            'new_status' => $data['new_status'],
            'start_date' => $data['start_date'],
            'start_time' => $data['start_time'],
        ];
        if (isset($data['checklist'])) {
            $updateData['checklist'] = $data['checklist'];
        }
        if (isset($data['attachments'])) {
            $updateData['attachments'] = $data['attachments'];
        }

        // Pembuat laporan = user yang saat ini mengisi form (otomatis, tidak dari input form)
        $updateData['created_by'] = $user->user_id;

        // For penangkar, only update task_report, new_status, checklist, start_date, start_time, and attachments
        if ($user->role === 'penangkar') {
            $task->update($updateData);
        } else {
            // For admin and kepala_satuan_tugas, can update more fields
            $updateData['last_edited_at'] = now();
            $updateData['last_edited_by'] = $user->user_id;
            $task->update($updateData);
        }

        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#laporan-subtab')
                    ->with('success', 'Laporan berhasil diisi.');
            }
        }

        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Laporan berhasil diisi.');
    }

    // Store note for this location
    public function storeNote(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Both kepala_satuan_tugas and penangkar can add notes (in pelaporan)
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan catatan.');
        }
        
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'note_date' => 'required|date',
            'keywords' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'assigned_to' => 'nullable',
            'planting_id' => 'nullable',
        ]);

        $data['user_id'] = auth()->user()->user_id;
        
        // Handle planting_id - save to link note to specific planting
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $data['planting_id'] = $planting ? $planting->planting_id : null;
        } else {
            $data['planting_id'] = null; // "Umum" = applies to all plantings
        }
        
        // Handle assigned_to - now single select, convert to array
        if ($request->has('assigned_to') && $request->assigned_to) {
            if ($request->assigned_to === 'all') {
                // Get all users related to this planting location (land workers and admins)
                $landWorkers = $plantingLocation->landWorkerUsers;
                $locationUsers = $landWorkers->unique('user_id');
                $adminUsers = \App\Models\User::where('role', 'admin')->get();
                $allUsers = $locationUsers->merge($adminUsers)->unique('user_id');
                $data['assigned_to'] = $allUsers->pluck('user_id')->toArray();
            } else {
                // Validate that it's a valid user ID
                $request->validate([
                    'assigned_to' => 'exists:users,user_id',
                ]);
                $data['assigned_to'] = [$request->assigned_to]; // Convert to array for compatibility
            }
        } else {
            $data['assigned_to'] = null;
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('planting-location-notes', 'public');
        }

        $note = PlantingLocationNote::create($data);
        
        // Send email notifications to assigned users
        if ($note && !empty($data['assigned_to'])) {
            $userIds = is_array($data['assigned_to']) ? $data['assigned_to'] : [$data['assigned_to']];
            if ($userIds) {
                SendNoteNotificationJob::dispatch($note, $userIds);
            }
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#catatan-subtab')
                    ->with('success', 'Catatan berhasil ditambahkan');
            }
        }
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Catatan berhasil ditambahkan');
    }

    // View note detail
    public function viewNote(PlantingLocation $plantingLocation, PlantingLocationNote $note)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Verify note belongs to this planting location (via planting)
        if (!$note->planting_id || !$note->planting || $note->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Catatan tidak ditemukan.');
        }
        
        // Load relationships
        $note->load(['user', 'planting.plantingLocation']);
        
        // Get assigned users
        $assignedUsers = $note->assignedUsers();
        
        // Check if current user has read this note
        $isRead = $note->isReadBy($user->user_id);
        
        // If note is assigned to current user and not read yet, mark as read
        if ($note->isAssignedTo($user->user_id) && !$isRead) {
            $note->markAsReadBy($user->user_id);
            $isRead = true;
        }
        
        if (request()->expectsJson()) {
            return response()->json([
                'note' => [
                    'id' => $note->planting_note_id,
                    'title' => $note->title,
                    'description' => $note->description,
                    'note_date' => $note->note_date->format('d M Y'),
                    'keywords' => $note->keywords,
                    'attachment_path' => $note->attachment_path,
                    'user' => $note->user ? ['id' => $note->user->user_id, 'name' => $note->user->name] : null,
                    'planting_location' => $note->planting?->plantingLocation ? ['id' => $note->planting->plantingLocation->planting_location_id, 'name' => $note->planting->plantingLocation->name] : null,
                    'assigned_users' => $assignedUsers->map(function($u) {
                        return ['id' => $u->user_id, 'name' => $u->name];
                    })->values(),
                    'is_read' => $isRead,
                    'read_by' => $note->read_by ?? []
                ]
            ]);
        }
        
        return view('planting.planting-locations.notes.show', compact('plantingLocation', 'note', 'assignedUsers', 'isRead'));
    }

    // Mark note as read
    public function markNoteAsRead(PlantingLocation $plantingLocation, PlantingLocationNote $note)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Verify note belongs to this planting location (via planting)
        if (!$note->planting_id || !$note->planting || $note->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Catatan tidak ditemukan.');
        }
        
        // Check if note is assigned to user
        if (!$note->isAssignedTo($user->user_id)) {
            abort(403, 'Catatan ini tidak ditugaskan kepada Anda.');
        }
        
        // Mark as read
        $note->markAsReadBy($user->user_id);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan telah ditandai sebagai sudah dibaca'
            ]);
        }
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Catatan telah ditandai sebagai sudah dibaca');
    }

    public function storeAttachment(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Both kepala_satuan_tugas and penangkar can add attachments (in pelaporan)
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan lampiran.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'attachment_type' => 'nullable|string|max:255',
            'plant_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachment_date' => 'required|date',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filePath = $file->store('attachments', 'public');

        Attachment::create([
            'module' => Attachment::MODULE_LOCATION,
            'planting_location_id' => $plantingLocation->planting_location_id,
            'seed_varieties_id' => null,
            'stock_id' => null,
            'title' => $request->title,
            'description' => $request->description,
            'attachment_date' => $request->attachment_date,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'created_by' => $user->user_id,
        ]);
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingIdForRedirect = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingIdForRedirect) {
            $planting = \App\Models\Planting::find($plantingIdForRedirect);
            if ($planting) {
                return redirect(route('planting-locations.attachments.index', $plantingLocation))
                    ->with('success', 'Lampiran berhasil ditambahkan');
            }
        }

        return redirect()->route('planting-locations.attachments.index', $plantingLocation)
            ->with('success', 'Lampiran berhasil ditambahkan');
    }

    public function updateAttachment(Request $request, PlantingLocation $plantingLocation, Attachment $attachment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if ($attachment->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Lampiran tidak ditemukan.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit lampiran.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'attachment_type' => 'nullable|string|max:255',
            'plant_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachment_date' => 'required|date',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $data = [
            'title' => $request->title,
            'attachment_type' => $request->attachment_type,
            'plant_type' => $request->plant_type,
            'description' => $request->description,
            'attachment_date' => $request->attachment_date,
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            
            $file = $request->file('file');
            $data['file_path'] = $file->store('planting-location-attachments', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }

        $attachment->update($data);
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.attachments.index', $plantingLocation))
                    ->with('success', 'Lampiran berhasil diperbarui');
            }
        }

        return redirect()->route('planting-locations.attachments.index', $plantingLocation)
            ->with('success', 'Lampiran berhasil diperbarui');
    }

    public function destroyAttachment(PlantingLocation $plantingLocation, Attachment $attachment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if ($attachment->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Lampiran tidak ditemukan.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus lampiran.');
        }
        
        // Delete file
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $attachment->delete();
        
        return redirect()->route('planting-locations.attachments.index', $plantingLocation)
            ->with('success', 'Lampiran berhasil dihapus');
    }

    public function showAttachment(PlantingLocation $plantingLocation, Attachment $attachment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if ($attachment->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Lampiran tidak ditemukan.');
        }
        
        $attachment->load(['creator']);
        
        return response()->json([
            'id' => $attachment->getKey(),
            'title' => $attachment->title,
            'attachment_type' => $attachment->attachment_type,
            'plant_type' => $attachment->plant_type,
            'description' => $attachment->description,
            'attachment_date' => $attachment->attachment_date?->format('Y-m-d'),
            'file_path' => $attachment->file_path,
            'file_name' => $attachment->file_name,
            'created_by' => $attachment->created_by,
            'edited_at' => null,
            'edited_by' => null,
            'creator' => $attachment->creator ? [
                'id' => $attachment->creator->user_id,
                'name' => $attachment->creator->name,
            ] : null,
            'editor' => null,
        ]);
    }

    // Mark planting as failed
    public function markPlantingFailed(PlantingLocation $plantingLocation, Planting $planting)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Only kepala_satuan_tugas can mark as failed
        if (!$user->canManagePlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menandai penanaman sebagai gagal.');
        }
        
        // Mark planting as completed so it no longer appears in the active "Penanaman" list
        // Record will still appear in "Riwayat Penanaman" > "Gagal panen"
        $planting->update([
            'is_completed' => true,
            'completed_at' => now(),
            'description' => trim(($planting->description ? $planting->description."\n" : '').'Ditandai sebagai gagal panen.'),
        ]);
        
        return redirect()->route('planting-locations.plantings.index', $plantingLocation)
            ->with('success', 'Penanaman ditandai sebagai gagal panen dan tidak lagi ditampilkan di daftar penanaman aktif.');
    }

    // Store treatment for this location
    public function storeTreatment(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Both kepala_satuan_tugas and penangkar can add treatments (in pelaporan)
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan data perawatan.');
        }
        
        $data = $request->validate([
            'treatment_name' => 'required|string|max:255',
            'treatment_type' => 'required|string|max:255',
            'product_detail' => 'nullable|string|max:255',
            'responsible_person_id' => 'required|exists:users,user_id',
            'application_method' => 'required|string|max:255',
            'withholding_period_days' => 'nullable|integer|min:0',
            'technician' => 'nullable|string|max:255',
            'institution_source' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
            'description' => 'nullable|string',
            'treatment_date' => 'required|date',
            'batch_number' => 'nullable|string|max:255',
            'amount_applied' => 'nullable|numeric|min:0',
            'treatment_location' => 'nullable|string|max:255',
            'retreat_date' => 'nullable|date',
            'total_cost' => 'required|numeric|min:0',
            'keywords' => 'nullable|string|max:255',
            'planting_id' => 'nullable',
            'unit_measurement' => 'nullable|string|max:255',
        ]);

        // Handle "umum" option for planting_id
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $data['planting_id'] = $planting ? $planting->planting_id : null;
        } else {
            $data['planting_id'] = null; // "Umum" = applies to all plantings
        }
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('treatments/attachments', $filename, 'public');
            $data['attachment'] = $path;
        }
        
        $treatment = Treatment::create($data);
        
        // Auto-create expense if total_cost is provided
        if ($treatment->total_cost && $treatment->total_cost > 0) {
            Expense::create([
                'planting_location_id' => $plantingLocation->planting_location_id,
                'expense_name' => $treatment->treatment_name,
                'amount' => $treatment->total_cost,
                'expense_type' => 'perawatan',
                'expense_date' => $treatment->treatment_date,
                'responsible_person_id' => $treatment->responsible_person_id,
                'planting_treatment_id' => $treatment->planting_treatment_id,
                'planting_id' => $treatment->planting_id,
            ]);
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#perawatan-subtab')
                    ->with('success', 'Data perawatan berhasil ditambahkan');
            }
        }
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=perawatan#pelaporan')
            ->with('success', 'Data perawatan berhasil ditambahkan');
    }

    public function showTreatment(PlantingLocation $plantingLocation, Treatment $treatment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Treatment must belong to this location via planting
        if (!$treatment->planting_id || !$treatment->planting || $treatment->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Data perawatan tidak ditemukan.');
        }
        
        $treatment->load(['planting.plant', 'responsiblePerson', 'editor']);
        
        return response()->json([
            'id' => $treatment->planting_treatment_id,
            'treatment_name' => $treatment->treatment_name,
            'treatment_type' => $treatment->treatment_type,
            'treatment_date' => $treatment->treatment_date->format('Y-m-d'),
            'product_detail' => $treatment->product_detail,
            'responsible_person_id' => $treatment->responsible_person_id,
            'responsible_person' => $treatment->responsiblePerson ? [
                'id' => $treatment->responsiblePerson->user_id,
                'name' => $treatment->responsiblePerson->name,
            ] : null,
            'application_method' => $treatment->application_method,
            'withholding_period_days' => $treatment->withholding_period_days,
            'technician' => $treatment->technician,
            'institution_source' => $treatment->institution_source,
            'attachment' => $treatment->attachment,
            'description' => $treatment->description,
            'batch_number' => $treatment->batch_number,
            'amount_applied' => $treatment->amount_applied,
            'unit_measurement' => $treatment->unit_measurement,
            'treatment_location' => $treatment->treatment_location,
            'retreat_date' => $treatment->retreat_date ? $treatment->retreat_date->format('Y-m-d') : null,
            'total_cost' => $treatment->total_cost,
            'keywords' => $treatment->keywords,
            'planting_id' => $treatment->planting_id,
            'planting' => $treatment->planting ? [
                'id' => $treatment->planting->planting_id,
                'name' => $treatment->planting->plant->name ?? '-',
            ] : null,
            'edited_at' => $treatment->edited_at ? $treatment->edited_at->toISOString() : null,
            'edited_by' => $treatment->edited_by,
            'editor' => $treatment->editor ? [
                'id' => $treatment->editor->user_id,
                'name' => $treatment->editor->name,
            ] : null,
        ]);
    }

    public function updateTreatment(Request $request, PlantingLocation $plantingLocation, Treatment $treatment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Treatment must belong to this location via planting
        if (!$treatment->planting_id || !$treatment->planting || $treatment->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Data perawatan tidak ditemukan.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data perawatan.');
        }
        
        $data = $request->validate([
            'treatment_name' => 'required|string|max:255',
            'treatment_type' => 'required|string|max:255',
            'product_detail' => 'nullable|string|max:255',
            'responsible_person_id' => 'required|exists:users,user_id',
            'application_method' => 'required|string|max:255',
            'withholding_period_days' => 'nullable|integer|min:0',
            'technician' => 'nullable|string|max:255',
            'institution_source' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
            'description' => 'nullable|string',
            'treatment_date' => 'required|date',
            'batch_number' => 'nullable|string|max:255',
            'amount_applied' => 'nullable|numeric|min:0',
            'treatment_location' => 'nullable|string|max:255',
            'retreat_date' => 'nullable|date',
            'total_cost' => 'nullable|numeric|min:0',
            'keywords' => 'nullable|string|max:255',
            'planting_id' => 'nullable',
            'unit_measurement' => 'nullable|string|max:255',
        ]);

        // Handle "umum" option for planting_id
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $data['planting_id'] = $planting ? $planting->planting_id : null;
        } else {
            $data['planting_id'] = null; // "Umum" = applies to all plantings
        }

        $data['edited_at'] = now();
        $data['edited_by'] = $user->user_id;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($treatment->attachment && Storage::disk('public')->exists($treatment->attachment)) {
                Storage::disk('public')->delete($treatment->attachment);
            }
            
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['attachment'] = $file->storeAs('treatments/attachments', $filename, 'public');
        }

        $oldDate = $treatment->treatment_date?->format('Y-m-d');
        $oldCost = $treatment->total_cost;
        $treatment->update($data);
        
        // Update expense if total_cost changed (cari expense dengan nilai LAMA sebelum update)
        if ($treatment->total_cost && $treatment->total_cost > 0) {
            $expense = Expense::where('planting_id', $treatment->planting_id)
                ->where('expense_type', 'perawatan')
                ->when($oldDate, fn($q) => $q->where('expense_date', $oldDate))
                ->when($oldCost !== null, fn($q) => $q->where('amount', $oldCost))
                ->first();
            if ($expense) {
                $expense->update([
                    'expense_name' => $treatment->treatment_name,
                    'amount' => $treatment->total_cost,
                    'expense_date' => $treatment->treatment_date,
                    'responsible_person_id' => $treatment->responsible_person_id,
                ]);
            } else {
                Expense::create([
                    'expense_name' => $treatment->treatment_name,
                    'amount' => $treatment->total_cost,
                    'expense_type' => 'perawatan',
                    'expense_date' => $treatment->treatment_date,
                    'responsible_person_id' => $treatment->responsible_person_id,
                    'planting_id' => $treatment->planting_id,
                ]);
            }
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#perawatan-subtab')
                    ->with('success', 'Data perawatan berhasil diperbarui');
            }
        }
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=perawatan#pelaporan')
            ->with('success', 'Data perawatan berhasil diperbarui');
    }

    public function destroyTreatment(PlantingLocation $plantingLocation, Treatment $treatment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Treatment must belong to this location via planting
        if (!$treatment->planting_id || !$treatment->planting || $treatment->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Data perawatan tidak ditemukan.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data perawatan.');
        }
        
        // Hapus expense terkait (cari via planting_id + tipe perawatan + tanggal + amount)
        Expense::where('planting_id', $treatment->planting_id)
            ->where('expense_type', 'perawatan')
            ->where('expense_date', $treatment->treatment_date)
            ->where('amount', $treatment->total_cost)
            ->delete();
        
        // Delete attachment file if exists
        if ($treatment->attachment && Storage::disk('public')->exists($treatment->attachment)) {
            Storage::disk('public')->delete($treatment->attachment);
        }
        
        $treatment->delete();
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=perawatan#pelaporan')
            ->with('success', 'Data perawatan berhasil dihapus');
    }

    // Store nutrient for this location
    public function storeNutrient(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Both kepala_satuan_tugas and penangkar can add nutrients (in pelaporan)
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan data nutrisi.');
        }
        
        $data = $request->validate([
            'nutrient_name' => 'nullable|string|max:255',
            'product_applied' => 'required|string|max:255',
            'application_date' => 'required|date',
            'amount_applied' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'application_method' => 'required|string|max:255',
            'total_cost' => 'required|numeric|min:0',
            'technician' => 'nullable|string|max:255',
            'institution_source' => 'nullable|string|max:255',
            'responsible_person_id' => 'nullable|exists:users,user_id',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'planting_id' => 'nullable',
            'description' => 'nullable|string',
        ]);

        // Handle "umum" option for planting_id
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $data['planting_id'] = $planting ? $planting->planting_id : null;
        } else {
            $data['planting_id'] = null; // "Umum" = applies to all plantings
        }
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment'] = $file->store('nutrient-attachments', 'public');
        }
        
        $nutrient = Nutrient::create($data);
        
        // Save to expenses if total_cost is provided
        if ($nutrient->total_cost && $nutrient->total_cost > 0) {
            Expense::create([
                'planting_location_id' => $plantingLocation->planting_location_id,
                'expense_name' => $nutrient->product_applied,
                'amount' => $nutrient->total_cost,
                'expense_type' => 'nutrisi',
                'expense_date' => $nutrient->application_date,
                'planting_nutrient_id' => $nutrient->planting_nutrient_id,
                'planting_id' => $nutrient->planting_id,
            ]);
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#nutrisi-subtab')
                    ->with('success', 'Data nutrisi berhasil ditambahkan');
            }
        }
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=nutrisi#pelaporan')
            ->with('success', 'Data nutrisi berhasil ditambahkan');
    }

    public function showNutrient(PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Nutrient must belong to this location via planting
        if (!$nutrient->planting_id || !$nutrient->planting || $nutrient->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Catatan nutrisi tidak ditemukan.');
        }
        
        $nutrient->load(['planting.plant', 'editor', 'responsiblePerson']);
        
        return response()->json([
            'id' => $nutrient->planting_nutrient_id,
            'nutrient_name' => $nutrient->nutrient_name,
            'product_applied' => $nutrient->product_applied,
            'application_date' => $nutrient->application_date->format('Y-m-d'),
            'amount_applied' => $nutrient->amount_applied,
            'unit' => $nutrient->unit,
            'application_method' => $nutrient->application_method,
            'total_cost' => $nutrient->total_cost,
            'technician' => $nutrient->technician,
            'institution_source' => $nutrient->institution_source,
            'responsible_person_id' => $nutrient->responsible_person_id,
            'responsible_person' => $nutrient->responsiblePerson ? [
                'id' => $nutrient->responsiblePerson->user_id,
                'name' => $nutrient->responsiblePerson->name,
            ] : null,
            'attachment' => $nutrient->attachment,
            'planting_id' => $nutrient->planting_id,
            'planting' => $nutrient->planting ? [
                'id' => $nutrient->planting->planting_id,
                'name' => $nutrient->planting->plant->name ?? '-',
            ] : null,
            'description' => $nutrient->description,
            'edited_at' => $nutrient->edited_at ? $nutrient->edited_at->toISOString() : null,
            'edited_by' => $nutrient->edited_by,
            'editor' => $nutrient->editor ? [
                'id' => $nutrient->editor->user_id,
                'name' => $nutrient->editor->name,
            ] : null,
        ]);
    }

    public function updateNutrient(Request $request, PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Nutrient must belong to this location via planting
        if (!$nutrient->planting_id || !$nutrient->planting || $nutrient->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Catatan nutrisi tidak ditemukan.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data nutrisi.');
        }
        
        $data = $request->validate([
            'nutrient_name' => 'nullable|string|max:255',
            'product_applied' => 'required|string|max:255',
            'application_date' => 'required|date',
            'amount_applied' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'application_method' => 'required|string|max:255',
            'total_cost' => 'nullable|numeric|min:0',
            'technician' => 'nullable|string|max:255',
            'institution_source' => 'nullable|string|max:255',
            'responsible_person_id' => 'nullable|exists:users,user_id',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'planting_id' => 'nullable',
            'description' => 'nullable|string',
        ]);

        // Handle "umum" option for planting_id
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $data['planting_id'] = $planting ? $planting->planting_id : null;
        } else {
            $data['planting_id'] = null; // "Umum" = applies to all plantings
        }

        $data['edited_at'] = now();
        $data['edited_by'] = $user->user_id;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($nutrient->attachment && Storage::disk('public')->exists($nutrient->attachment)) {
                Storage::disk('public')->delete($nutrient->attachment);
            }
            
            $file = $request->file('attachment');
            $data['attachment'] = $file->store('nutrient-attachments', 'public');
        }

        $oldDate = $nutrient->application_date?->format('Y-m-d');
        $oldCost = $nutrient->total_cost;
        $nutrient->update($data);
        
        // Update expense if total_cost changed (cari expense dengan nilai LAMA sebelum update)
        if ($nutrient->total_cost && $nutrient->total_cost > 0) {
            $expense = Expense::where('planting_id', $nutrient->planting_id)
                ->where('expense_type', 'nutrisi')
                ->when($oldDate, fn($q) => $q->where('expense_date', $oldDate))
                ->when($oldCost !== null, fn($q) => $q->where('amount', $oldCost))
                ->first();
            if ($expense) {
                $expense->update([
                    'expense_name' => $nutrient->product_applied,
                    'amount' => $nutrient->total_cost,
                    'expense_date' => $nutrient->application_date,
                ]);
            } else {
                Expense::create([
                    'expense_name' => $nutrient->product_applied,
                    'amount' => $nutrient->total_cost,
                    'expense_type' => 'nutrisi',
                    'expense_date' => $nutrient->application_date,
                    'planting_id' => $nutrient->planting_id,
                ]);
            }
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingId = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingId) {
            $planting = \App\Models\Planting::find($plantingId);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#nutrisi-subtab')
                    ->with('success', 'Data nutrisi berhasil diperbarui');
            }
        }
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=nutrisi#pelaporan')
            ->with('success', 'Data nutrisi berhasil diperbarui');
    }

    public function destroyNutrient(PlantingLocation $plantingLocation, Nutrient $nutrient)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Nutrient must belong to this location via planting
        if (!$nutrient->planting_id || !$nutrient->planting || $nutrient->planting->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Catatan nutrisi tidak ditemukan.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data nutrisi.');
        }
        
        // Hapus expense terkait (cari via planting_id + tipe nutrisi + tanggal + amount)
        Expense::where('planting_id', $nutrient->planting_id)
            ->where('expense_type', 'nutrisi')
            ->where('expense_date', $nutrient->application_date)
            ->where('amount', $nutrient->total_cost)
            ->delete();
        
        $nutrient->delete();
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=nutrisi#pelaporan')
            ->with('success', 'Data nutrisi berhasil dihapus');
    }

    public function storeExpense(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan pengeluaran.');
        }
        
        $expenseType = $request->input('expense_type');
        
        if (empty($expenseType)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['expense_type' => 'Jenis pengeluaran harus dipilih.']);
        }
        
        try {
            if ($expenseType === 'perawatan') {
            // Handle treatment form
            $data = $request->validate([
                'treatment_name' => 'required|string|max:255',
                'treatment_type' => 'required|string|max:255',
                'product_detail' => 'nullable|string|max:255',
                'responsible_person_id' => 'required|exists:users,user_id',
                'application_method' => 'required|string|max:255',
                'withholding_period_days' => 'nullable|integer|min:0',
                'technician' => 'nullable|string|max:255',
                'institution_source' => 'nullable|string|max:255',
                'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
                'description' => 'nullable|string',
                'treatment_date' => 'required|date',
                'batch_number' => 'nullable|string|max:255',
                'amount_applied' => 'nullable|numeric|min:0',
                'treatment_location' => 'nullable|string|max:255',
                'retreat_date' => 'nullable|date',
                'total_cost' => 'required|numeric|min:0',
                'keywords' => 'nullable|string|max:255',
                'planting_id' => 'nullable|exists:planting_production,planting_production_id',
                'unit_measurement' => 'nullable|string|max:255',
            ]);
            
            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $data['attachment'] = $file->storeAs('treatments/attachments', $filename, 'public');
            }
            
            $treatment = Treatment::create($data);
            
            // Create expense (hanya planting_id, lokasi dari planting)
            Expense::create([
                'expense_name' => $treatment->treatment_name,
                'amount' => $treatment->total_cost,
                'expense_type' => 'perawatan',
                'expense_date' => $treatment->treatment_date,
                'responsible_person_id' => $treatment->responsible_person_id,
                'planting_id' => $treatment->planting_id,
            ]);
            
            return redirect()->to(route('planting-locations.show', $plantingLocation) . '?tab=pengeluaran')
                ->with('success', 'Data perawatan dan pengeluaran berhasil ditambahkan');
                
        } elseif ($expenseType === 'nutrisi') {
            // Handle nutrient form
            $data = $request->validate([
                'nutrient_name' => 'nullable|string|max:255',
                'product_applied' => 'required|string|max:255',
                'application_date' => 'required|date',
                'amount_applied' => 'required|numeric|min:0',
                'unit' => 'required|string|max:255',
                'application_method' => 'required|string|max:255',
                'total_cost' => 'required|numeric|min:0',
                'technician' => 'nullable|string|max:255',
                'institution_source' => 'nullable|string|max:255',
                'responsible_person_id' => 'nullable|exists:users,user_id',
                'attachment' => 'nullable|file|max:10240',
                'planting_id' => 'nullable|exists:planting_production,planting_production_id',
                'description' => 'nullable|string',
            ]);
            
            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $data['attachment'] = $file->store('nutrient-attachments', 'public');
            }
            
            $nutrient = Nutrient::create($data);
            
            // Create expense (hanya planting_id, lokasi dari planting)
            Expense::create([
                'expense_name' => $nutrient->product_applied,
                'amount' => $nutrient->total_cost,
                'expense_type' => 'nutrisi',
                'expense_date' => $nutrient->application_date,
                'planting_id' => $nutrient->planting_id,
            ]);
            
            return redirect()->to(route('planting-locations.show', $plantingLocation) . '?tab=pengeluaran')
                ->with('success', 'Data nutrisi dan pengeluaran berhasil ditambahkan');
                
        } elseif ($expenseType === 'upah_pekerja') {
            // Handle upah pekerja form
            $data = $request->validate([
                'work_name' => 'required|string|max:255',
                'work_date' => 'nullable|date',
                'work_description' => 'nullable|string',
                'worker_name' => 'nullable|string|max:255',
                'amount' => 'required|numeric|min:0',
                'planting_id' => 'nullable',
                'description' => 'nullable|string',
            ]);

            // Handle planting_id validation manually (gunakan planting_id sebagai kunci utama)
            $plantingId = null;
            if (isset($data['planting_id']) && $data['planting_id'] !== '' && $data['planting_id'] !== null && $data['planting_id'] !== '0') {
                $plantingId = $data['planting_id'];
                if (!Planting::whereKey($plantingId)->exists()) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['planting_id' => 'Asosiasi penanaman yang dipilih tidak valid.']);
                }
            }

            $expenseData = [
                'expense_name' => $data['work_name'],
                'work_name' => $data['work_name'],
                'work_date' => !empty($data['work_date']) ? $data['work_date'] : null,
                'work_description' => !empty($data['work_description']) ? $data['work_description'] : null,
                'worker_name' => !empty($data['worker_name']) ? $data['worker_name'] : null,
                'amount' => $data['amount'],
                'expense_type' => 'upah_pekerja',
                'expense_date' => !empty($data['work_date']) ? $data['work_date'] : now()->toDateString(),
                'planting_id' => $plantingId,
                'description' => !empty($data['description']) ? $data['description'] : null,
                'responsible_person_id' => $user->user_id,
            ];

            \Log::info('Creating expense', ['expense_data' => $expenseData]);
            
            $expense = Expense::create($expenseData);
            
            \Log::info('Expense created successfully', ['expense_id' => $expense->expense_id]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Data pengeluaran upah pekerja berhasil ditambahkan']);
            }
            
            return redirect()->to(route('planting-locations.show', $plantingLocation) . '?tab=pengeluaran')
                ->with('success', 'Data pengeluaran upah pekerja berhasil ditambahkan');
                
        } else {
            // Handle lainnya form
            $data = $request->validate([
                'expense_name' => 'required|string|max:255',
                'work_date' => 'nullable|date',
                'work_description' => 'nullable|string',
                'worker_name' => 'nullable|string|max:255',
                'amount' => 'required|numeric|min:0',
                'planting_id' => 'nullable',
                'description' => 'nullable|string',
            ]);

            // Handle planting_id validation manually (gunakan planting_id sebagai kunci utama)
            $plantingId = null;
            if (isset($data['planting_id']) && $data['planting_id'] !== '' && $data['planting_id'] !== null && $data['planting_id'] !== '0') {
                $plantingId = $data['planting_id'];
                if (!Planting::whereKey($plantingId)->exists()) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['planting_id' => 'Asosiasi penanaman yang dipilih tidak valid.']);
                }
            }

            $expenseData = [
                'expense_name' => $data['expense_name'],
                'work_name' => $data['expense_name'],
                'work_date' => !empty($data['work_date']) ? $data['work_date'] : null,
                'work_description' => !empty($data['work_description']) ? $data['work_description'] : null,
                'worker_name' => !empty($data['worker_name']) ? $data['worker_name'] : null,
                'amount' => $data['amount'],
                'expense_type' => 'lainnya',
                'expense_date' => !empty($data['work_date']) ? $data['work_date'] : now()->toDateString(),
                'planting_id' => $plantingId,
                'description' => !empty($data['description']) ? $data['description'] : null,
                'responsible_person_id' => $user->user_id,
            ];

            \Log::info('Creating expense (lainnya)', ['expense_data' => $expenseData]);
            
            $expense = Expense::create($expenseData);
            
            \Log::info('Expense created successfully', ['expense_id' => $expense->expense_id]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Data pengeluaran berhasil ditambahkan']);
            }
            
            return redirect()->to(route('planting-locations.show', $plantingLocation) . '?tab=pengeluaran')
                ->with('success', 'Data pengeluaran berhasil ditambahkan');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->errors()]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Error creating expense', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function showExpense(PlantingLocation $plantingLocation, Expense $expense)
    {
        try {
            $user = auth()->user();
            
            // Check if user has access
            if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Anda tidak memiliki akses ke lokasi penanaman ini.'], 403);
                }
                abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
            }
            
            // Verify expense belongs to this planting location (via planting)
            $expenseLocationId = $expense->planting && $expense->planting->planting_location_id
                ? $expense->planting->planting_location_id
                : null;
            if ($expenseLocationId !== $plantingLocation->planting_location_id) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Pengeluaran tidak ditemukan.'], 404);
                }
                abort(404, 'Pengeluaran tidak ditemukan.');
            }
            
            // Load relationships safely
            $expense->load(['planting.plant', 'responsiblePerson', 'editor']);
            
            // Load planting with plant relationship if exists - use try-catch to handle any errors
            $plantingName = null;
            if ($expense->planting_id) {
                try {
                    $expense->load('planting.plant');
                    if ($expense->planting && $expense->planting->plant) {
                        $plantingName = $expense->planting->plant->name;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error loading planting.plant for expense: ' . $e->getMessage());
                    // Try to load just planting and then plant separately
                    try {
                        $expense->load('planting');
                        if ($expense->planting && $expense->planting->plant_id) {
                            $plant = \App\Models\Plant::find($expense->planting->plant_id);
                            if ($plant) {
                                $plantingName = $plant->name;
                            }
                        }
                    } catch (\Exception $e2) {
                        \Log::warning('Error loading plant separately: ' . $e2->getMessage());
                    }
                }
            }
            
            $expenseDate = $expense->expense_date ? (is_string($expense->expense_date) ? $expense->expense_date : $expense->expense_date->format('Y-m-d')) : null;
            $expenseDateFormatted = $expense->expense_date ? (is_string($expense->expense_date) ? \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') : $expense->expense_date->format('d M Y')) : '-';
            
            return response()->json([
                'success' => true,
                'expense' => [
                'id' => $expense->expense_id,
                'expense_name' => $expense->expense_name ?? '',
                'work_name' => $expense->work_name ?? null,
                'amount' => $expense->amount ?? 0,
                    'amount_formatted' => number_format($expense->amount ?? 0, 0, ',', '.'),
                'expense_type' => $expense->expense_type ?? '',
                    'expense_type_label' => ucfirst(str_replace('_', ' ', $expense->expense_type ?? '')),
                    'expense_date' => $expenseDate,
                    'expense_date_formatted' => $expenseDateFormatted,
                'work_date' => $expense->work_date ? (is_string($expense->work_date) ? $expense->work_date : $expense->work_date->format('Y-m-d')) : null,
                'work_description' => $expense->work_description ?? null,
                'worker_name' => $expense->worker_name ?? null,
                'planting_id' => $expense->planting_id ?? null,
                    'plant' => $expense->planting && $expense->planting->plant ? [
                        'id' => $expense->planting->plant->plant_id,
                        'name' => $expense->planting->plant->name ?? '-',
                        'variety' => $expense->planting->plant->variety ?? null,
                ] : null,
                    'planting_location' => [
                        'id' => $plantingLocation->planting_location_id,
                        'name' => $plantingLocation->name ?? '-',
                    ],
                'description' => $expense->description ?? null,
                'responsible_person_id' => $expense->responsible_person_id ?? null,
                'responsible_person' => ($expense->responsiblePerson && $expense->responsiblePerson->user_id) ? [
                    'id' => $expense->responsiblePerson->user_id,
                    'name' => $expense->responsiblePerson->name ?? '-',
                ] : null,
                'edited_at' => $expense->edited_at ? (method_exists($expense->edited_at, 'toISOString') ? $expense->edited_at->toISOString() : (is_string($expense->edited_at) ? $expense->edited_at : $expense->edited_at->format('c'))) : null,
                'edited_by' => $expense->edited_by ?? null,
                'editor' => ($expense->editor && $expense->editor->user_id) ? [
                    'id' => $expense->editor->user_id,
                    'name' => $expense->editor->name ?? '-',
                ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in showExpense: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Gagal memuat data pengeluaran: ' . $e->getMessage()
                ], 500);
            }
            
            abort(500, 'Gagal memuat data pengeluaran.');
        }
    }

    public function updateExpense(Request $request, PlantingLocation $plantingLocation, Expense $expense)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit pengeluaran.');
        }
        
        // Only allow editing for upah_pekerja and lainnya types
        if (!in_array($expense->expense_type, ['upah_pekerja', 'lainnya'])) {
            abort(403, 'Pengeluaran ini tidak dapat diedit karena terkait dengan data perawatan atau nutrisi.');
        }
        
        $data = $request->validate([
            'expense_name' => 'required|string|max:255',
            'work_date' => 'nullable|date',
            'work_description' => 'nullable|string',
            'worker_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'planting_id' => 'nullable|exists:planting_production,planting_production_id',
            'description' => 'nullable|string',
        ]);

        $data['work_name'] = $data['expense_name'];
        $data['work_date'] = !empty($data['work_date']) ? $data['work_date'] : null;
        $data['expense_date'] = !empty($data['work_date']) ? $data['work_date'] : now()->toDateString();
        $data['edited_at'] = now();
        $data['edited_by'] = $user->user_id;

        $expense->update($data);
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?tab=pengeluaran')
            ->with('success', 'Data pengeluaran berhasil diperbarui');
    }

    public function destroyExpense(PlantingLocation $plantingLocation, Expense $expense)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus pengeluaran.');
        }
        
        // Only allow deleting for upah_pekerja and lainnya types
        if (!in_array($expense->expense_type, ['upah_pekerja', 'lainnya'])) {
            abort(403, 'Pengeluaran ini tidak dapat dihapus karena terkait dengan data perawatan atau nutrisi.');
        }
        
        $expense->delete();
        
        return redirect()->to(route('planting-locations.expenses.index', $plantingLocation))
            ->with('success', 'Data pengeluaran berhasil dihapus');
    }

    /**
     * Show current plantings page
     */
    public function currentPlantings(PlantingLocation $plantingLocation, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this planting location
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Load all plantings (no year/month filter)
        $allPlantings = $plantingLocation->plantings()
            ->with(['plant.satuanTanam', 'plant.satuanPanen', 'plant.type', 'seedSource.plant', 'field', 'postHarvests.stock', 'postHarvests.certificationReports'])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        // Ditanam saat ini (belum completed)
        // Active plantings: hanya yang is_completed = false
        // Note: 
        // - Mencatat kehilangan tidak mengubah status planting, penanaman tetap aktif
        // - Jika user memilih "Simpan dan Lanjutkan Penanaman", is_completed = false, jadi tetap aktif
        // - Jika user memilih "Simpan dan Selesaikan Panen", is_completed = true, jadi tidak aktif
        $activePlantings = $allPlantings->filter(fn ($planting) => ! $planting->is_completed);
        
        $plantingLocation->load('fields');
        $seedSources = \App\Models\SeedSource::with('variety.type')->orderBy('origin_lot_number')->get();
        $allPlants = \App\Models\Plant::with(['type', 'satuanTanam', 'satuanPanen', 'satuanStok'])->orderBy('name')->get();
        
        return view('planting.planting-locations.current-plantings', compact(
            'plantingLocation',
            'activePlantings',
            'allPlants',
            'seedSources'
        ));
    }

    /**
     * Show planting history page
     */
    public function plantingHistory(PlantingLocation $plantingLocation, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this planting location
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        $postHarvests = \App\Models\PlantingPostHarvest::whereHas('planting.field', function ($q) use ($plantingLocation) {
                $q->where('planting_location_id', $plantingLocation->planting_location_id);
            })
            ->with([
                'planting.plant',
                'planting.field',
                'planting.seedSource.variety',
                'planting.satuanPanen',
                'certificationReports.packagings',
                'stock.packagings',
                'stock.labels.packagings',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->filter(function ($item) {
                if (! $item->isLulus() || $item->isCertified()) {
                    return true;
                }

                return (bool) $item->planting?->is_completed;
            })
            ->values();

        $completedPlantings = $plantingLocation->plantings()
            ->with(['plant', 'plant.type', 'seedSource', 'field'])
            ->where('is_completed', true)
            ->orderByDesc('completed_at')
            ->orderByDesc('planted_at')
            ->get();

        return view('planting.planting-locations.planting-history', compact(
            'plantingLocation',
            'postHarvests',
            'completedPlantings'
        ));
    }

    /**
     * Riwayat sertifikat label benih yang sudah dilabel di lokasi ini,
     * termasuk aksi sertifikasi ulang.
     */
    public function labelCertificates(PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }

        $stocks = \App\Models\Stock::query()
            ->whereHas('postHarvest.planting.field', function ($q) use ($plantingLocation) {
                $q->where('planting_location_id', $plantingLocation->planting_location_id);
            })
            ->whereHas('labels')
            ->with([
                'plant.type',
                'plant.satuanStok',
                'labels',
                'packagings',
                'postHarvest.planting.field',
                'postHarvest.planting.seedSource.plant',
                'certificationReport',
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('planting.planting-locations.label-certificates', compact(
            'plantingLocation',
            'stocks'
        ));
    }

    /**
     * Show attachment page
     */
    public function attachments(PlantingLocation $plantingLocation)
    {
        $user = auth()->user();

        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }

        $attachments = $plantingLocation->locationAttachments()
            ->with(['creator'])
            ->orderBy('attachment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('planting.planting-locations.attachments', compact(
            'plantingLocation',
            'attachments'
        ));
    }

    /**
     * Show expenses page
     */
    public function expenses(PlantingLocation $plantingLocation, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this planting location
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Load expenses (melalui planting, lokasi dari planting)
        $expensesQuery = $plantingLocation->expenses()->with([
            'editor', 
            'responsiblePerson', 
            'planting.plant',
        ]);
        
        // Filter by year if provided
        if ($request->filled('year')) {
            $expensesQuery->whereYear('expense_date', $request->year);
        }
        
        // Filter by month if provided
        if ($request->filled('month')) {
            $expensesQuery->whereMonth('expense_date', $request->month);
        }
        
        // Filter by type if provided
        if ($request->filled('type') && $request->type !== 'all') {
            $expensesQuery->where('expense_type', $request->type);
        }
        
        // Filter by planting if provided
        if ($request->filled('planting_id')) {
            $expensesQuery->where('planting_id', $request->planting_id);
        }
        
        $expenses = $expensesQuery->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate totals
        $totalExpenses = $expenses->sum('amount');
        $totalByType = $expenses->groupBy('expense_type')->map(function($group) {
            return $group->sum('amount');
        });
        
        // Get available years for filter
        $existingYears = $plantingLocation->expenses()
            ->whereNotNull('expense_date')
            ->selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->pluck('year');
        
        $currentYear = (int)date('Y');
        $years = collect();
        
        foreach ($existingYears as $year) {
            $years->push((int)$year);
        }
        
        if (!$years->contains($currentYear)) {
            $years->push($currentYear);
        }
        
        $years = $years->unique()->sortDesc()->values();
        
        // Get all plantings for filter dropdown
        $allPlantings = $plantingLocation->plantings()
            ->with(['plant'])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        return view('planting.planting-locations.expenses', compact(
            'plantingLocation',
            'expenses',
            'totalExpenses',
            'totalByType',
            'years',
            'allPlantings'
        ));
    }

    /**
     * Show planting reports page for a specific planting
     */
    public function showPlantingReports(PlantingLocation $plantingLocation, Planting $planting, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this planting location
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Verify that this planting belongs to this planting location
        if ($planting->planting_location_id != $plantingLocation->planting_location_id) {
            abort(404, 'Penanaman tidak ditemukan di lokasi penanaman ini.');
        }
        
        $planting->load(['plant.type']);
        
        // Load tasks for this planting
        $statusFilter = $request->get('status', 'all');
        $assigneeFilter = $request->get('assignee', 'all');
        $taskYear = $request->get('task_year', '');
        $taskMonth = $request->get('task_month', '');
        
        // Load tasks for this planting OR general tasks (planting_id = null)
        $tasksQuery = $plantingLocation->tasks()
            ->where(function($query) use ($planting) {
                $query->where('planting_tasks.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_tasks.planting_id');
            })
            ->with(['assignedUser', 'planting.plant']);
        
        if ($statusFilter !== 'all') {
            $tasksQuery->where('new_status', $statusFilter);
        }
        
        if ($assigneeFilter !== 'all') {
            $tasksQuery->where('assigned_to', $assigneeFilter);
        }
        
        // Filter by year
        if ($taskYear) {
            $tasksQuery->whereYear('due_date', $taskYear);
        }
        
        // Filter by month
        if ($taskMonth) {
            $tasksQuery->whereMonth('due_date', $taskMonth);
        }
        
        $tasks = $tasksQuery->orderBy('due_date', 'asc')->get();
        
        // Get available years for task filter (including general tasks)
        $existingYears = $plantingLocation->tasks()
            ->where(function($query) use ($planting) {
                $query->where('planting_tasks.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_tasks.planting_id');
            })
            ->whereNotNull('due_date')
            ->selectRaw('YEAR(due_date) as year')
            ->distinct()
            ->pluck('year');
        
        $currentYear = (int)date('Y');
        $taskYears = collect();
        
        foreach ($existingYears as $year) {
            $taskYears->push((int)$year);
        }
        
        if (!$taskYears->contains($currentYear)) {
            $taskYears->push($currentYear);
        }
        
        for ($i = 1; $i <= 5; $i++) {
            $futureYear = $currentYear + $i;
            if (!$taskYears->contains($futureYear)) {
                $taskYears->push($futureYear);
            }
        }
        
        $taskYears = $taskYears->unique()->sortDesc()->values();
        
        // Get all months
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $taskMonths = $monthNames;
        
        // Load treatments for this planting OR general treatments (planting_id = null)
        $treatments = $plantingLocation->treatments()
            ->where(function($query) use ($planting) {
                $query->where('planting_treatments.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_treatments.planting_id');
            })
            ->with(['planting.plant', 'responsiblePerson', 'editor'])
            ->orderBy('treatment_date', 'desc')
            ->get();
        
        // Load nutrients for this planting OR general nutrients (planting_id = null)
        $nutrients = $plantingLocation->nutrients()
            ->where(function($query) use ($planting) {
                $query->where('planting_nutrients.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_nutrients.planting_id');
            })
            ->with(['planting.plant', 'editor'])
            ->orderBy('application_date', 'desc')
            ->get();
        
        // Load notes for this planting OR general notes (planting_id = null)
        $notes = $plantingLocation->notes()
            ->where(function($query) use ($planting) {
                $query->where('planting_notes.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_notes.planting_id');
            })
            ->with('user')
            ->orderBy('note_date', 'desc')
            ->get();
        
        // Photos feature removed (planting_location_photos table dropped)
        $photos = collect([]);
        
        // Lampiran kini unik per lokasi penanaman (tidak terhubung ke planting tertentu)
        $attachments = $plantingLocation->attachments()
            ->with(['creator', 'editor'])
            ->orderBy('attachment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Load "pengeluaran lainnya" (upah_pekerja & lainnya) yang terkait dengan penanaman ini
        $otherExpenses = $plantingLocation->expenses()
            ->where('expenses.planting_id', $planting->planting_id)
            ->whereIn('expense_type', ['upah_pekerja', 'lainnya'])
            ->with(['responsiblePerson'])
            ->orderBy('expense_date', 'desc')
            ->get();
        $totalOtherExpenses = $otherExpenses->sum('amount');
        
        // Get all users for task assignment
        $users = \App\Models\User::orderBy('name')->get();
        
        // Get land workers for this location (pekerja lahan dengan jabatan)
        $landWorkers = $plantingLocation->landWorkerUsers()->orderBy('name')->get();
        $locationUsers = $landWorkers->unique('user_id')->sortBy('name');
        
        $taskTemplates = \Illuminate\Support\Facades\Schema::hasTable('task_templates')
            ? \App\Models\TaskTemplate::where('association', 'penanaman')->where('is_active', true)->orderBy('name')->get()
            : collect();
        
        // Get inventory types for treatment dropdown
        $inventoryTypes = \App\Models\InventoryType::orderBy('name')->get();
        
        // Get all active plantings for dropdowns (including this planting)
        $allPlantingsForLocation = $plantingLocation->plantings()
            ->with(['plant'])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        // Get all tasks for "Semua Laporan" tab (without status filter)
        $allTasks = $plantingLocation->tasks()
            ->with(['planting.plant', 'assignedUser', 'createdByUser', 'lastEditedByUser'])
            ->where(function($query) use ($planting) {
                $query->where('planting_tasks.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_tasks.planting_id');
            })
            ->orderBy('due_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('planting.plants.planting-reports', compact(
            'plantingLocation',
            'planting',
            'tasks',
            'statusFilter',
            'assigneeFilter',
            'taskYear',
            'taskMonth',
            'taskYears',
            'taskMonths',
            'treatments',
            'nutrients',
            'notes',
            'photos',
            'attachments',
            'users',
            'locationUsers',
            'taskTemplates',
            'inventoryTypes',
            'allPlantingsForLocation',
            'allTasks',
            'otherExpenses',
            'totalOtherExpenses'
        ));
    }
}


