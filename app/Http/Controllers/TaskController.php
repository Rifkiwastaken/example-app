<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskTemplate;
use App\Models\TaskSeries;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['assignedUser', 'template', 'series']);

        // Filter by association if user is not admin
        if (!auth()->user()->isAdmin()) {
            $userRole = auth()->user()->role;
            $allowedAssociations = $this->getAllowedAssociations($userRole);
            $query->whereIn('association', $allowedAssociations);
        }

        // Filter by assigned user if not admin
        if (!auth()->user()->isAdmin()) {
            $query->where('assigned_to', auth()->id());
        }

        // Apply filters
        if ($request->filled('association')) {
            $query->where('association', $request->association);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderBy('due_date', 'asc')->paginate(10);
        
        $associations = Task::getAssociations();
        $statuses = Task::getStatuses();
        $users = User::where('id', '!=', auth()->id())->get();

        return view('tasks.index', compact('tasks', 'associations', 'statuses', 'users'));
    }

    public function create()
    {
        $associations = Task::getAssociations();
        $priorities = Task::getPriorities();
        $statuses = Task::getStatuses();
        $users = User::where('id', '!=', auth()->id())->get();
        $templates = TaskTemplate::active()->get();
        $series = TaskSeries::active()->get();

        return view('tasks.create', compact('associations', 'priorities', 'statuses', 'users', 'templates', 'series'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_report' => 'nullable|string',
            'checklist' => 'nullable|array',
            'attachments' => 'nullable|array',
            'association' => 'required|in:' . implode(',', array_keys(Task::getAssociations())),
            'new_status' => 'required|in:' . implode(',', array_keys(Task::getStatuses())),
            'assigned_to' => 'nullable|exists:users,id',
            'new_priority' => 'required|in:' . implode(',', array_keys(Task::getPriorities())),
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'template_id' => 'nullable|exists:task_templates,id',
            'series_id' => 'nullable|exists:task_series,id',
        ]);

        $data = $validated;

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('task-attachments', 'public');
            }
            $data['attachments'] = $attachments;
        }

        Task::create($data);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan');
    }

    public function show(Task $task)
    {
        $task->load(['assignedUser', 'template', 'series']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task, Request $request)
    {
        $associations = Task::getAssociations();
        $priorities = Task::getPriorities();
        $statuses = Task::getStatuses();
        $users = User::where('id', '!=', auth()->id())->get();
        $templates = TaskTemplate::active()->get();
        $series = TaskSeries::active()->get();
        
        $returnToPlantingLocation = $request->get('return_to_planting_location');

        return view('tasks.edit', compact('task', 'associations', 'priorities', 'statuses', 'users', 'templates', 'series', 'returnToPlantingLocation'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_report' => 'nullable|string',
            'checklist' => 'nullable|array',
            'attachments' => 'nullable|array',
            'association' => 'required|in:' . implode(',', array_keys(Task::getAssociations())),
            'new_status' => 'required|in:' . implode(',', array_keys(Task::getStatuses())),
            'assigned_to' => 'nullable|exists:users,id',
            'new_priority' => 'required|in:' . implode(',', array_keys(Task::getPriorities())),
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'template_id' => 'nullable|exists:task_templates,id',
            'series_id' => 'nullable|exists:task_series,id',
        ]);

        $data = $validated;

        // Handle checklist - filter out empty items
        if (isset($data['checklist']) && is_array($data['checklist'])) {
            $data['checklist'] = array_values(array_filter($data['checklist'], function($item) {
                return !empty(trim($item));
            }));
            // If checklist is empty, set to null
            if (empty($data['checklist'])) {
                $data['checklist'] = null;
            }
        }

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $attachments = $task->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('task-attachments', 'public');
            }
            $data['attachments'] = $attachments;
        }

        $task->update($data);

        // Redirect back to planting location if task is associated with one
        if ($request->has('return_to_planting_location')) {
            return redirect()->route('planting-locations.show', $request->return_to_planting_location)
                ->with('success', 'Tugas berhasil diupdate');
        }

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diupdate');
    }

    public function destroy(Task $task)
    {
        // Delete attachments if exist
        if ($task->attachments) {
            foreach ($task->attachments as $attachment) {
                Storage::disk('public')->delete($attachment);
            }
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus');
    }

    /**
     * Show form for creating task from template
     */
    public function createFromTemplate()
    {
        $templates = TaskTemplate::active()->get();
        $series = TaskSeries::active()->get();
        $users = User::where('id', '!=', auth()->id())->get();

        return view('tasks.create-from-template', compact('templates', 'series', 'users'));
    }

    /**
     * Store task from template
     */
    public function storeFromTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:task_templates,id',
            'series_id' => 'nullable|exists:task_series,id',
            'start_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $template = TaskTemplate::find($validated['template_id']);
        $series = null;

        if ($validated['series_id']) {
            $series = TaskSeries::find($validated['series_id']);
        }

        // Create tasks based on template or series
        if ($series && $series->series_tasks) {
            foreach ($series->series_tasks as $taskData) {
                $dueDate = \Carbon\Carbon::parse($validated['start_date'])->addDays($taskData['days_offset'] ?? 0);
                
                Task::create([
                    'title' => $taskData['title'],
                    'description' => $taskData['description'] ?? null,
                    'association' => $template->association,
                    'new_status' => 'dilakukan',
                    'assigned_to' => $validated['assigned_to'],
                    'new_priority' => $taskData['priority'] ?? 'medium',
                    'start_date' => $validated['start_date'],
                    'due_date' => $dueDate->toDateString(),
                    'template_id' => $template->id,
                    'series_id' => $series->id,
                ]);
            }
        } else {
            // Create single task from template
            Task::create([
                'title' => $template->name,
                'description' => $template->description,
                'association' => $template->association,
                'new_status' => 'dilakukan',
                'assigned_to' => $validated['assigned_to'],
                'new_priority' => 'medium',
                'start_date' => $validated['start_date'],
                'due_date' => $validated['start_date'],
                'template_id' => $template->id,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dibuat dari template');
    }

    /**
     * Get allowed associations based on user role
     */
    private function getAllowedAssociations($role)
    {
        return match($role) {
            'admin' => array_keys(Task::getAssociations()),
            'kepala_satuan_tugas' => ['penanaman'],
            'petugas_sertifikasi' => ['sertifikasi'],
            'petugas_gudang' => ['gudang'],
            'petugas_bbi' => ['penjualan'],
            default => [],
        };
    }
}

