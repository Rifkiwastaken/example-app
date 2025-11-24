<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::withCount('users')->paginate(10);
        
        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = Location::getTypes();
        return view('locations.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Location::getTypes())),
            'description' => 'nullable|string',
            'google_maps_link' => 'nullable|url',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('locations', 'public');
        }

        Location::create($data);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        $location->load('users');
        return view('locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        $types = Location::getTypes();
        return view('locations.edit', compact('location', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Location::getTypes())),
            'description' => 'nullable|string',
            'google_maps_link' => 'nullable|url',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($location->photo) {
                Storage::disk('public')->delete($location->photo);
            }
            $data['photo'] = $request->file('photo')->store('locations', 'public');
        }

        $location->update($data);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        // Delete photo if exists
        if ($location->photo) {
            Storage::disk('public')->delete($location->photo);
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}















