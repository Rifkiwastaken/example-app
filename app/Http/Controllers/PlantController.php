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
    /**
     * Map form start_method values to database ENUM values
     */
    private function mapStartMethod($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'Tanam Langsung' => 'tanam_langsung',
            'Mulai di baki semai' => 'baki_semai',
            'Pindahkan ke tanah' => 'pindahkan_ke_tanah',
            'Pindah tanaman (transplant)' => 'transplant',
            'Dalam pot (container)' => 'container',
            'Ditanam di baki semai' => 'ditanam_di_baki_semai',
            'Batang bawah/ tanaman induk' => 'batang_bawah',
            'Umbi' => 'umbi',
            'Sambung/okulasi' => 'sambung_okulasi',
            'Lainnya' => 'lainnya',
        ];
        
        // If value is already in ENUM format, return as is
        $validEnumValues = ['tanam_langsung', 'baki_semai', 'pindahkan_ke_tanah', 'transplant', 'container', 'ditanam_di_baki_semai', 'batang_bawah', 'umbi', 'sambung_okulasi', 'lainnya'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form germination_stage values to database ENUM values
     */
    private function mapGerminationStage($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'benih ditanam' => 'benih_ditanam',
            'perkecambahan' => 'perkecambahan',
            'bibit/ tunas muda' => 'bibit',
            'sudah ditanam' => 'sudah_ditanam',
            'fase vegetatif' => 'vegetatif',
            'berbunga' => 'berbunga',
            'pematangan buah' => 'pematangan_buah',
            'selesai' => 'selesai',
        ];
        
        $validEnumValues = ['benih_ditanam', 'perkecambahan', 'bibit', 'sudah_ditanam', 'vegetatif', 'berbunga', 'pematangan_buah', 'selesai'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form light_profile values to database ENUM values
     */
    private function mapLightProfile($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'sinar matahari penuh' => 'matahari_penuh',
            'sinar matahari penuh sebagian' => 'matahari_penuh_sebagian',
            'sinar matahari sebagian' => 'matahari_sebagian',
            'matahari hingga setengah teduh' => 'matahari_setengah_teduh',
            'setengah teduh' => 'setengah_teduh',
            'teduh sepenuhnya' => 'teduh_sepenuhnya',
        ];
        
        $validEnumValues = ['matahari_penuh', 'matahari_penuh_sebagian', 'matahari_sebagian', 'matahari_setengah_teduh', 'setengah_teduh', 'teduh_sepenuhnya'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form soil_condition values to database ENUM values
     */
    private function mapSoilCondition($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'tanah berkapur' => 'berkapur',
            'tanah liat' => 'liat',
            'tanah lempung' => 'lempung',
            'tanah gambut' => 'gambut',
            'tanah berpasir' => 'berpasir',
            'tanah lanau' => 'lanau',
        ];
        
        $validEnumValues = ['berkapur', 'liat', 'lempung', 'gambut', 'berpasir', 'lanau'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form harvest_unit values to database ENUM values
     */
    private function mapHarvestUnit($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'jumlah' => 'satuan',
        ];
        
        $validEnumValues = ['ikat', 'barel', 'tandan', 'gantang', 'lusin', 'gram', 'batang', 'kilogram', 'kiloliter', 'liter', 'mililiter', 'satuan', 'ton'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? $value;
    }

    public function index(Request $request)
    {
        $query = Plant::with(['type', 'plantingLocation']);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }
        if ($request->filled('plant_type_id')) {
            $query->where('plant_type_id', $request->plant_type_id);
        }

        $plants = $query->orderBy('name')->paginate(15)->withQueryString();
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
        try {
            $data = $request->validate([
                'plant_type_id' => 'required|exists:plant_types,plant_type_id',
                'variety' => 'nullable|string|max:255',
                'planting_location_ids' => 'nullable|array',
                'planting_location_ids.*' => 'nullable|exists:planting_locations,planting_location_id',
                // Planting details
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
                // Harvest details
                'days_to_flower' => 'nullable|integer|min:0',
                'days_to_harvest' => 'nullable|integer|min:0',
                'harvest_window_days' => 'nullable|integer|min:0',
                'expected_loss_rate' => 'nullable|numeric|min:0|max:100',
                'harvest_unit' => 'nullable|string|max:255',
                'expected_yield_per_hectare' => 'nullable|numeric|min:0',
                'quantity_planted' => 'nullable|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
        
        try {
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
            
            $plantData = [
                'name' => trim($name),
                'plant_type_id' => $data['plant_type_id'] ?? null,
                'variety' => $data['variety'] ?? null,
                'planting_location_id' => !empty($data['planting_location_ids']) ? $data['planting_location_ids'][0] : null,
                'status' => 'perencanaan',
                'progress' => 0,
            ];
            
            $plant = Plant::create($plantData);
            
            // Data katalog (detail tanaman) disimpan di record Planting agar muncul di halaman detail
            $catalogData = [
                'plant_id' => $plant->plant_id,
                'days_to_emerge' => $data['days_to_emerge'] ?? null,
                'spacing_between_plants' => $data['spacing_between_plants'] ?? null,
                'spacing_between_rows' => $data['spacing_between_rows'] ?? null,
                'sowing_depth' => $data['sowing_depth'] ?? null,
                'avg_height' => $data['avg_height'] ?? null,
                'start_method' => $this->mapStartMethod($data['start_method'] ?? null),
                'germination_stage' => $this->mapGerminationStage($data['germination_stage'] ?? null),
                'seeds_per_hole' => $data['seeds_per_hole'] ?? null,
                'light_profile' => $this->mapLightProfile($data['light_profile'] ?? null),
                'soil_condition' => $this->mapSoilCondition($data['soil_condition'] ?? null),
                'planting_detail' => $data['planting_detail'] ?? null,
                'pruning_detail' => $data['pruning_detail'] ?? null,
                'days_to_flower' => $data['days_to_flower'] ?? null,
                'days_to_harvest' => $data['days_to_harvest'] ?? null,
                'harvest_window_days' => $data['harvest_window_days'] ?? null,
                'expected_loss_rate' => $data['expected_loss_rate'] ?? null,
                'harvest_unit' => $this->mapHarvestUnit($data['harvest_unit'] ?? null),
                'expected_yield_per_hectare' => $data['expected_yield_per_hectare'] ?? null,
                'quantity_planted' => $data['quantity_planted'] ?? null,
            ];
            
            $plantingLocationIds = array_filter((array) ($data['planting_location_ids'] ?? []));
            if (!empty($plantingLocationIds)) {
                foreach ($plantingLocationIds as $locationId) {
                    if (!empty($locationId)) {
                        $catalogData['planting_location_id'] = $locationId;
                        Planting::create($catalogData);
                    }
                }
            } else {
                // Tanpa lokasi pun tetap buat satu record Planting agar detail tanaman tampil di halaman detail
                $catalogData['planting_location_id'] = null;
                Planting::create($catalogData);
            }
            
            return redirect()->route('plants.show', $plant)->with('success', 'Tanaman berhasil ditambahkan');
        } catch (\Exception $e) {
            \Log::error('Error creating plant: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data tanaman: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Plant $plant)
    {
        $plant->load(['type', 'plantingLocation', 'plantings', 'harvests']);
        return view('planting/plants/show', compact('plant'));
    }

    public function edit(Plant $plant)
    {
        // Prevent penangkar from editing plants
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data tanaman.');
        }
        
        $plant->load('plantings');
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        return view('planting/plants/edit', compact('plant', 'types', 'locations'));
    }

    public function update(Request $request, Plant $plant)
    {
        // Prevent penangkar from updating plants
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data tanaman.');
        }
        
        $data = $request->validate([
            'plant_type_id' => 'nullable|exists:plant_types,plant_type_id',
            'variety' => 'nullable|string|max:255',
            'planting_location_ids' => 'nullable|array',
            'planting_location_ids.*' => 'exists:planting_locations,planting_location_id',
            // Planting details
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
            // Harvest details
            'days_to_flower' => 'nullable|integer|min:0',
            'days_to_harvest' => 'nullable|integer|min:0',
            'harvest_window_days' => 'nullable|integer|min:0',
            'expected_loss_rate' => 'nullable|numeric|min:0|max:100',
            'harvest_unit' => 'nullable|string|max:255',
            'expected_yield_per_hectare' => 'nullable|numeric|min:0',
            'quantity_planted' => 'nullable|numeric|min:0',
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
            $name = $request->variety ?? $plant->name;
        }
        
        // Ensure name is not empty
        if (empty(trim($name))) {
            $name = $plant->name;
        }
        
        $plantData = [
            'name' => trim($name),
            'plant_type_id' => $data['plant_type_id'] ?? null,
            'variety' => $data['variety'] ?? null,
            'planting_location_id' => !empty($data['planting_location_ids']) ? $data['planting_location_ids'][0] : null,
        ];
        
        $plant->update($plantData);
        
        // Data katalog (detail tanaman) untuk disimpan ke record Planting
        $plantingData = [
            'days_to_emerge' => $data['days_to_emerge'] ?? null,
            'spacing_between_plants' => $data['spacing_between_plants'] ?? null,
            'spacing_between_rows' => $data['spacing_between_rows'] ?? null,
            'sowing_depth' => $data['sowing_depth'] ?? null,
            'avg_height' => $data['avg_height'] ?? null,
            'start_method' => $this->mapStartMethod($data['start_method'] ?? null),
            'germination_stage' => $this->mapGerminationStage($data['germination_stage'] ?? null),
            'seeds_per_hole' => $data['seeds_per_hole'] ?? null,
            'light_profile' => $this->mapLightProfile($data['light_profile'] ?? null),
            'soil_condition' => $this->mapSoilCondition($data['soil_condition'] ?? null),
            'planting_detail' => $data['planting_detail'] ?? null,
            'pruning_detail' => $data['pruning_detail'] ?? null,
            'days_to_flower' => $data['days_to_flower'] ?? null,
            'days_to_harvest' => $data['days_to_harvest'] ?? null,
            'harvest_window_days' => $data['harvest_window_days'] ?? null,
            'expected_loss_rate' => $data['expected_loss_rate'] ?? null,
            'harvest_unit' => $this->mapHarvestUnit($data['harvest_unit'] ?? null),
            'expected_yield_per_hectare' => $data['expected_yield_per_hectare'] ?? null,
            'quantity_planted' => $data['quantity_planted'] ?? null,
        ];
        
        // Normalize: treat empty string as null for location
        $rawLocationIds = $data['planting_location_ids'] ?? [];
        $plantingLocationIds = array_values(array_map(function ($id) {
            return ($id === '' || $id === null) ? null : $id;
        }, array_filter($rawLocationIds, function ($id) {
            return $id !== null && $id !== '';
        })));
        // If we have one empty selection, treat as one null location
        if (!empty($rawLocationIds) && count($plantingLocationIds) === 0 && in_array('', $rawLocationIds, true)) {
            $plantingLocationIds = [null];
        }
        
        $existingPlantings = $plant->plantings;
        
        if (!empty($plantingLocationIds)) {
            foreach ($plantingLocationIds as $locationId) {
                $plantingData['planting_location_id'] = $locationId;
                $existingPlanting = $existingPlantings->firstWhere('planting_location_id', $locationId);
                
                if ($existingPlanting) {
                    $existingPlanting->update($plantingData);
                } else {
                    $plantingData['plant_id'] = $plant->plant_id;
                    Planting::create($plantingData);
                }
            }
            // Hapus record penanaman yang lokasinya tidak lagi dipilih (dan belum punya panen)
            $plantingsToDelete = $existingPlantings->whereNotIn('planting_location_id', $plantingLocationIds);
            foreach ($plantingsToDelete as $plantingToDelete) {
                if (!$plantingToDelete->harvest) {
                    $plantingToDelete->delete();
                }
            }
        } else {
            // Tanpa lokasi: tetap simpan detail tanaman ke satu record Planting (update yang pertama atau buat baru)
            $first = $existingPlantings->first();
            $plantingData['plant_id'] = $plant->plant_id;
            $plantingData['planting_location_id'] = null;
            if ($first) {
                $first->update($plantingData);
            } else {
                Planting::create($plantingData);
            }
        }
        
        return redirect()->route('plants.show', $plant)->with('success', 'Tanaman diperbarui');
    }

    /**
     * Show current plantings for a plant
     */
    public function currentPlantings(Plant $plant, Request $request)
    {
        $plant->load(['type']);
        
        // Get all active plantings for this plant
        // Active means not completed (is_completed = false)
        $currentPlantings = Planting::where('plant_id', $plant->plant_id)
            ->with(['harvests', 'losses'])
            ->where('is_completed', false)
            ->get();
        
        // Get all planting locations for the "Tanam Baru" form
        $allPlantingLocations = PlantingLocation::orderBy('name')->get();
        
        // Get all plants for the "Tanam Baru" form
        $allPlants = Plant::with('type')->orderBy('name')->get();
        
        // Group by location for better display
        $plantingsByLocation = $currentPlantings->groupBy('planting_location_id');
        
        // Get harvested, lost, and failed plantings for sub-tabs
        // Harvested plantings: any planting that has at least one harvest with quantity > 0
        // Removed is_completed check so all plantings with harvests are shown
        $harvestedPlantings = Planting::where('plant_id', $plant->plant_id)
            ->whereHas('harvests', function($q) {
                $q->where('quantity', '>', 0);
            })
            ->with(['harvests' => function($q) {
                $q->where('quantity', '>', 0)->orderBy('harvested_at', 'desc');
            }, 'location'])
            ->orderBy('planted_at', 'desc')
            ->get();
        
        $lostPlantings = Planting::where('plant_id', $plant->plant_id)
            ->whereHas('losses')
            ->with(['losses'])
            ->orderBy('planted_at', 'desc')
            ->get();
        
        $failedPlantings = Planting::where('plant_id', $plant->plant_id)
            ->whereHas('harvests', function($q) {
                $q->where('quantity', '<=', 0);
            })
            ->with(['harvests'])
            ->orderBy('planted_at', 'desc')
            ->get();
        
        return view('planting.plants.current-plantings', compact(
            'plant', 
            'currentPlantings', 
            'plantingsByLocation',
            'allPlantingLocations',
            'allPlants',
            'harvestedPlantings',
            'lostPlantings',
            'failedPlantings'
        ));
    }

    /**
     * Show reports for a specific planting
     */
    public function showPlantingReports(Plant $plant, Planting $planting, Request $request)
    {
        $plant->load(['type']);
        
        // Verify that this planting belongs to this plant
        if ($planting->plant_id != $plant->plant_id) {
            abort(404, 'Penanaman tidak ditemukan untuk tanaman ini.');
        }
        
        $plantingLocation = $planting->location;
        
        // Check if user has access to this planting location
        $user = auth()->user();
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Load tasks for this planting
        $statusFilter = $request->get('status', 'all'); // Default to 'all' to show "Semua Laporan" tab
        $assigneeFilter = $request->get('assignee', 'all');
        $taskYear = $request->get('task_year', '');
        $taskMonth = $request->get('task_month', '');
        
        $tasksQuery = $plantingLocation->tasks()
            ->where('planting_id', $planting->planting_id)
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
        
        // Get available years for task filter
        $existingYears = $plantingLocation->tasks()
            ->where('planting_id', $planting->planting_id)
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
        
        // Load treatments for this planting
        $treatments = $plantingLocation->treatments()
            ->where('planting_id', $planting->planting_id)
            ->with(['plantingLocation', 'planting.plant', 'responsiblePerson', 'editor'])
            ->orderBy('treatment_date', 'desc')
            ->get();
        
        // Load nutrients for this planting
        $nutrients = $plantingLocation->nutrients()
            ->where('planting_id', $planting->planting_id)
            ->with(['plantingLocation', 'planting.plant', 'editor'])
            ->orderBy('application_date', 'desc')
            ->get();
        
        // Load notes for this planting location
        $notes = $plantingLocation->notes()
            ->with('user')
            ->orderBy('note_date', 'desc')
            ->get();
        
        // Load photos for this planting location
        $photos = $plantingLocation->photos()
            ->orderBy('taken_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Load attachments for this planting location
        $attachments = $plantingLocation->attachments()
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
        
        // Get active plantings for dropdowns (only this planting)
        $activePlantings = collect([$planting]);
        
        // Get all plantings for this location (for association dropdowns)
        $allPlantingsForLocation = $plantingLocation->plantings()
            ->with(['plant'])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        return view('planting.plants.planting-reports', compact(
            'plant',
            'planting',
            'plantingLocation',
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
            'activePlantings',
            'allTasks',
            'allPlantingsForLocation'
        ));
    }

    /**
     * Show harvest history for a plant
     */
    public function harvestsIndex(Plant $plant, Request $request)
    {
        $plant->load(['type']);
        
        $query = Harvest::where('plant_id', $plant->plant_id)
            ->with(['planting', 'certification'])
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
            $q->where('plant_id', $plant->plant_id);
        })->orderBy('name')->get();
        
        // Get available years for filter
        $years = Harvest::where('plant_id', $plant->plant_id)
            ->selectRaw('YEAR(harvested_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('planting.plants.harvests', compact('plant', 'harvests', 'locations', 'years'));
    }

    public function destroy(Plant $plant)
    {
        // Prevent penangkar from deleting plants
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data tanaman.');
        }
        
        $plant->delete();
        return redirect()->route('plants.index')->with('success', 'Tanaman berhasil dihapus');
    }
}







