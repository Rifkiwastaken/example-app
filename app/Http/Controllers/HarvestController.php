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
            'plant_id' => 'required|exists:plants,plant_id',
            'planting_id' => 'nullable|exists:plantings,planting_id',
            'planting_location_id' => 'required|exists:planting_locations,planting_location_id',
            'harvested_at' => 'required|date',
            'batch_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'source' => 'required|string|max:255',
            'quality' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'loss_quantity' => 'nullable|numeric|min:0',
            'harvest_unit' => 'nullable|string|max:255',
            'unit_quantity' => 'nullable|numeric|min:0',
            'quantity_per_unit' => 'nullable|numeric|min:0',
            'quantity_per_unit_kg' => 'nullable|numeric|min:0',
            'recorded_by' => 'nullable|exists:users,user_id',
            'action' => 'nullable|string|in:save_and_complete,save_and_continue',
        ]);

        // If quantity_per_unit_kg is provided, use it as quantity_per_unit
        if ($request->filled('quantity_per_unit_kg')) {
            $data['quantity_per_unit'] = $request->input('quantity_per_unit_kg');
        }
        unset($data['quantity_per_unit_kg']); // Remove from data as it's not in fillable
        
        // Set recorded_by to current user if not provided
        if (!isset($data['recorded_by']) || !$data['recorded_by']) {
            $data['recorded_by'] = auth()->user()->user_id;
        }
        
        // If planting_id is not provided but planting_location_id and plant_id are provided,
        // try to find the most recent active planting for this location and plant
        if (empty($data['planting_id']) && !empty($data['planting_location_id']) && !empty($data['plant_id'])) {
            $planting = Planting::where('planting_location_id', $data['planting_location_id'])
                ->where('plant_id', $data['plant_id'])
                ->whereNotNull('planted_at')
                ->orderBy('planted_at', 'desc')
                ->first();
            
            if ($planting) {
                $data['planting_id'] = $planting->planting_id;
            }
        }
        
        // Handle action: save_and_complete or save_and_continue
        $action = $request->input('action', 'save_and_complete');
        unset($data['action']); // Remove action from data before creating harvest

        try {
            $harvest = Harvest::create($data);
        } catch (\Exception $e) {
            \Log::error('Error creating harvest: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan data panen: ' . $e->getMessage()])->withInput();
        }
        
        $message = 'Data panen berhasil ditambahkan';
        $planting = null;
        
        // Update planting status based on action
        if ($request->filled('planting_id')) {
            $planting = Planting::find($request->planting_id);
            if ($planting) {
                if ($action === 'save_and_complete') {
                    // Mark planting as completed
                    $planting->update(['is_completed' => true]);
                    $message = 'Data panen berhasil ditambahkan dan penanaman telah diselesaikan';
                } elseif ($action === 'save_and_continue') {
                    // Keep current planting active (data penanaman tetap ada di record)
                    // Don't mark as completed - the planting continues
                    $planting->update(['is_completed' => false]);
                    $message = 'Data panen berhasil ditambahkan. Silakan isi form penanaman baru untuk melanjutkan.';
                    
                    // Redirect to planting location page with flag to open new planting modal
                    // Pass previous planting data to pre-fill the form
                    $plantingLocation = PlantingLocation::find($planting->planting_location_id);
                    if ($plantingLocation) {
                        return redirect()->route('planting-locations.plantings.index', $plantingLocation)
                            ->with('success', $message)
                            ->with('open_new_planting_modal', true)
                            ->with('prefill_plant_id', $planting->plant_id)
                            ->with('prefill_bed_label', $planting->bed_label)
                            ->with('prefill_notes', 'Lanjutan dari penanaman batch: ' . ($planting->planting_batch_number ?? '-'));
                    }
                }
            }
        } else {
            if ($action === 'save_and_complete') {
                $message = 'Data panen berhasil ditambahkan dan penanaman telah diselesaikan';
            } elseif ($action === 'save_and_continue') {
                $message = 'Data panen berhasil ditambahkan. Silakan buat penanaman baru.';
            }
        }
        
        // Redirect based on source
        if ($request->filled('planting_location_id') && $request->filled('from_planting_location')) {
            // From planting location current plantings page
            $plantingLocationId = $request->input('planting_location_id');
            
            // If save_and_continue, also pass prefill data
            if ($action === 'save_and_continue' && $planting) {
                return redirect()->route('planting-locations.plantings.index', $plantingLocationId)
                    ->with('success', $message)
                    ->with('open_new_planting_modal', true)
                    ->with('prefill_plant_id', $planting->plant_id)
                    ->with('prefill_bed_label', $planting->bed_label)
                    ->with('prefill_notes', 'Lanjutan dari penanaman batch: ' . ($planting->planting_batch_number ?? '-'));
            }
            
            return redirect()->route('planting-locations.plantings.index', $plantingLocationId)
                ->with('success', $message);
        } elseif ($request->filled('planting_id')) {
            // Get planting to find plant
            if ($planting && $planting->plant) {
                return redirect()->route('plants.current-plantings', $planting->plant)
                    ->with('success', $message);
            }
        }
        
        // Default redirect to harvests index
        if ($harvest->plant) {
            return redirect()->route('plants.harvests.index', $harvest->plant)
                ->with('success', $message);
        }
        
        // Fallback redirect
        return redirect()->back()->with('success', $message);
    }

    public function show(Harvest $harvest)
    {
        $harvest->load(['plant', 'planting', 'location', 'recorder', 'editor']);
        
        // If request is AJAX, return JSON response
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'harvest' => [
                    'id' => $harvest->harvest_id,
                    'harvested_at' => $harvest->harvested_at ? $harvest->harvested_at->format('Y-m-d') : null,
                    'harvested_at_formatted' => $harvest->harvested_at ? $harvest->harvested_at->format('d M Y') : '-',
                    'batch_no' => $harvest->batch_no ?? '-',
                    'quantity' => $harvest->quantity ?? 0,
                    'quantity_formatted' => number_format($harvest->quantity ?? 0, 2),
                    'unit' => $harvest->unit ?? 'kg',
                    'harvest_unit' => $harvest->harvest_unit ?? null,
                    'unit_quantity' => $harvest->unit_quantity ?? null,
                    'quantity_per_unit' => $harvest->quantity_per_unit ?? null,
                    'source' => $harvest->source ?? '-',
                    'quality' => $harvest->quality ?? null,
                    'loss_quantity' => $harvest->loss_quantity ?? null,
                    'loss_quantity_formatted' => $harvest->loss_quantity ? number_format($harvest->loss_quantity, 2) . ' ' . ($harvest->unit ?? 'kg') : '-',
                    'note' => $harvest->note ?? null,
                    'location_name' => $harvest->location->name ?? null,
                    'bed_label' => $harvest->planting->bed_label ?? null,
                    'recorder_name' => $harvest->recorder->name ?? null,
                    'edited_at' => $harvest->edited_at ? $harvest->edited_at->format('Y-m-d H:i:s') : null,
                    'edited_at_formatted' => $harvest->edited_at ? $harvest->edited_at->format('d M Y H:i') : null,
                    'editor_name' => $harvest->editor->name ?? null,
                ]
            ]);
        }
        
        return view('planting/harvests/show', compact('harvest'));
    }

    public function edit(Harvest $harvest)
    {
        // Prevent penangkar from editing harvests
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data panen.');
        }
        
        $harvest->load(['plant', 'planting', 'location', 'recorder', 'editor']);
        $plants = Plant::orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        // Get plantings - if location_id is provided in request, filter by it
        $plantingsQuery = Planting::where('plant_id', $harvest->plant_id);
        if ($request->filled('planting_location_id')) {
            $plantingsQuery->where('planting_location_id', $request->planting_location_id);
        } else {
            $plantingsQuery->where('planting_location_id', $harvest->planting_location_id);
        }
        $plantings = $plantingsQuery->get();
        
        // Get users related to planting location
        $locationUsers = collect();
        if ($harvest->planting_location_id && $harvest->location) {
            $landManagerUsers = $harvest->location->landManagerUsers;
            $landWorkerUsers = $harvest->location->landWorkerUsers;
            $locationUsers = $landManagerUsers->merge($landWorkerUsers)->unique('id');
        }
        
        // If request is AJAX, return JSON response
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'harvest' => [
                    'id' => $harvest->harvest_id,
                    'plant_id' => $harvest->plant_id ?? null,
                    'harvested_at' => $harvest->harvested_at ? $harvest->harvested_at->format('Y-m-d') : null,
                    'batch_no' => $harvest->batch_no ?? '',
                    'quantity' => $harvest->quantity ?? 0,
                    'unit' => $harvest->unit ?? 'kg',
                    'harvest_unit' => $harvest->harvest_unit ?? null,
                    'unit_quantity' => $harvest->unit_quantity ?? null,
                    'quantity_per_unit' => $harvest->quantity_per_unit ?? null,
                    'quantity_per_unit_kg' => $harvest->quantity_per_unit ?? null,
                    'quantity_per_unit_unit' => $harvest->unit ?? 'kg',
                    'planting_location_id' => $harvest->planting_location_id ?? null,
                    'planting_id' => $harvest->planting_id ?? null,
                    'source' => $harvest->source ?? '',
                    'quality' => $harvest->quality ?? null,
                    'loss_quantity' => $harvest->loss_quantity ?? null,
                    'note' => $harvest->note ?? null,
                    'recorded_by' => $harvest->recorded_by ?? null,
                ],
                'locations' => $locations->map(function($loc) {
                    return ['id' => $loc->planting_location_id, 'name' => $loc->name];
                })->values(),
                'plantings' => $plantings->map(function($p) {
                    return ['id' => $p->planting_id, 'bed_label' => $p->bed_label ?? '-'];
                })->values(),
                'all_plantings' => Planting::where('plant_id', $harvest->plant_id)
                    ->get()
                    ->map(function($p) {
                        return ['id' => $p->planting_id, 'bed_label' => $p->bed_label ?? '-', 'planting_location_id' => $p->planting_location_id];
                    })->values(),
                'users' => $locationUsers->map(function($u) {
                    return ['id' => $u->user_id, 'name' => $u->name];
                })->values(),
            ]);
        }
        
        return view('planting/harvests/edit', compact('harvest', 'plants', 'locations', 'plantings'));
    }

    public function update(Request $request, Harvest $harvest)
    {
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,plant_id',
            'planting_id' => 'nullable|exists:plantings,planting_id',
            'planting_location_id' => 'required|exists:planting_locations,planting_location_id',
            'harvested_at' => 'required|date',
            'batch_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'source' => 'required|string|max:255',
            'quality' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'loss_quantity' => 'nullable|numeric|min:0',
            'harvest_unit' => 'nullable|string|max:255',
            'unit_quantity' => 'nullable|numeric|min:0',
            'quantity_per_unit' => 'nullable|numeric|min:0',
            'quantity_per_unit_kg' => 'nullable|numeric|min:0',
            'recorded_by' => 'nullable|exists:users,user_id',
        ]);

        // If quantity_per_unit_kg is provided, use it as quantity_per_unit
        if ($request->filled('quantity_per_unit_kg')) {
            $data['quantity_per_unit'] = $request->input('quantity_per_unit_kg');
        }
        unset($data['quantity_per_unit_kg']); // Remove from data as it's not in fillable

        // Don't update recorded_by on edit - it should remain as the original recorder
        unset($data['recorded_by']);

        // Add edited_at and edited_by
        $data['edited_at'] = now();
        $data['edited_by'] = auth()->user()->user_id;
        
        $harvest->update($data);
        
        // If request is AJAX, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data panen berhasil diperbarui'
            ]);
        }
        
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

    /**
     * Show detailed harvest information (for modal display)
     */
    public function showDetail(Harvest $harvest)
    {
        try {
            $harvest->load(['plant', 'planting.plant', 'location', 'recorder', 'editor']);
            
            // Get planting data
            $planting = $harvest->planting;
            
            // Get tasks related to this planting (from planting_location)
            $tasks = [];
            if ($harvest->planting_location_id && $planting) {
                try {
                    $tasks = \App\Models\Task::where('planting_location_id', $harvest->planting_location_id)
                        ->where(function($query) use ($planting) {
                            $query->where('planting_id', $planting->planting_id)
                                  ->orWhereNull('planting_id');
                        })
                        ->with(['assignedUser'])
                        ->orderBy('due_date', 'desc')
                        ->get()
                        ->map(function($task) {
                            return [
                                'title' => $task->title ?? '-',
                                'due_date' => $task->due_date ? $task->due_date->format('d M Y') : '-',
                                'new_status' => $task->new_status ?? 'tidak_selesai',
                                'new_priority' => $task->new_priority ?? 'medium',
                                'assigned_user_name' => $task->assignedUser ? $task->assignedUser->name : '-',
                            ];
                        })->toArray();
                } catch (\Exception $e) {
                    \Log::error('Error loading tasks: ' . $e->getMessage());
                }
            }
            
            // Get treatments related to this planting (from planting_location)
            $treatments = [];
            if ($harvest->planting_location_id && $planting) {
                try {
                    $treatments = \App\Models\Treatment::where('planting_location_id', $harvest->planting_location_id)
                        ->where(function($query) use ($planting) {
                            $query->where('planting_id', $planting->planting_id)
                                  ->orWhereNull('planting_id');
                        })
                        ->orderBy('treatment_date', 'desc')
                        ->get()
                        ->map(function($treatment) {
                            return [
                                'treatment_date' => $treatment->treatment_date ? $treatment->treatment_date->format('d M Y') : '-',
                                'treatment_name' => $treatment->treatment_name ?? '-',
                                'treatment_type' => $treatment->treatment_type ?? '-',
                                'product_detail' => $treatment->product_detail ?? '-',
                                'application_method' => $treatment->application_method ?? '-',
                                'total_cost' => $treatment->total_cost ?? 0,
                            ];
                        })->toArray();
                } catch (\Exception $e) {
                    \Log::error('Error loading treatments: ' . $e->getMessage());
                }
            }
            
            // Get nutrients related to this planting (from planting_location)
            $nutrients = [];
            if ($harvest->planting_location_id && $planting) {
                try {
                    $nutrients = \App\Models\Nutrient::where('planting_location_id', $harvest->planting_location_id)
                        ->where(function($query) use ($planting) {
                            $query->where('planting_id', $planting->planting_id)
                                  ->orWhereNull('planting_id');
                        })
                        ->orderBy('application_date', 'desc')
                        ->get()
                        ->map(function($nutrient) {
                            return [
                                'application_date' => $nutrient->application_date ? $nutrient->application_date->format('d M Y') : '-',
                                'product_applied' => $nutrient->product_applied ?? '-',
                                'amount_applied' => $nutrient->amount_applied ?? 0,
                                'unit' => $nutrient->unit ?? 'kg',
                                'application_method' => $nutrient->application_method ?? '-',
                                'total_cost' => $nutrient->total_cost ?? 0,
                            ];
                        })->toArray();
                } catch (\Exception $e) {
                    \Log::error('Error loading nutrients: ' . $e->getMessage());
                }
            }
            
            // Get notes related to planting location
            $notes = [];
            if ($harvest->planting_location_id) {
                try {
                    $notes = \App\Models\PlantingLocationNote::where('planting_location_id', $harvest->planting_location_id)
                        ->with(['user'])
                        ->orderBy('note_date', 'desc')
                        ->get()
                        ->map(function($note) {
                            return [
                                'note_date' => $note->note_date ? $note->note_date->format('d M Y') : '-',
                                'title' => $note->title ?? 'Catatan',
                                'description_short' => \Illuminate\Support\Str::limit($note->description ?? '', 100),
                                'user_name' => $note->user ? $note->user->name : '-',
                            ];
                        })->toArray();
                } catch (\Exception $e) {
                    \Log::error('Error loading notes: ' . $e->getMessage());
                }
            }
            
            // Get expenses related to this planting (from planting_location)
            $expenses = [];
            $totalTreatmentCost = 0;
            $totalNutrientCost = 0;
            $totalOtherExpenses = 0;
            
            if ($harvest->planting_location_id && $planting) {
                try {
                    // Get all expenses for this planting location and planting
                    $allExpenses = \App\Models\Expense::where('planting_location_id', $harvest->planting_location_id)
                        ->where(function($query) use ($planting) {
                            $query->where('planting_id', $planting->planting_id)
                                  ->orWhereNull('planting_id');
                        })
                        ->orderBy('expense_date', 'desc')
                        ->get();
                    
                    foreach ($allExpenses as $expense) {
                        $expenses[] = [
                            'expense_date' => $expense->expense_date ? $expense->expense_date->format('d M Y') : '-',
                            'expense_name' => $expense->expense_name ?? '-',
                            'expense_type_label' => ucfirst(str_replace('_', ' ', $expense->expense_type ?? 'lainnya')),
                            'amount' => $expense->amount ?? 0,
                        ];
                        
                        // Calculate totals by type
                        if ($expense->expense_type === 'perawatan') {
                            $totalTreatmentCost += $expense->amount ?? 0;
                        } elseif ($expense->expense_type === 'nutrisi') {
                            $totalNutrientCost += $expense->amount ?? 0;
                        } else {
                            $totalOtherExpenses += $expense->amount ?? 0;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error loading expenses: ' . $e->getMessage());
                }
            }
            
            $totalExpenses = $totalTreatmentCost + $totalNutrientCost + $totalOtherExpenses;
            
            return response()->json([
                'success' => true,
                'harvest' => [
                    'id' => $harvest->harvest_id,
                    'harvested_at_formatted' => $harvest->harvested_at ? $harvest->harvested_at->format('d M Y') : '-',
                    'batch_no' => $harvest->batch_no ?? '-',
                    'quantity' => $harvest->quantity ?? 0,
                    'quantity_formatted' => number_format($harvest->quantity ?? 0, 2),
                    'unit' => $harvest->unit ?? 'kg',
                    'quality' => $harvest->quality ?? '-',
                    'note' => $harvest->note ?? null,
                ],
                'planting' => [
                    'plant_name' => $planting && $planting->plant ? $planting->plant->name : ($harvest->plant ? $harvest->plant->name : '-'),
                    'variety' => $planting && $planting->plant ? $planting->plant->variety : ($harvest->plant ? $harvest->plant->variety : '-'),
                    'planting_batch_number' => $planting ? ($planting->planting_batch_number ?? '-') : '-',
                    'bed_label' => $planting ? ($planting->bed_label ?? '-') : '-',
                    'quantity_planted' => $planting ? $planting->quantity_planted : null,
                    'planted_at' => $planting && $planting->planted_at ? $planting->planted_at->format('d F Y') : '-',
                    'estimated_harvest_date' => $planting && $planting->estimated_harvest_date ? $planting->estimated_harvest_date->format('d F Y') : '-',
                    'notes' => $planting ? $planting->notes : null,
                ],
                'tasks' => $tasks,
                'treatments' => $treatments,
                'nutrients' => $nutrients,
                'notes' => $notes,
                'expenses' => $expenses,
                'totalTreatmentCost' => $totalTreatmentCost,
                'totalNutrientCost' => $totalNutrientCost,
                'totalOtherExpenses' => $totalOtherExpenses,
                'totalExpenses' => $totalExpenses,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in showDetail: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data. Silakan refresh halaman dan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}





