<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(10);
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only admin can create users
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menambahkan akun.');
        }
        
        $roles = User::getRoles();
        // Hapus petugas_sertifikasi dari daftar roles
        unset($roles['petugas_sertifikasi']);
        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
        ];
        $contactTypes = [
            'pegawai_uptd_bbi_tpph' => 'Pegawai UPTD BBI TPPH',
            'pegawai_gudang' => 'Pegawai Gudang',
            'petugas_sertifikasi' => 'Petugas Sertifikasi',
            'petani' => 'Petani',
            'penyuluh' => 'Penyuluh',
            'penangkar' => 'Penangkar',
            'lainnya' => 'Lainnya',
        ];
        
        return view('users.create', compact('roles', 'statuses', 'contactTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only admin can store users
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menambahkan akun.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', array_keys(User::getRoles())),
            'location_placement' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'full_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'contact_type' => 'nullable|string',
            'organization' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:255',
            'primary_phone' => 'nullable|string|max:255',
            'primary_phone_is_whatsapp' => 'nullable|boolean',
            'secondary_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        
        // Set status default to 'active' if not provided
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'active';
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->storeAs('photos/users', $photoName, 'public');
            $data['photo_path'] = $photoPath;
        }
        
        // Handle checkbox
        $data['primary_phone_is_whatsapp'] = $request->has('primary_phone_is_whatsapp') ? 1 : 0;
        
        // Remove photo from data array (already handled)
        unset($data['photo']);

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Only admin can edit users
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengedit akun.');
        }
        
        $roles = User::getRoles();
        // Hapus petugas_sertifikasi dari daftar roles
        unset($roles['petugas_sertifikasi']);
        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
        ];
        $contactTypes = [
            'pegawai_uptd_bbi_tpph' => 'Pegawai UPTD BBI TPPH',
            'pegawai_gudang' => 'Pegawai Gudang',
            'petugas_sertifikasi' => 'Petugas Sertifikasi',
            'petani' => 'Petani',
            'penyuluh' => 'Penyuluh',
            'penangkar' => 'Penangkar',
            'lainnya' => 'Lainnya',
        ];
        
        return view('users.edit', compact('user', 'roles', 'statuses', 'contactTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Only admin can update users
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat memperbarui akun.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', array_keys(User::getRoles())),
            'location_placement' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'full_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'contact_type' => 'nullable|string',
            'organization' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:255',
            'primary_phone' => 'nullable|string|max:255',
            'primary_phone_is_whatsapp' => 'nullable|boolean',
            'secondary_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo_path && \Storage::disk('public')->exists($user->photo_path)) {
                \Storage::disk('public')->delete($user->photo_path);
            }
            
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->storeAs('photos/users', $photoName, 'public');
            $data['photo_path'] = $photoPath;
        }
        
        // Handle checkbox
        $data['primary_phone_is_whatsapp'] = $request->has('primary_phone_is_whatsapp') ? 1 : 0;
        
        // Remove photo from data array (already handled)
        unset($data['photo']);
        unset($data['password_confirmation']);

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Only admin can delete users
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menghapus akun.');
        }
        
        // Prevent admin from deleting themselves
        if ($user->user_id === auth()->user()->user_id) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}















