<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'district',
        'type',
        'description',
        'google_maps_link',
        'photo',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the users assigned to this location
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the tasks for this location
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the type label in Indonesian
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'lokasi_lahan' => 'Lokasi Lahan',
            'lokasi_sertifikasi' => 'Lokasi Sertifikasi',
            'lokasi_gudang' => 'Lokasi Gudang',
            'lokasi_kantor_utama' => 'Lokasi Kantor Utama',
            default => $this->type,
        };
    }

    /**
     * Get all available location types
     */
    public static function getTypes(): array
    {
        return [
            'lokasi_lahan' => 'Lokasi Lahan',
            'lokasi_sertifikasi' => 'Lokasi Sertifikasi',
            'lokasi_gudang' => 'Lokasi Gudang',
            'lokasi_kantor_utama' => 'Lokasi Kantor Utama',
        ];
    }
}















