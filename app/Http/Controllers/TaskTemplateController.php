<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use App\Models\TaskSeries;
use Illuminate\Http\Request;

class TaskTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = TaskTemplate::withCount('tasks')->paginate(10);
        
        return view('task-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $associations = TaskTemplate::getAssociations();
        return view('task-templates.create', compact('associations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'association' => 'required|in:' . implode(',', array_keys(TaskTemplate::getAssociations())),
            'tasks_list' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['is_active'] = true;

        TaskTemplate::create($data);

        return redirect()->route('task-templates.index')
            ->with('success', 'Template tugas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskTemplate $taskTemplate)
    {
        $taskTemplate->load('tasks', 'series');
        return view('task-templates.show', compact('taskTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskTemplate $taskTemplate)
    {
        $associations = TaskTemplate::getAssociations();
        return view('task-templates.edit', compact('taskTemplate', 'associations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskTemplate $taskTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'association' => 'required|in:' . implode(',', array_keys(TaskTemplate::getAssociations())),
            'tasks_list' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $taskTemplate->update($request->all());

        return redirect()->route('task-templates.index')
            ->with('success', 'Template tugas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskTemplate $taskTemplate)
    {
        $taskTemplate->delete();

        return redirect()->route('task-templates.index')
            ->with('success', 'Template tugas berhasil dihapus.');
    }

    /**
     * Show the form for creating task series from template
     */
    public function createSeries(TaskTemplate $taskTemplate)
    {
        return view('task-templates.create-series', compact('taskTemplate'));
    }

    /**
     * Store task series from template
     */
    public function storeSeries(Request $request, TaskTemplate $taskTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'series_tasks' => 'required|array',
        ]);

        $data = $request->all();
        $data['template_id'] = $taskTemplate->id;
        $data['is_active'] = true;

        TaskSeries::create($data);

        return redirect()->route('task-templates.show', $taskTemplate)
            ->with('success', 'Series tugas berhasil ditambahkan.');
    }

    /**
     * API: Get template by ID (for AJAX requests)
     */
    public function apiShow(TaskTemplate $taskTemplate)
    {
        return response()->json($taskTemplate);
    }
}













