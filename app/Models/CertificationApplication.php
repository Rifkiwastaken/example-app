<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationApplication extends Model
{
    protected $table = 'certification_application';

    public const UPDATED_AT = null;

    protected $fillable = [
        'planting_id',
        'komoditas',
        'nama_varietas',
        'kelas_benih_tujuan',
        'luas_lahan_ha',
        'koordinat_gps_lahan',
        'file_peta_sketsa',
        'no_label_benih_sumber',
        'kelas_benih_sumber',
        'produsen_asal_benih_sumber',
        'jumlah_benih_sumber_kg',
        'rencana_tgl_sebar',
        'rencana_tgl_tanam',
        'sejarah_lahan_sebelumnya',
        'status_pengajuan',
        'lampiran_formulir',
        'created_by',
    ];

    protected $casts = [
        'luas_lahan_ha' => 'decimal:2',
        'jumlah_benih_sumber_kg' => 'decimal:2',
        'rencana_tgl_sebar' => 'date',
        'rencana_tgl_tanam' => 'date',
        'created_at' => 'datetime',
    ];

    public const KOMODITAS = ['Pangan', 'Hortikultura Sayur', 'Hortikultura Hias', 'Perkebunan'];

    public const STATUS_PENGAJUAN = ['Draft', 'Ditinjau', 'Disetujui', 'Ditolak'];

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
        return 'Pengajuan Permohonan Sertifikasi';
    }

    public function reportDate()
    {
        return $this->rencana_tgl_tanam ?? $this->created_at;
    }

    public function supervisorName(): string
    {
        return $this->produsen_asal_benih_sumber ?: '-';
    }

    public function reportStatus(): string
    {
        return $this->status_pengajuan;
    }
}
