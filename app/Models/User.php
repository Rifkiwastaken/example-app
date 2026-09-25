<?php

namespace App\Models;

use App\Traits\HasCustomId;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasCustomId;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'location_id',
        'location_placement',
        'placement_location_id',
        'photo_path',
        'full_name',
        'status',
        'contact_type',
        'organization',
        'position',
        'nip',
        'primary_phone',
        'primary_phone_is_whatsapp',
        'secondary_phone',
        'address',
        'province',
        'city',
        'district',
        'village',
        'notes',
        'password_encrypted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'password_encrypted',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the role label in Indonesian
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Admin/Kepala Seksi',
            'kepala_satuan_tugas' => 'Kepala Satuan Tugas/Manajemen Penanaman',
            'petugas_sertifikasi' => 'Petugas Sertifikasi',
            'petugas_gudang' => 'Petugas Gudang',
            'petugas_bbi' => 'Petugas BBI',
            'penangkar' => 'Penangkar',
            default => $this->role,
        };
    }

    /**
     * Get all available user roles
     */
    public static function getRoles(): array
    {
        return [
            'admin' => 'Admin/Kepala Seksi',
            'kepala_satuan_tugas' => 'Kepala Satuan Tugas/Manajemen Penanaman',
            'petugas_sertifikasi' => 'Petugas Sertifikasi',
            'petugas_gudang' => 'Petugas Gudang',
            'petugas_bbi' => 'Petugas BBI',
            'penangkar' => 'Penangkar',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function rememberRevealablePassword(?string $plain): void
    {
        $plain = trim((string) $plain);
        $this->password_encrypted = $plain !== ''
            ? Crypt::encryptString($plain)
            : null;
    }

    public function revealablePassword(): ?string
    {
        if (! filled($this->password_encrypted)) {
            return null;
        }

        try {
            $plain = Crypt::decryptString($this->password_encrypted);

            return filled($plain) ? $plain : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Check if user has access to specific module
     */
    public function hasAccessTo(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return match($module) {
            'penanaman' => $this->role === 'kepala_satuan_tugas' || $this->role === 'penangkar',
            'sertifikasi' => $this->role === 'petugas_sertifikasi',
            'gudang' => $this->role === 'petugas_gudang',
            'penjualan' => $this->role === 'petugas_bbi',
            'pelayanan_publik' => $this->role === 'petugas_bbi' || $this->role === 'kepala_satuan_tugas',
            default => false,
        };
    }

    /**
     * Lokasi penanaman tempat user ditugaskan (disimpan saat admin membuat akun).
     */
    public function placementLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'placement_location_id', 'planting_location_id');
    }

    /**
     * Lokasi penanaman yang ditugaskan sebagai pengelola.
     */
    public function managedPlantingLocations()
    {
        if ($this->isAdmin()) {
            return PlantingLocation::query();
        }

        if (in_array($this->role, ['kepala_satuan_tugas', 'penangkar'], true) && $this->placement_location_id) {
            return PlantingLocation::where('planting_location_id', $this->placement_location_id);
        }

        return PlantingLocation::query()->whereRaw('1 = 0');
    }

    /**
     * Get planting locations where user is assigned as worker
     */
    public function workedPlantingLocations()
    {
        return $this->managedPlantingLocations();
    }

    /**
     * Get all planting locations assigned to this user
     */
    public function assignedPlantingLocations()
    {
        return $this->managedPlantingLocations()->get();
    }

    /**
     * Check if user is assigned to a planting location.
     */
    public function isAssignedToPlantingLocation(PlantingLocation $plantingLocation): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->placement_location_id === $plantingLocation->getKey();
    }

    /**
     * Check if user can edit/delete planting location
     */
    public function canManagePlantingLocation(PlantingLocation $plantingLocation): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Only kepala_satuan_tugas can manage (edit/delete)
        if ($this->role !== 'kepala_satuan_tugas') {
            return false;
        }

        return $this->isAssignedToPlantingLocation($plantingLocation);
    }

    /**
     * Check if user can add data in pelaporan tab
     */
    public function canAddDataInPelaporan(PlantingLocation $plantingLocation): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Both kepala_satuan_tugas and penangkar can add data in pelaporan
        if (!in_array($this->role, ['kepala_satuan_tugas', 'penangkar'])) {
            return false;
        }

        return $this->isAssignedToPlantingLocation($plantingLocation);
    }

    /**
     * Check if user can edit/delete data in pelaporan tab
     */
    public function canManageDataInPelaporan(PlantingLocation $plantingLocation): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Only kepala_satuan_tugas can edit/delete in pelaporan
        if ($this->role !== 'kepala_satuan_tugas') {
            return false;
        }

        return $this->isAssignedToPlantingLocation($plantingLocation);
    }
}
