<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLoss;
use App\Models\Task;
use App\Models\PlantingLocationNote;
use App\Models\PlantingLocationPhoto;
use App\Jobs\SendTaskNotificationJob;
use App\Jobs\SendNoteNotificationJob;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\Expense;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Harvest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PlantingLocationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PlantingLocation::with(['landManagerUsers', 'landWorkerUsers']);
        
        // Filter: Admin melihat semua; non-admin hanya lokasi yang ditugaskan (manager atau worker)
        if (!$user->isAdmin()) {
            $query->where(function($q) use ($user) {
                $q->whereHas('landManagerUsers', function($q) use ($user) {
                    $q->where('users.user_id', $user->user_id);
                })->orWhereHas('landWorkerUsers', function($q) use ($user) {
                    $q->where('users.user_id', $user->user_id);
                });
            });
        }
        
        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        // Filter by assignment (user) - for admin filter dropdown
        if ($request->filled('assignment')) {
            $userId = $request->input('assignment');
            $query->where(function($q) use ($userId) {
                $q->whereHas('landManagerUsers', function($q) use ($userId) {
                    $q->where('users.user_id', $userId);
                })->orWhereHas('landWorkerUsers', function($q) use ($userId) {
                    $q->where('users.user_id', $userId);
                });
            });
        }
        
        $plantingLocations = $query->orderBy('name')->paginate(15)->withQueryString();
        
        // Get all users who are assigned to any planting location (as manager or worker)
        // Use a more efficient query with distinct user IDs from pivot tables
        $managerUserIds = DB::table('user_planting_location_land_manager')
            ->distinct()
            ->pluck('user_id');
        
        $workerUserIds = DB::table('user_planting_location_land_worker')
            ->distinct()
            ->pluck('user_id');
        
        $allAssignedUserIds = $managerUserIds->merge($workerUserIds)->unique();
        
        $assignedUsers = $allAssignedUserIds->isNotEmpty() 
            ? User::whereIn('user_id', $allAssignedUserIds)->orderBy('name')->get()
            : collect();
        
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
                'location_summary' => 'nullable|string|max:255',
                'administrative_address' => 'nullable|string',
                'google_maps_link' => 'nullable|url|max:255',
                'primary_photo' => 'nullable|image|max:5120',
                'location_type' => 'required|in:lapangan,sawah,greenhouse,grow_room,padang_rumput,petak_ternak,lainnya',
                'location_type_custom' => 'nullable|string|max:255',
                'planting_format' => 'required|in:ditanam_dalam_petak,cover_crop,row_crop,lainnya',
                'planting_format_custom' => 'nullable|string|max:255',
                'num_beds' => 'nullable|integer|min:0',
                'bed_length_m' => 'nullable|numeric|min:0',
                'bed_width_m' => 'nullable|numeric|min:0',
                'map_size' => 'nullable|string|max:255',
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
                'land_manager_user_ids' => 'nullable|array',
                'land_manager_user_ids.*' => 'nullable|exists:users,user_id',
                'land_worker_user_ids' => 'nullable|array',
                'land_worker_user_ids.*' => 'nullable|exists:users,user_id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            if ($data['planting_format'] === 'lainnya') {
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

            unset(
                $data['land_status_custom'],
                $data['ownership_status_custom'],
                $data['water_source_custom'],
                $data['soil_type_custom'],
                $data['light_condition_custom'],
                $data['primary_photo'],
                $data['land_manager_user_ids'],
                $data['land_worker_user_ids']
            );

            if ($request->hasFile('primary_photo')) {
                $data['primary_photo_path'] = $request->file('primary_photo')->store('planting-location', 'public');
            }

            $loc = PlantingLocation::create($data);

            // Sync land manager and worker users
            $landManagerUserIds = $request->input('land_manager_user_ids', []);
            $landWorkerUserIds = $request->input('land_worker_user_ids', []);
            
            // Ensure arrays are not null and filter out empty values (user_id is string/UUID)
            if (!is_array($landManagerUserIds)) {
                $landManagerUserIds = [];
            }
            if (!is_array($landWorkerUserIds)) {
                $landWorkerUserIds = [];
            }
            $landManagerUserIds = array_values(array_filter($landManagerUserIds, fn($id) => !empty(trim((string) $id))));
            $landWorkerUserIds = array_values(array_filter($landWorkerUserIds, fn($id) => !empty(trim((string) $id))));

            $loc->landManagerUsers()->sync($landManagerUserIds);
            $loc->landWorkerUsers()->sync($landWorkerUserIds);

            return redirect()->route('planting-locations.show', $loc)->with('success', 'Lokasi penanaman berhasil ditambahkan');
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
        $plantingLocation->load(['landManagerUsers', 'landWorkerUsers']);
        
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
        $plantingLocation->load(['landManagerUsers', 'landWorkerUsers']);

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
            'location_summary' => 'nullable|string|max:255',
            'administrative_address' => 'nullable|string',
            'google_maps_link' => 'nullable|url|max:255',
            'primary_photo' => 'nullable|image|max:5120',
            'location_type' => 'required|in:lapangan,sawah,greenhouse,grow_room,padang_rumput,petak_ternak,lainnya',
            'location_type_custom' => 'nullable|string|max:255',
            'planting_format' => 'required|in:ditanam_dalam_petak,cover_crop,row_crop,lainnya',
            'planting_format_custom' => 'nullable|string|max:255',
            'num_beds' => 'nullable|integer|min:0',
            'bed_length_m' => 'nullable|numeric|min:0',
            'bed_width_m' => 'nullable|numeric|min:0',
            'map_size' => 'nullable|string|max:255',
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
            'land_manager_user_ids' => 'nullable|array',
            'land_manager_user_ids.*' => 'exists:users,user_id',
            'land_worker_user_ids' => 'nullable|array',
            'land_worker_user_ids.*' => 'exists:users,user_id',
        ]);

        if ($data['planting_format'] === 'lainnya') {
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

        unset(
            $data['land_status_custom'],
            $data['ownership_status_custom'],
            $data['water_source_custom'],
            $data['soil_type_custom'],
            $data['light_condition_custom'],
            $data['primary_photo'],
            $data['land_manager_user_ids'],
            $data['land_worker_user_ids']
        );

        if ($request->hasFile('primary_photo')) {
            if ($plantingLocation->primary_photo_path) {
                Storage::disk('public')->delete($plantingLocation->primary_photo_path);
            }

            $data['primary_photo_path'] = $request->file('primary_photo')->store('planting-location', 'public');
        }

        $plantingLocation->update($data);

        // Sync land manager and worker users
        $landManagerUserIds = $request->input('land_manager_user_ids', []);
        $landWorkerUserIds = $request->input('land_worker_user_ids', []);
        
        // Ensure arrays are not null and filter out empty values (user_id is string/UUID)
        if (!is_array($landManagerUserIds)) {
            $landManagerUserIds = [];
        }
        if (!is_array($landWorkerUserIds)) {
            $landWorkerUserIds = [];
        }
        $landManagerUserIds = array_values(array_filter($landManagerUserIds, fn($id) => !empty(trim((string) $id))));
        $landWorkerUserIds = array_values(array_filter($landWorkerUserIds, fn($id) => !empty(trim((string) $id))));

        $plantingLocation->landManagerUsers()->sync($landManagerUserIds);
        $plantingLocation->landWorkerUsers()->sync($landWorkerUserIds);

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
        
        // Valid harvest unit values from ENUM
        $validHarvestUnits = ['ikat','barel','tandan','gantang','lusin','gram','batang','kilogram','kiloliter','liter','mililiter','satuan','ton'];
        
        try {
            // Auto-generate planting_batch_number if not provided or empty
            $plantingBatchNumber = trim($request->input('planting_batch_number', ''));
            if (empty($plantingBatchNumber)) {
                $year = date('Y');
                $plantingCount = Planting::whereYear('planted_at', $year)->count() + 1;
                $plantingBatchNumber = 'TANAM-' . $year . '-' . str_pad($plantingCount, 3, '0', STR_PAD_LEFT);
                
                // Ensure uniqueness by checking if batch number already exists
                while (Planting::where('planting_batch_number', $plantingBatchNumber)->exists()) {
                    $plantingCount++;
                    $plantingBatchNumber = 'TANAM-' . $year . '-' . str_pad($plantingCount, 3, '0', STR_PAD_LEFT);
                }
                
                // Merge the generated batch number back to request
                $request->merge(['planting_batch_number' => $plantingBatchNumber]);
            }
            
            $data = $request->validate([
                'plant_id' => 'required|exists:plants,plant_id',
                'planting_location_id' => 'nullable|exists:planting_locations,planting_location_id',
                'planting_batch_number' => 'required|string|max:255|unique:plantings,planting_batch_number',
            'planted_at' => 'required|date',
            'estimated_harvest_date' => 'nullable|date|after_or_equal:planted_at',
            'area_ha' => 'nullable|numeric|min:0',
            'planting_format' => 'nullable|string|in:rumpun,batang,lainnya',
            'planting_format_custom' => 'nullable|string|max:255',
            'quantity_planted' => 'required|numeric|min:0',
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
            'harvest_unit' => 'nullable|string|in:' . implode(',', $validHarvestUnits),
            'expected_yield_per_hectare' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $plant = Plant::findOrFail($data['plant_id']);
        
        // Auto-fill from plant type if available and field is not provided
        if ($plant->type) {
            $data['days_to_harvest'] = $data['days_to_harvest'] ?? $plant->type->days_to_harvest;
            $data['spacing_between_plants'] = $data['spacing_between_plants'] ?? $plant->type->spacing_between_plants;
            $data['spacing_between_rows'] = $data['spacing_between_rows'] ?? $plant->type->spacing_between_rows;
            $data['sowing_depth'] = $data['sowing_depth'] ?? $plant->type->sowing_depth;
            $data['days_to_emerge'] = $data['days_to_emerge'] ?? $plant->type->days_to_emerge;
            
            // Get harvest_unit from plant type if available, otherwise use default 'kilogram'
            $harvestUnitFromType = $plant->type->harvest_unit ?? null;
            
            // Validate harvest_unit from type is valid, otherwise use default
            if ($harvestUnitFromType && in_array($harvestUnitFromType, $validHarvestUnits)) {
                $data['harvest_unit'] = $data['harvest_unit'] ?? $harvestUnitFromType;
            } else {
                $data['harvest_unit'] = $data['harvest_unit'] ?? 'kilogram';
            }
        } else {
            // If no plant type, set default harvest_unit
            $data['harvest_unit'] = $data['harvest_unit'] ?? 'kilogram';
        }

        // Set planting_location_id from route parameter
        $data['planting_location_id'] = $plantingLocation->planting_location_id;
        
        // Handle planting_format_custom
        if (isset($data['planting_format']) && $data['planting_format'] === 'lainnya') {
            $customFormat = trim((string) $request->input('planting_format_custom'));
            if ($customFormat === '') {
                return back()
                    ->withErrors(['planting_format_custom' => 'Format tanam lainnya wajib diisi.'])
                    ->withInput();
            }
            $data['planting_format_custom'] = $customFormat;
        } else {
            $data['planting_format_custom'] = null;
        }
        
            // Handle perennial checkbox
            $data['perennial'] = $request->has('perennial') ? true : false;
            
            $planting = Planting::create($data);
            
            // Redirect to current plantings page
            return redirect()->route('planting-locations.plantings.index', $plantingLocation)
                ->with('success', 'Penanaman berhasil ditambahkan');
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
            'planting_id' => 'required|exists:plantings,planting_id',
            'loss_date' => 'required|date',
            'loss_amount' => 'required|numeric|min:0.01',
            'loss_reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $planting = Planting::findOrFail($data['planting_id']);
        
        // Validate that loss amount doesn't exceed available plants
        $totalLosses = $planting->losses()->sum('loss_amount');
        $availablePlants = $planting->quantity_planted - $totalLosses;
        
        if ($data['loss_amount'] > $availablePlants) {
            return back()
                ->withErrors(['loss_amount' => 'Jumlah kehilangan tidak boleh melebihi tanaman yang tersedia (' . number_format($availablePlants, 0) . ' tanaman).'])
                ->withInput();
        }

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

        // Handle assigned_to - check if "semua_user" is selected
        // Note: assigned_to is VARCHAR(36) for single user ID
        // For "semua_user", we store null and the collaborators field stores all user IDs
        if ($request->filled('assigned_to') && $request->assigned_to === 'semua_user') {
            // Get all users related to this planting location (land managers, land workers, and admins)
            $landManagers = $plantingLocation->landManagerUsers;
            $landWorkers = $plantingLocation->landWorkerUsers;
            $locationUsers = $landManagers->merge($landWorkers)->unique('user_id');
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            $allUsers = $locationUsers->merge($adminUsers)->unique('user_id');
            // Store null for assigned_to (indicates all users)
            // Store user IDs in collaborators field (which is cast to array)
            $data['assigned_to'] = null;
            $data['collaborators'] = $allUsers->pluck('user_id')->toArray();
        } elseif ($request->filled('assigned_to') && $request->assigned_to !== 'semua_user') {
            // Single user assignment - validate and keep as is (will be stored as single ID)
            $data['assigned_to'] = $request->assigned_to;
        } else {
            $data['assigned_to'] = null;
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
                'description' => $data['description'] ?? '',
                'association' => 'penanaman',
                'is_active' => true,
                'tasks_list' => [[
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'task_report' => $data['task_report'] ?? '',
                    'checklist' => $data['checklist'] ?? [],
                    'association' => $data['association'],
                    'new_status' => $data['new_status'],
                    'new_priority' => $data['new_priority'],
                    'repeats' => $data['repeats'] ?? '',
                    'hours_spent' => $data['hours_spent'] ?? null,
                    'task_color' => $data['task_color'] ?? '#28a745',
                ]],
            ];

            \App\Models\TaskTemplate::create($templateData);
            
            return redirect()->route('planting-locations.show', $plantingLocation)
                ->with('success', 'Template laporan berhasil disimpan');
        }

        $data['planting_location_id'] = $plantingLocation->planting_location_id;
        
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
                        ->with('fill_task_id', $task->task_id);
                }
            }
            
            return redirect()->route('planting-locations.show', $plantingLocation)
                ->with('success', 'Tugas berhasil ditambahkan')
                ->with('fill_task_id', $task->task_id);
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

        // Verify task belongs to this planting location
        if ($task->planting_location_id !== $plantingLocation->planting_location_id) {
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

            // Verify task belongs to this planting location
            if ($task->planting_location_id !== $plantingLocation->planting_location_id) {
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
                    \Log::warning('Failed to load assignedUser for task ' . $task->task_id . ': ' . $e->getMessage());
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
                    'id' => $task->task_id,
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

        // Verify task belongs to this planting location
        if ($task->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Tugas tidak ditemukan.');
        }

        try {
            $task->load(['assignedUser', 'createdByUser', 'lastEditedByUser', 'plantingLocation', 'planting.plant']);

            return response()->json([
                'task' => [
                    'id' => $task->task_id,
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

        // Verify task belongs to this planting location
        if ($task->planting_location_id !== $plantingLocation->planting_location_id) {
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

        // Verify task belongs to this planting location
        if ($task->planting_location_id !== $plantingLocation->planting_location_id) {
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

        // Both admin, kepala_satuan_tugas, and penangkar can fill report
        if (!$user->isAdmin() && !$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengisi laporan ini.');
        }

        // Verify task belongs to this planting location
        if ($task->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Tugas tidak ditemukan.');
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

        $data['planting_location_id'] = $plantingLocation->planting_location_id;
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
                // Get all users related to this planting location (land managers, land workers, and admins)
                $landManagers = $plantingLocation->landManagerUsers;
                $landWorkers = $plantingLocation->landWorkerUsers;
                $locationUsers = $landManagers->merge($landWorkers)->unique('user_id');
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
        
        // Verify note belongs to this planting location
        if ($note->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Catatan tidak ditemukan.');
        }
        
        // Load relationships
        $note->load(['user', 'plantingLocation']);
        
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
                    'id' => $note->planting_location_note_id,
                    'title' => $note->title,
                    'description' => $note->description,
                    'note_date' => $note->note_date->format('d M Y'),
                    'keywords' => $note->keywords,
                    'attachment_path' => $note->attachment_path,
                    'user' => $note->user ? ['id' => $note->user->user_id, 'name' => $note->user->name] : null,
                    'planting_location' => $note->plantingLocation ? ['id' => $note->plantingLocation->planting_location_id, 'name' => $note->plantingLocation->name] : null,
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
        
        // Verify note belongs to this planting location
        if ($note->planting_location_id !== $plantingLocation->planting_location_id) {
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

    // Store photo for this location
    public function storePhoto(Request $request, PlantingLocation $plantingLocation)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Both kepala_satuan_tugas and penangkar can add photos (in pelaporan)
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan foto.');
        }
        
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|max:5120', // 5MB per photo
            'description' => 'nullable|string|max:255',
            'taken_at' => 'nullable|date',
            'planting_id' => 'nullable',
        ]);
        
        // Handle planting_id - save to link photo to specific planting
        $plantingId = null;
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $plantingId = $planting ? $planting->planting_id : null;
        }

        foreach ($request->file('photos') as $photo) {
            $filePath = $photo->store('planting-location-photos', 'public');
            
            PlantingLocationPhoto::create([
                'planting_location_id' => $plantingLocation->planting_location_id,
                'planting_id' => $plantingId,
                'file_path' => $filePath,
                'file_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'description' => $request->description,
                'taken_at' => $request->taken_at,
            ]);
        }
        
        // Check if request comes from planting reports page
        $fromPlantingReports = $request->input('from_planting_reports', false);
        $plantingIdForRedirect = $request->input('planting_id_for_redirect');
        
        if ($fromPlantingReports && $plantingIdForRedirect) {
            $planting = \App\Models\Planting::find($plantingIdForRedirect);
            if ($planting) {
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#foto-subtab')
                    ->with('success', 'Foto berhasil diunggah');
            }
        }
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Foto berhasil diunggah');
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
            'description' => 'nullable|string',
            'attachment_date' => 'required|date',
            'file' => 'required|file|max:10240', // 10MB max
            'planting_id' => 'nullable',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('planting-location-attachments', 'public');
        
        // Handle planting_id - save to link attachment to specific planting
        $plantingId = null;
        if ($request->filled('planting_id') && $request->planting_id !== 'umum' && $request->planting_id !== '') {
            $planting = $plantingLocation->plantings()->find($request->planting_id);
            $plantingId = $planting ? $planting->planting_id : null;
        }
        
        Attachment::create([
            'planting_location_id' => $plantingLocation->planting_location_id,
            'planting_id' => $plantingId,
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
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#lampiran-subtab')
                    ->with('success', 'Lampiran berhasil ditambahkan');
            }
        }
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=lampiran#pelaporan')
            ->with('success', 'Lampiran berhasil ditambahkan');
    }

    public function updateAttachment(Request $request, PlantingLocation $plantingLocation, Attachment $attachment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit lampiran.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachment_date' => 'required|date',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'attachment_date' => $request->attachment_date,
            'edited_at' => now(),
            'edited_by' => $user->user_id,
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
                return redirect(route('planting-locations.plantings.reports', [$plantingLocation, $planting]) . '#lampiran-subtab')
                    ->with('success', 'Lampiran berhasil diperbarui');
            }
        }
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=lampiran#pelaporan')
            ->with('success', 'Lampiran berhasil diperbarui');
    }

    public function destroyAttachment(PlantingLocation $plantingLocation, Attachment $attachment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus lampiran.');
        }
        
        // Delete file
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $attachment->delete();
        
        return redirect()->to(route('planting-locations.show', $plantingLocation) . '?subtab=lampiran#pelaporan')
            ->with('success', 'Lampiran berhasil dihapus');
    }

    public function showAttachment(PlantingLocation $plantingLocation, Attachment $attachment)
    {
        $user = auth()->user();
        
        // Check if user has access
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        $attachment->load(['creator', 'editor']);
        
        return response()->json([
            'id' => $attachment->attachment_id,
            'title' => $attachment->title,
            'description' => $attachment->description,
            'attachment_date' => $attachment->attachment_date->format('Y-m-d'),
            'file_path' => $attachment->file_path,
            'file_name' => $attachment->file_name,
            'created_by' => $attachment->created_by,
            'edited_at' => $attachment->edited_at ? $attachment->edited_at->toISOString() : null,
            'edited_by' => $attachment->edited_by,
            'creator' => $attachment->creator ? [
                'id' => $attachment->creator->user_id,
                'name' => $attachment->creator->name,
            ] : null,
            'editor' => $attachment->editor ? [
                'id' => $attachment->editor->user_id,
                'name' => $attachment->editor->name,
            ] : null,
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
        
        // Create a harvest record with zero quantity to mark as failed
        Harvest::create([
            'plant_id' => $planting->plant_id,
            'planting_id' => $planting->planting_id,
            'planting_location_id' => $plantingLocation->planting_location_id,
            'harvested_at' => now(),
            'batch_no' => 'FAILED-' . date('Y') . '-' . str_pad($planting->planting_id, 3, '0', STR_PAD_LEFT),
            'quantity' => 0,
            'unit' => 'kg',
            'quality' => 'Gagal Panen',
            'note' => 'Tanaman ditandai sebagai gagal panen',
        ]);
        
        // Mark planting as completed so it no longer appears in the active "Penanaman" list
        // Record will still appear in "Riwayat Penanaman" > "Gagal panen"
        $planting->update(['is_completed' => true]);
        
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

        $data['planting_location_id'] = $plantingLocation->planting_location_id;
        
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
                'treatment_id' => $treatment->treatment_id,
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
        
        $treatment->load(['planting.plant', 'responsiblePerson', 'editor']);
        
        return response()->json([
            'id' => $treatment->treatment_id,
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

        $treatment->update($data);
        
        // Update expense if total_cost changed
        if ($treatment->total_cost && $treatment->total_cost > 0) {
            $expense = Expense::where('treatment_id', $treatment->treatment_id)->first();
            if ($expense) {
                $expense->update([
                    'expense_name' => $treatment->treatment_name,
                    'amount' => $treatment->total_cost,
                    'expense_date' => $treatment->treatment_date,
                    'responsible_person_id' => $treatment->responsible_person_id,
                ]);
            } else {
                Expense::create([
                    'planting_location_id' => $plantingLocation->planting_location_id,
                    'expense_name' => $treatment->treatment_name,
                    'amount' => $treatment->total_cost,
                    'expense_type' => 'perawatan',
                    'expense_date' => $treatment->treatment_date,
                    'responsible_person_id' => $treatment->responsible_person_id,
                    'treatment_id' => $treatment->treatment_id,
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
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data perawatan.');
        }
        
        // Delete associated expense
        Expense::where('treatment_id', $treatment->treatment_id)->delete();
        
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

        $data['planting_location_id'] = $plantingLocation->planting_location_id;
        
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
                'nutrient_id' => $nutrient->nutrient_id,
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
        
        $nutrient->load(['planting.plant', 'editor', 'responsiblePerson']);
        
        return response()->json([
            'id' => $nutrient->nutrient_id,
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

        $nutrient->update($data);
        
        // Update expense if total_cost changed
        if ($nutrient->total_cost && $nutrient->total_cost > 0) {
            $expense = Expense::where('nutrient_id', $nutrient->nutrient_id)->first();
            if ($expense) {
                $expense->update([
                    'expense_name' => $nutrient->product_applied,
                    'amount' => $nutrient->total_cost,
                    'expense_date' => $nutrient->application_date,
                ]);
            } else {
                Expense::create([
                    'planting_location_id' => $plantingLocation->planting_location_id,
                    'expense_name' => $nutrient->product_applied,
                    'amount' => $nutrient->total_cost,
                    'expense_type' => 'nutrisi',
                    'expense_date' => $nutrient->application_date,
                    'nutrient_id' => $nutrient->nutrient_id,
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
        
        if (!$user->canAddDataInPelaporan($plantingLocation)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data nutrisi.');
        }
        
        // Delete associated expense
        Expense::where('nutrient_id', $nutrient->nutrient_id)->delete();
        
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
                'planting_id' => 'nullable|exists:plantings,planting_id',
                'unit_measurement' => 'nullable|string|max:255',
            ]);

            $data['planting_location_id'] = $plantingLocation->planting_location_id;
            
            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $data['attachment'] = $file->storeAs('treatments/attachments', $filename, 'public');
            }
            
            $treatment = Treatment::create($data);
            
            // Create expense
            Expense::create([
                'planting_location_id' => $plantingLocation->planting_location_id,
                'expense_name' => $treatment->treatment_name,
                'amount' => $treatment->total_cost,
                'expense_type' => 'perawatan',
                'expense_date' => $treatment->treatment_date,
                'responsible_person_id' => $treatment->responsible_person_id,
                'treatment_id' => $treatment->treatment_id,
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
                'planting_id' => 'nullable|exists:plantings,planting_id',
                'description' => 'nullable|string',
            ]);

            $data['planting_location_id'] = $plantingLocation->planting_location_id;
            
            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $data['attachment'] = $file->store('nutrient-attachments', 'public');
            }
            
            $nutrient = Nutrient::create($data);
            
            // Create expense
            Expense::create([
                'planting_location_id' => $plantingLocation->planting_location_id,
                'expense_name' => $nutrient->product_applied,
                'amount' => $nutrient->total_cost,
                'expense_type' => 'nutrisi',
                'expense_date' => $nutrient->application_date,
                'nutrient_id' => $nutrient->nutrient_id,
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

            // Handle planting_id validation manually
            $plantingId = null;
            if (isset($data['planting_id']) && $data['planting_id'] !== '' && $data['planting_id'] !== null && $data['planting_id'] !== '0') {
                $plantingId = (int)$data['planting_id'];
                if (!Planting::where('id', $plantingId)->exists()) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['planting_id' => 'Asosiasi penanaman yang dipilih tidak valid.']);
                }
            }

            $expenseData = [
                'planting_location_id' => $plantingLocation->planting_location_id,
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

            // Handle planting_id validation manually
            $plantingId = null;
            if (isset($data['planting_id']) && $data['planting_id'] !== '' && $data['planting_id'] !== null && $data['planting_id'] !== '0') {
                $plantingId = (int)$data['planting_id'];
                if (!Planting::where('id', $plantingId)->exists()) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['planting_id' => 'Asosiasi penanaman yang dipilih tidak valid.']);
                }
            }

            $expenseData = [
                'planting_location_id' => $plantingLocation->planting_location_id,
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
            
            // Verify expense belongs to this planting location
            if ($expense->planting_location_id !== $plantingLocation->planting_location_id) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Pengeluaran tidak ditemukan.'], 404);
                }
                abort(404, 'Pengeluaran tidak ditemukan.');
            }
            
            // Load relationships safely
            $expense->load(['treatment', 'nutrient', 'responsiblePerson', 'editor']);
            
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
                'treatment_id' => $expense->treatment_id ?? null,
                'nutrient_id' => $expense->nutrient_id ?? null,
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
            'planting_id' => 'nullable|exists:plantings,planting_id',
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
            ->with(['plant', 'plant.type', 'harvest.certification', 'harvests' => function($query) {
                $query->orderBy('harvested_at', 'desc');
            }, 'losses' => function($query) {
                $query->orderBy('loss_date', 'desc');
            }])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        // Ditanam saat ini (belum completed)
        // Active plantings: hanya yang is_completed = false
        // Note: 
        // - Mencatat kehilangan tidak mengubah status planting, penanaman tetap aktif
        // - Jika user memilih "Simpan dan Lanjutkan Penanaman", is_completed = false, jadi tetap aktif
        // - Jika user memilih "Simpan dan Selesaikan Panen", is_completed = true, jadi tidak aktif
        $activePlantings = $allPlantings->filter(function($planting) {
            // Only check is_completed status
            // If is_completed = false, planting is still active regardless of harvests or losses
            return !$planting->is_completed;
        });
        
        // Get all plants for dropdown
        $allPlants = \App\Models\Plant::with('type')->orderBy('name')->get();
        
        return view('planting.planting-locations.current-plantings', compact(
            'plantingLocation',
            'activePlantings',
            'allPlants'
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
        
        // Load all plantings (no year/month filter)
        // Load all harvests first, then filter in collection
        $allPlantings = $plantingLocation->plantings()
            ->with([
                'plant', 
                'plant.type', 
                'harvests' => function($query) {
                    $query->orderBy('harvested_at', 'desc');
                },
                'losses' => function($query) {
                    $query->orderBy('loss_date', 'desc');
                }
            ])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        // Telah dipanen (sudah ada harvest dengan quantity > 0)
        // Filter plantings that have at least one harvest with quantity > 0
        $harvestedPlantings = $allPlantings->filter(function($planting) {
            if (!$planting->harvests || $planting->harvests->isEmpty()) {
                return false;
            }
            // Check if there's at least one harvest with quantity > 0
            return $planting->harvests->where('quantity', '>', 0)->count() > 0;
        });
        
        // Kehilangan (ada losses)
        $lossPlantings = $allPlantings->filter(function($planting) {
            return $planting->losses && $planting->losses->count() > 0;
        });
        
        // Gagal panen (harvest dengan quantity = 0 atau null)
        $failedPlantings = $allPlantings->filter(function($planting) {
            // Check hasOne relationship first
            if ($planting->harvest) {
                return $planting->harvest->quantity == 0 || $planting->harvest->quantity === null;
            }
            // Check hasMany relationship
            if ($planting->harvests && $planting->harvests->count() > 0) {
                $latestHarvest = $planting->harvests->first();
                return $latestHarvest && ($latestHarvest->quantity == 0 || $latestHarvest->quantity === null);
            }
            return false;
        });
        
        return view('planting.planting-locations.planting-history', compact(
            'plantingLocation',
            'harvestedPlantings',
            'lossPlantings',
            'failedPlantings'
        ));
    }

    /**
     * Show harvest detail page
     */
    public function harvestDetail(PlantingLocation $plantingLocation, Planting $planting, Harvest $harvest)
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
        
        // Verify that this harvest belongs to this planting
        if ($harvest->planting_id != $planting->planting_id) {
            abort(404, 'Data panen tidak ditemukan untuk penanaman ini.');
        }
        
        // Load planting with relationships
        $planting->load(['plant', 'plant.type']);
        
        // Load tasks for this planting OR general tasks (planting_id = null)
        $tasks = $plantingLocation->tasks()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['assignedUser', 'planting.plant'])
            ->orderBy('due_date', 'desc')
            ->get();
        
        // Load treatments for this planting OR general treatments (planting_id = null)
        $treatments = $plantingLocation->treatments()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['plantingLocation', 'planting.plant', 'responsiblePerson'])
            ->orderBy('treatment_date', 'desc')
            ->get();
        
        // Load nutrients for this planting OR general nutrients (planting_id = null)
        $nutrients = $plantingLocation->nutrients()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['plantingLocation', 'planting.plant'])
            ->orderBy('application_date', 'desc')
            ->get();
        
        // Load notes for this planting location
        $notes = $plantingLocation->notes()
            ->with('user')
            ->orderBy('note_date', 'desc')
            ->get();
        
        // Load expenses for this planting OR general expenses (planting_id = null)
        $expenses = $plantingLocation->expenses()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['treatment', 'nutrient'])
            ->orderBy('expense_date', 'desc')
            ->get();
        
        // Calculate total expenses by type
        $totalTreatmentCost = $expenses->where('expense_type', 'perawatan')->sum('amount');
        $totalNutrientCost = $expenses->where('expense_type', 'nutrisi')->sum('amount');
        $totalOtherExpenses = $expenses->whereIn('expense_type', ['upah_pekerja', 'lainnya'])->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        
        return view('planting.planting-locations.harvest-detail', compact(
            'plantingLocation',
            'planting',
            'harvest',
            'tasks',
            'treatments',
            'nutrients',
            'notes',
            'expenses',
            'totalTreatmentCost',
            'totalNutrientCost',
            'totalOtherExpenses',
            'totalExpenses'
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
        
        // Load expenses
        $expensesQuery = $plantingLocation->expenses()->with([
            'editor', 
            'responsiblePerson', 
            'planting.plant',
            'treatment',
            'nutrient'
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
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
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
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
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
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['plantingLocation', 'planting.plant', 'responsiblePerson', 'editor'])
            ->orderBy('treatment_date', 'desc')
            ->get();
        
        // Load nutrients for this planting OR general nutrients (planting_id = null)
        $nutrients = $plantingLocation->nutrients()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['plantingLocation', 'planting.plant', 'editor'])
            ->orderBy('application_date', 'desc')
            ->get();
        
        // Load notes for this planting OR general notes (planting_id = null)
        $notes = $plantingLocation->notes()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with('user')
            ->orderBy('note_date', 'desc')
            ->get();
        
        // Load photos for this planting OR general photos (planting_id = null)
        $photos = $plantingLocation->photos()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->orderBy('taken_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Load attachments for this planting OR general attachments (planting_id = null)
        $attachments = $plantingLocation->attachments()
            ->where(function($query) use ($planting) {
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
            })
            ->with(['creator', 'editor'])
            ->orderBy('attachment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all users for task assignment
        $users = \App\Models\User::orderBy('name')->get();
        
        // Get land managers and workers for this location
        $landManagers = $plantingLocation->landManagerUsers()->orderBy('name')->get();
        $landWorkers = $plantingLocation->landWorkerUsers()->orderBy('name')->get();
        $locationUsers = $landManagers->merge($landWorkers)->unique('id')->sortBy('name');
        
        // Get task templates
        $taskTemplates = \App\Models\TaskTemplate::where('association', 'penanaman')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
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
                $query->where('planting_id', $planting->planting_id)
                      ->orWhereNull('planting_id');
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
            'allTasks'
        ));
    }
}


