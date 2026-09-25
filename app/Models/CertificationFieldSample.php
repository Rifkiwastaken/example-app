<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationFieldSample extends Model
{
    protected $table = 'certification_field_sample';

    public const UPDATED_AT = null;

    protected $fillable = [
        'planting_id',
        'nomor_titik_sampel',
        'jumlah_tanaman_diperiksa',
        'jumlah_cvl_ditemukan',
        'lampiran_formulir',
        'created_by',
    ];

    protected $casts = [
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

    public function persentaseCvl(): float
    {
        if (! $this->jumlah_tanaman_diperiksa) {
            return 0.0;
        }

        return round($this->jumlah_cvl_ditemukan / $this->jumlah_tanaman_diperiksa * 100, 2);
    }

    public function reportTitle(): string
    {
        return 'Detail Titik Sampel ke-'.$this->nomor_titik_sampel;
    }

    public function reportDate()
    {
        return $this->created_at;
    }

    public function supervisorName(): string
    {
        return $this->creator?->name ?: '-';
    }

    public function reportStatus(): string
    {
        return $this->persentaseCvl().'% CVL';
    }
}
