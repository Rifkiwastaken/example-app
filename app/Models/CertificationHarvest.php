<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationHarvest extends Model
{
    protected $table = 'certification_harvest';

    public const UPDATED_AT = null;

    protected $fillable = [
        'planting_id',
        'tgl_panen',
        'volume_kotor_panen_kg',
        'status_kebersihan_alat_panen',
        'status_kebersihan_wadah',
        'no_segel_sementara',
        'nama_pengawas_pbt',
        'lampiran_formulir',
        'created_by',
    ];

    protected $casts = [
        'tgl_panen' => 'date',
        'volume_kotor_panen_kg' => 'decimal:2',
        'status_kebersihan_alat_panen' => 'boolean',
        'status_kebersihan_wadah' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_production_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function reportTitle(): string
    {
        return 'Pengawasan Panen dan Pemeriksaan Alat';
    }

    public function reportDate()
    {
        return $this->tgl_panen ?? $this->created_at;
    }

    public function supervisorName(): string
    {
        return $this->nama_pengawas_pbt ?: '-';
    }

    public function reportStatus(): string
    {
        return $this->status_kebersihan_alat_panen && $this->status_kebersihan_wadah ? 'Bersih' : 'Perlu perbaikan';
    }
}
