<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLoss;
use App\Models\Location;
use App\Models\Task;
use App\Models\PlantingLocationNote;
use App\Models\PlantingLocationPhoto;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\User;
use App\Models\Harvest;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantingLocationController extends Controller
{
    public function index()
    {
        $plantingLocations = PlantingLocation::with(['baseLocation'])->orderBy('name')->paginate(15);
        $locations = Location::orderBy('name')->get();
        return view('planting/planting-locations/index', compact('plantingLocations', 'locations'));
    }

    public function create()
    {
        $locations = Location::orderBy('name')->get();
        $contacts = Contact::orderBy('full_name')->get();
        return view('planting/planting-locations/create', compact('locations', 'contacts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'location_summary' => 'nullable|string|max:255',
            'administrative_address' => 'nullable|string',
            'google_maps_link' => 'nullable|url|max:255',
            'primary_photo' => 'nullable|image|max:5120',
            'location_type' => 'required|in:lapangan,greenhouse,grow_room,padang_rumput,petak_ternak,lainnya',
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
            'responsible_contact_ids' => 'nullable|array',
            'responsible_contact_ids.*' => 'exists:contacts,id',
            'land_manager_contact_ids' => 'nullable|array',
            'land_manager_contact_ids.*' => 'exists:contacts,id',
            'land_worker_contact_ids' => 'nullable|array',
            'land_worker_contact_ids.*' => 'exists:contacts,id',
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
            $data['responsible_contact_ids'],
            $data['land_manager_contact_ids'],
            $data['land_worker_contact_ids']
        );

        if ($request->hasFile('primary_photo')) {
            $data['primary_photo_path'] = $request->file('primary_photo')->store('planting-location', 'public');
        }

        $loc = PlantingLocation::create($data);

        $loc->responsibleContacts()->sync($request->input('responsible_contact_ids', []));
        $loc->landManagerContacts()->sync($request->input('land_manager_contact_ids', []));
        $loc->landWorkerContacts()->sync($request->input('land_worker_contact_ids', []));

        return redirect()->route('planting-locations.show', $loc)->with('success', 'Lokasi penanaman ditambahkan');
    }

    public function show(PlantingLocation $plantingLocation, Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        // Load active plantings (not harvested)
        $activePlantings = $plantingLocation->plantings()
            ->whereDoesntHave('harvest')
            ->with(['plant', 'plant.type', 'losses'])
            ->whereNotNull('planted_at')
            ->whereYear('planted_at', $year)
            ->orderBy('planted_at', 'desc')
            ->get();
        
        // Load historical plantings (harvested or old)
        $historicalPlantings = $plantingLocation->plantings()
            ->with(['plant', 'plant.type', 'harvest'])
            ->whereNotNull('planted_at')
            ->whereYear('planted_at', $year)
            ->orderBy('planted_at', 'desc')
            ->get()
            ->filter(function($planting) {
                return $planting->harvest || ($planting->planted_at && $planting->planted_at->year < date('Y'));
            });
        
        // Load tasks
        $statusFilter = $request->get('status', 'all');
        $assigneeFilter = $request->get('assignee', 'all');
        
        $tasksQuery = $plantingLocation->tasks()->with(['assignedUser', 'plant']);
        
        if ($statusFilter !== 'all') {
            $tasksQuery->where('new_status', $statusFilter);
        }
        
        if ($assigneeFilter !== 'all') {
            $tasksQuery->where('assigned_to', $assigneeFilter);
        }
        
        $tasks = $tasksQuery->orderBy('due_date', 'asc')->get();
        
        // Load notes
        $notes = $plantingLocation->notes()->with('user')->orderBy('note_date', 'desc')->get();
        
        // Load photos
        $photos = $plantingLocation->photos()->orderBy('taken_at', 'desc')->orderBy('created_at', 'desc')->get();
        
        // Load treatments
        $treatments = $plantingLocation->treatments()->with('plantingLocation')->orderBy('treatment_date', 'desc')->get();
        
        // Load nutrients
        $nutrients = $plantingLocation->nutrients()->with('plantingLocation')->orderBy('application_date', 'desc')->get();
        
        // Get all plants for dropdown
        $allPlants = Plant::with('type')->orderBy('name')->get();
        
        // Get all users for task assignment
        $users = User::orderBy('name')->get();
        
        // Get inventory types for treatment dropdown
        $inventoryTypes = \App\Models\InventoryType::orderBy('name')->get();
        
        // Get planting years for filter
        $plantingYears = $plantingLocation->plantings()
            ->whereNotNull('planted_at')
            ->selectRaw('YEAR(planted_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // If no plantings, add current year
        if ($plantingYears->isEmpty()) {
            $plantingYears = collect([date('Y')]);
        }
        
        $plantingLocation->load(['baseLocation', 'responsibleContacts', 'landManagerContacts', 'landWorkerContacts']);
        
        return view('planting/planting-locations/show', compact(
            'plantingLocation',
            'activePlantings',
            'historicalPlantings',
            'tasks',
            'notes',
            'photos',
            'treatments',
            'nutrients',
            'allPlants',
            'users',
            'inventoryTypes',
            'year',
            'plantingYears',
            'statusFilter',
            'assigneeFilter'
        ));
    }

    public function edit(PlantingLocation $plantingLocation)
    {
        $locations = Location::orderBy('name')->get();
        $contacts = Contact::orderBy('full_name')->get();
        $plantingLocation->load(['responsibleContacts', 'landManagerContacts', 'landWorkerContacts']);

        return view('planting/planting-locations/edit', compact('plantingLocation', 'locations', 'contacts'));
    }

    public function update(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'location_summary' => 'nullable|string|max:255',
            'administrative_address' => 'nullable|string',
            'google_maps_link' => 'nullable|url|max:255',
            'primary_photo' => 'nullable|image|max:5120',
            'location_type' => 'required|in:lapangan,greenhouse,grow_room,padang_rumput,petak_ternak,lainnya',
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
            'responsible_contact_ids' => 'nullable|array',
            'responsible_contact_ids.*' => 'exists:contacts,id',
            'land_manager_contact_ids' => 'nullable|array',
            'land_manager_contact_ids.*' => 'exists:contacts,id',
            'land_worker_contact_ids' => 'nullable|array',
            'land_worker_contact_ids.*' => 'exists:contacts,id',
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
            $data['responsible_contact_ids'],
            $data['land_manager_contact_ids'],
            $data['land_worker_contact_ids']
        );

        if ($request->hasFile('primary_photo')) {
            if ($plantingLocation->primary_photo_path) {
                Storage::disk('public')->delete($plantingLocation->primary_photo_path);
            }

            $data['primary_photo_path'] = $request->file('primary_photo')->store('planting-location', 'public');
        }

        $plantingLocation->update($data);

        $plantingLocation->responsibleContacts()->sync($request->input('responsible_contact_ids', []));
        $plantingLocation->landManagerContacts()->sync($request->input('land_manager_contact_ids', []));
        $plantingLocation->landWorkerContacts()->sync($request->input('land_worker_contact_ids', []));

        return redirect()->route('planting-locations.show', $plantingLocation)->with('success', 'Lokasi penanaman diperbarui');
    }

    public function destroy(PlantingLocation $plantingLocation)
    {
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
        // Valid harvest unit values from ENUM
        $validHarvestUnits = ['ikat','barel','tandan','gantang','lusin','gram','batang','kilogram','kiloliter','liter','mililiter','satuan','ton'];
        
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'planted_at' => 'required|date',
            'quantity_planted' => 'required|numeric|min:0',
            'bed_label' => 'nullable|string|max:255',
            'days_to_harvest' => 'nullable|integer|min:0',
            'harvest_unit' => 'nullable|string|in:' . implode(',', $validHarvestUnits),
            'notes' => 'nullable|string',
        ]);

        $plant = Plant::findOrFail($data['plant_id']);
        
        // Auto-fill from plant type if available
        if ($plant->type) {
            $data['days_to_harvest'] = $data['days_to_harvest'] ?? $plant->type->days_to_harvest;
            
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

        $data['planting_location_id'] = $plantingLocation->id;
        $planting = Planting::create($data);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Penanaman berhasil ditambahkan');
    }

    // Store loss for a planting
    public function storeLoss(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'planting_id' => 'required|exists:plantings,id',
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

        $loss = PlantingLoss::create($data);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Kehilangan berhasil dicatat');
    }

    // Store task for this location
    public function storeTask(Request $request, PlantingLocation $plantingLocation)
    {
        $actionType = $request->input('action_type', 'create');
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_report' => 'nullable|string',
            'checklist' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'association' => 'required|string',
            'planting_id' => 'nullable|exists:plantings,id',
            'new_status' => 'required|in:dilakukan,dalam_progress,selesai,tidak_selesai,terlewat,ditinggalkan',
            'assigned_to' => 'nullable',
            'assigned_contact_id' => 'nullable|exists:contacts,id',
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

        // Handle assigned_to - check if it's a contact or user
        if ($request->filled('assigned_contact_id')) {
            // If contact is assigned, we store contact_id separately
            // For now, we'll store it in a note or handle differently
            // Since Task uses assigned_to for users, we'll leave it null for contacts
            $data['assigned_to'] = null;
        } elseif ($request->filled('assigned_to') && !str_starts_with($request->assigned_to, 'contact_')) {
            $data['assigned_to'] = $request->assigned_to;
        } else {
            $data['assigned_to'] = null;
        }

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('task-attachments', 'public');
            }
            $data['attachments'] = $attachments;
        }

        // Filter out contact assignment from data array
        unset($data['assigned_contact_id']);

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
                ->with('success', 'Template tugas berhasil disimpan');
        }

        $data['planting_location_id'] = $plantingLocation->id;
        $data['association'] = $data['association'] ?? 'penanaman';
        
        Task::create($data);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Tugas berhasil ditambahkan');
    }

    // Store note for this location
    public function storeNote(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'note_date' => 'required|date',
            'keywords' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $data['planting_location_id'] = $plantingLocation->id;
        $data['user_id'] = auth()->id();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('planting-location-notes', 'public');
        }

        PlantingLocationNote::create($data);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Catatan berhasil ditambahkan');
    }

    // Store photo for this location
    public function storePhoto(Request $request, PlantingLocation $plantingLocation)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|max:5120', // 5MB per photo
            'description' => 'nullable|string|max:255',
            'taken_at' => 'nullable|date',
        ]);

        foreach ($request->file('photos') as $photo) {
            $filePath = $photo->store('planting-location-photos', 'public');
            
            PlantingLocationPhoto::create([
                'planting_location_id' => $plantingLocation->id,
                'file_path' => $filePath,
                'file_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'description' => $request->description,
                'taken_at' => $request->taken_at,
            ]);
        }
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Foto berhasil diunggah');
    }

    // Mark planting as failed
    public function markPlantingFailed(PlantingLocation $plantingLocation, Planting $planting)
    {
        // Create a harvest record with zero quantity to mark as failed
        Harvest::create([
            'plant_id' => $planting->plant_id,
            'planting_id' => $planting->id,
            'planting_location_id' => $plantingLocation->id,
            'harvested_at' => now(),
            'batch_no' => 'FAILED-' . date('Y') . '-' . str_pad($planting->id, 3, '0', STR_PAD_LEFT),
            'quantity' => 0,
            'unit' => 'kg',
            'quality' => 'Gagal Panen',
            'note' => 'Tanaman ditandai sebagai gagal panen',
        ]);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Penanaman ditandai sebagai gagal');
    }

    // Store treatment for this location
    public function storeTreatment(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'treatment_type' => 'required|string|max:255',
            'product_detail' => 'nullable|string|max:255',
            'subtract_from_inventory' => 'nullable|exists:inventory_types,id',
            'application_method' => 'required|string|max:255',
            'withholding_period_days' => 'nullable|integer|min:0',
            'technician' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'treatment_date' => 'required|date',
            'batch_number' => 'nullable|string|max:255',
            'amount_applied' => 'nullable|numeric|min:0',
            'inventory_amount_used' => 'nullable|numeric|min:0',
            'inventory_unit' => 'nullable|string|max:255',
            'treatment_location' => 'nullable|string|max:255',
            'retreat_date' => 'nullable|date',
            'total_cost' => 'nullable|numeric|min:0',
            'record_expense' => 'boolean',
            'keywords' => 'nullable|string|max:255',
            'planting_id' => 'nullable|exists:plantings,id',
        ]);

        $data['planting_location_id'] = $plantingLocation->id;
        
        // Handle record_expense checkbox
        $data['record_expense'] = $request->has('record_expense') && $request->record_expense == '1';
        
        Treatment::create($data);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Data perawatan berhasil ditambahkan');
    }

    // Store nutrient for this location
    public function storeNutrient(Request $request, PlantingLocation $plantingLocation)
    {
        $data = $request->validate([
            'product_applied' => 'required|string|max:255',
            'application_date' => 'required|date',
            'amount_applied' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'application_method' => 'required|string|max:255',
            'total_cost' => 'nullable|numeric|min:0',
            'technician' => 'nullable|string|max:255',
            'planting_id' => 'nullable|exists:plantings,id',
            'description' => 'nullable|string',
        ]);

        $data['planting_location_id'] = $plantingLocation->id;
        
        Nutrient::create($data);
        
        return redirect()->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Data nutrisi berhasil ditambahkan');
    }
}


