<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PlantingLocation;
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
        $users = User::orderBy('name')->paginate(10);
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $stats = [
            'admin' => (int) ($roleCounts['admin'] ?? 0),
            'kepala_satuan_tugas' => (int) ($roleCounts['kepala_satuan_tugas'] ?? 0),
            'petugas' => (int) (
                ($roleCounts['petugas_sertifikasi'] ?? 0)
                + ($roleCounts['petugas_gudang'] ?? 0)
                + ($roleCounts['petugas_bbi'] ?? 0)
            ),
            'total' => (int) $roleCounts->sum(),
            'penanaman' => (int) (($roleCounts['kepala_satuan_tugas'] ?? 0) + ($roleCounts['penangkar'] ?? 0)),
            'sertifikasi' => (int) ($roleCounts['petugas_sertifikasi'] ?? 0),
            'gudang' => (int) ($roleCounts['petugas_gudang'] ?? 0),
            'penjualan' => (int) ($roleCounts['petugas_bbi'] ?? 0),
        ];

        return view('users.index', compact('users', 'stats'));
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
        
        $plantingLocations = PlantingLocation::orderBy('name')->get();
        
        return view('users.create', compact('roles', 'statuses', 'contactTypes', 'plantingLocations'));
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
            'placement_location_id' => 'nullable|exists:planting_locations,planting_location_id',
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
        unset($data['photo'], $data['password_confirmation'], $data['password_encrypted']);
        $placement = $this->resolvePlacement($request);
        $data['placement_location_id'] = $placement['placement_location_id'];
        $data['location_placement'] = $placement['location_placement'];

        $user = User::create($data);
        $user->rememberRevealablePassword($request->password);
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $plainPassword = auth()->user()?->isAdmin() ? $user->revealablePassword() : null;

        return view('users.show', compact('user', 'plainPassword'));
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
        
        $plantingLocations = PlantingLocation::orderBy('name')->get();
        
        return view('users.edit', compact('user', 'roles', 'statuses', 'contactTypes', 'plantingLocations'));
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
            'placement_location_id' => 'nullable|exists:planting_locations,planting_location_id',
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
            $data['password'] = $request->password;
            $user->rememberRevealablePassword($request->password);
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
        unset($data['photo'], $data['password_confirmation'], $data['password_encrypted']);
        $placement = $this->resolvePlacement($request);
        $data['placement_location_id'] = $placement['placement_location_id'];
        $data['location_placement'] = $placement['location_placement'];

        $user->fill($data);
        $user->save();

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

    protected function resolvePlacement(Request $request): array
    {
        $role = (string) $request->input('role');
        $uptLabel = 'UPTD BBI TPHP';

        if (in_array($role, ['petugas_gudang', 'petugas_bbi'], true)) {
            return [
                'placement_location_id' => null,
                'location_placement' => $uptLabel,
            ];
        }

        if (in_array($role, ['kepala_satuan_tugas', 'penangkar'], true)) {
            $location = $request->filled('placement_location_id')
                ? PlantingLocation::find($request->placement_location_id)
                : null;

            return [
                'placement_location_id' => $location?->getKey(),
                'location_placement' => $location?->name,
            ];
        }

        $location = $request->filled('placement_location_id')
            ? PlantingLocation::find($request->placement_location_id)
            : null;

        return [
            'placement_location_id' => $location?->getKey(),
            'location_placement' => $location?->name ?: $request->input('location_placement'),
        ];
    }
}















