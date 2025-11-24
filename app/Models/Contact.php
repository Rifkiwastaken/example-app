<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'photo_path',
        'status',
        'contact_type',
        'organization',
        'position',
        'nip',
        'primary_phone',
        'primary_phone_is_whatsapp',
        'secondary_phone',
        'email',
        'address',
        'province',
        'city',
        'district',
        'village',
        'notes',
    ];

    protected $casts = [
        'primary_phone_is_whatsapp' => 'bool',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_INACTIVE => 'Non-Aktif',
    ];

    public const CONTACT_TYPES = [
        'pegawai_uptd_bbi_tpph' => 'Pegawai UPTD BBI TPPH',
        'pegawai_gudang' => 'Pegawai Gudang',
        'petugas_sertifikasi' => 'Petugas Sertifikasi',
        'petani' => 'Petani / Penanggung Jawab Lahan',
        'penyuluh' => 'Penyuluh',
        'lainnya' => 'Lainnya',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getContactTypeLabelAttribute(): string
    {
        return self::CONTACT_TYPES[$this->contact_type] ?? ucfirst($this->contact_type);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (Str::startsWith($this->photo_path, ['http://', 'https://'])) {
            return $this->photo_path;
        }

        if (Storage::disk('public')->exists($this->photo_path)) {
            return Storage::disk('public')->url($this->photo_path);
        }

        return asset('storage/' . ltrim($this->photo_path, '/'));
    }
}


