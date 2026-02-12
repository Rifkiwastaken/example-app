<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantPhotoController extends Controller
{
    public function index(Plant $plant)
    {
        $photos = $plant->photos()->orderBy('taken_at', 'desc')->paginate(15);
        return view('planting/plants/photos/index', compact('plant', 'photos'));
    }

    public function create(Plant $plant)
    {
        return view('planting/plants/photos/create', compact('plant'));
    }

    public function store(Request $request, Plant $plant)
    {
        $data = $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|max:10240', // 10MB max per photo
            'description' => 'nullable|string',
            'taken_at' => 'nullable|date',
        ]);

        foreach ($request->file('photos') as $photo) {
            $filePath = $photo->store('plant-photos', 'public');

            PlantPhoto::create([
                'plant_id' => $plant->plant_id,
                'file_path' => $filePath,
                'file_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'description' => $data['description'],
                'taken_at' => $data['taken_at'] ?: now(),
            ]);
        }
        
        return redirect()->route('plants.photos.index', $plant)
            ->with('success', 'Foto berhasil diupload');
    }

    public function show(Plant $plant, PlantPhoto $photo)
    {
        return view('planting/plants/photos/show', compact('plant', 'photo'));
    }

    public function edit(Plant $plant, PlantPhoto $photo)
    {
        // Prevent penangkar from editing photos
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit foto.');
        }
        
        return view('planting/plants/photos/edit', compact('plant', 'photo'));
    }

    public function update(Request $request, Plant $plant, PlantPhoto $photo)
    {
        // Prevent penangkar from updating photos
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit foto.');
        }
        
        $data = $request->validate([
            'description' => 'nullable|string',
            'taken_at' => 'nullable|date',
        ]);

        $photo->update($data);
        
        return redirect()->route('plants.photos.index', $plant)
            ->with('success', 'Foto berhasil diperbarui');
    }

    public function destroy(Plant $plant, PlantPhoto $photo)
    {
        // Prevent penangkar from deleting photos
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus foto.');
        }
        
        // Delete file from storage
        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }
        
        $photo->delete();
        
        return redirect()->route('plants.photos.index', $plant)
            ->with('success', 'Foto berhasil dihapus');
    }
}
