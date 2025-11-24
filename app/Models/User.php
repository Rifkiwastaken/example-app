<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'location_id',
        'location_placement',
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
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
     * Get the location assigned to this user
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the tasks assigned to this user
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

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
            default => false,
        };
    }
}
