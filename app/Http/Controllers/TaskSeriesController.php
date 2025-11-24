<?php

namespace App\Http\Controllers;

use App\Models\TaskSeries;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;

class TaskSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $series = TaskSeries::with('template')->withCount('tasks')->paginate(10);
        
        return view('task-series.index', compact('series'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $templates = TaskTemplate::active()->get();
        return view('task-series.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:task_templates,id',
            'series_tasks' => 'required|array',
        ]);

        $data = $request->all();
        $data['is_active'] = true;

        TaskSeries::create($data);

        return redirect()->route('task-series.index')
            ->with('success', 'Series tugas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskSeries $taskSeries)
    {
        $taskSeries->load('template', 'tasks');
        return view('task-series.show', compact('taskSeries'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskSeries $taskSeries)
    {
        $templates = TaskTemplate::active()->get();
        return view('task-series.edit', compact('taskSeries', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskSeries $taskSeries)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:task_templates,id',
            'series_tasks' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $taskSeries->update($request->all());

        return redirect()->route('task-series.index')
            ->with('success', 'Series tugas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskSeries $taskSeries)
    {
        $taskSeries->delete();

        return redirect()->route('task-series.index')
            ->with('success', 'Series tugas berhasil dihapus.');
    }
}















