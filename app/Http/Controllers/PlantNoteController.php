<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantNoteController extends Controller
{
    public function index(Plant $plant)
    {
        $notes = $plant->notes()->orderBy('note_date', 'desc')->paginate(15);
        return view('planting/plants/notes/index', compact('plant', 'notes'));
    }

    public function create(Plant $plant)
    {
        return view('planting/plants/notes/create', compact('plant'));
    }

    public function store(Request $request, Plant $plant)
    {
        $data = $request->validate([
            'description' => 'required|string',
            'note_date' => 'required|date',
            'keywords' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $data['plant_id'] = $plant->plant_id;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('plant-notes', 'public');
        }

        PlantNote::create($data);
        
        return redirect()->route('plants.notes.index', $plant)
            ->with('success', 'Catatan berhasil ditambahkan');
    }

    public function show(Plant $plant, PlantNote $note)
    {
        return view('planting/plants/notes/show', compact('plant', 'note'));
    }

    public function edit(Plant $plant, PlantNote $note)
    {
        // Prevent penangkar from editing notes
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit catatan.');
        }
        
        return view('planting/plants/notes/edit', compact('plant', 'note'));
    }

    public function update(Request $request, Plant $plant, PlantNote $note)
    {
        // Prevent penangkar from updating notes
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit catatan.');
        }
        
        $data = $request->validate([
            'description' => 'required|string',
            'note_date' => 'required|date',
            'keywords' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('plant-notes', 'public');
        }

        $note->update($data);
        
        return redirect()->route('plants.notes.index', $plant)
            ->with('success', 'Catatan berhasil diperbarui');
    }

    public function destroy(Plant $plant, PlantNote $note)
    {
        // Prevent penangkar from deleting notes
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus catatan.');
        }
        
        // Delete attachment file if exists
        if ($note->attachment_path && Storage::disk('public')->exists($note->attachment_path)) {
            Storage::disk('public')->delete($note->attachment_path);
        }
        
        $note->delete();
        
        return redirect()->route('plants.notes.index', $plant)
            ->with('success', 'Catatan berhasil dihapus');
    }
}
