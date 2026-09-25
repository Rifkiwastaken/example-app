<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationInspectionField extends Model
{
    protected $table = 'certification_inspection_field';

    public const UPDATED_AT = null;

    protected $fillable = [
        'planting_id',
        'fase_inspeksi',
        'tgl_inspeksi',
        'umur_tanaman_hst',
        'nama_pbt_pemeriksa',
        'jarak_isolasi_meter',
        'status_isolasi_waktu',
        'kesesuaian_dokumen_sumber',
        'total_tanaman_sampel',
        'total_cvl_ditemukan',
        'persentase_cvl_akhir',
        'jenis_opt_dominan',
        'tingkat_serangan_opt',
        'status_roguing',
        'status_kelulusan_fase',
        'lampiran_formulir',
        'created_by',
    ];

    protected $casts = [
        'tgl_inspeksi' => 'date',
        'status_isolasi_waktu' => 'boolean',
        'kesesuaian_dokumen_sumber' => 'boolean',
        'persentase_cvl_akhir' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public const FASE = ['Pendahuluan', 'Vegetatif', 'Berbunga', 'Masak'];

    public const TINGKAT_OPT = ['Aman', 'Ringan', 'Sedang', 'Berat'];

    public const STATUS_ROUGING = ['Belum', 'Sudah Bersih', 'Perlu Diulang'];

    public const STATUS_KELULUSAN = ['LULUS', 'TIDAK LULUS', 'REINSPEKSI'];

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
        return 'Pemeriksaan Lapangan Fase '.$this->fase_inspeksi;
    }

    public function reportDate()
    {
        return $this->tgl_inspeksi ?? $this->created_at;
    }

    public function supervisorName(): string
    {
        return $this->nama_pbt_pemeriksa ?: '-';
    }

    public function reportStatus(): string
    {
        return $this->status_kelulusan_fase;
    }
}
