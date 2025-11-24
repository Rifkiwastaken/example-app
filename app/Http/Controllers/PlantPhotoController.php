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

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $photo) {
            $filePath = $photo->store('plant-photos', 'public');
            
            $uploadedPhotos[] = [
                'plant_id' => $plant->id,
                'file_path' => $filePath,
                'file_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'description' => $data['description'],
                'taken_at' => $data['taken_at'] ?: now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        PlantPhoto::insert($uploadedPhotos);
        
        return redirect()->route('plants.photos.index', $plant)
            ->with('success', 'Foto berhasil diupload');
    }

    public function show(Plant $plant, PlantPhoto $photo)
    {
        return view('planting/plants/photos/show', compact('plant', 'photo'));
    }

    public function edit(Plant $plant, PlantPhoto $photo)
    {
        return view('planting/plants/photos/edit', compact('plant', 'photo'));
    }

    public function update(Request $request, Plant $plant, PlantPhoto $photo)
    {
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
        // Delete file from storage
        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }
        
        $photo->delete();
        
        return redirect()->route('plants.photos.index', $plant)
            ->with('success', 'Foto berhasil dihapus');
    }
}
