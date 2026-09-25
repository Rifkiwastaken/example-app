<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationPcb extends Model
{
    protected $table = 'certification_pcb';

    public const UPDATED_AT = null;

    protected $fillable = [
        'planting_id',
        'no_berita_acara_pcb',
        'no_segel_sampel_lab',
        'berat_sampel_kirim_gram',
        'status_posisi_lot',
        'lampiran_formulir',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public const STATUS_POSISI_LOT = ['Diolah', 'Sampel Di Lab', 'Lulus Lab', 'Gagal'];

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
        return 'Pengolahan Benih & Pengambilan Contoh Benih (PCB)';
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
        return $this->status_posisi_lot;
    }
}
