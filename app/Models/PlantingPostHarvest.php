<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlantingPostHarvest extends Model
{
    use HasCustomId;

    protected $table = 'planting_post_harvest';
    protected $primaryKey = 'planting_post_harvest_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'planting_id',
        'no_sertifikat_lab_bpsb',
        'uji_ke',
        'tgl_panen',
        'tgl_aju',
        'tgl_uji',
        'tgl_selesai_uji',
        'realisasi_produksi',
        'nomor_induk',
        'nomor_lot',
        'kadar_air_persen',
        'benih_murni_persen',
        'kotoran_benih_persen',
        'benih_tanaman_lain_persen',
        'daya_berkecambah_persen',
        'catatan_kesehatan_penyakit',
        'tgl_kadaluarsa_mutu',
        'total_hasil_uji',
        'status_kelulusan_lab',
        'lampiran_hasil_lab',
        'alasan_uji_ulang',
        'created_by',
    ];

    protected $casts = [
        'tgl_panen' => 'date',
        'tgl_aju' => 'date',
        'tgl_uji' => 'date',
        'tgl_selesai_uji' => 'date',
        'tgl_kadaluarsa_mutu' => 'date',
        'realisasi_produksi' => 'decimal:2',
        'total_hasil_uji' => 'decimal:2',
        'kadar_air_persen' => 'decimal:2',
        'benih_murni_persen' => 'decimal:2',
        'kotoran_benih_persen' => 'decimal:2',
        'benih_tanaman_lain_persen' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public const LULUS = 'LULUS';
    public const TIDAK_LULUS = 'TIDAK LULUS';

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_production_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function certificationReports(): HasMany
    {
        return $this->hasMany(CertificationReport::class, 'planting_post_harvest_id', 'planting_post_harvest_id');
    }

    public function certificationReport(): HasOne
    {
        return $this->hasOne(CertificationReport::class, 'planting_post_harvest_id', 'planting_post_harvest_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'planting_post_harvest_id', 'planting_post_harvest_id');
    }

    /**
     * Semua hasil uji pada nomor induk yang sama, termasuk uji ulang.
     */
    public function siblingTests()
    {
        return static::where('nomor_induk', $this->nomor_induk)->orderBy('uji_ke');
    }

    /** Alias agar notifikasi masa edar tetap memakai nama atribut yang umum. */
    public function getExpiryDateAttribute()
    {
        return $this->tgl_kadaluarsa_mutu;
    }

    public function getReportNumberBpsbAttribute(): ?string
    {
        return $this->no_sertifikat_lab_bpsb;
    }

    public function isLulus(): bool
    {
        return $this->status_kelulusan_lab === self::LULUS;
    }

    public function isCertified(): bool
    {
        if ($this->relationLoaded('certificationReports')) {
            return $this->certificationReports->isNotEmpty();
        }

        return $this->certificationReports()->exists();
    }

    public static function generateLotNumber(string $nomorInduk, int $ujiKe): string
    {
        return $nomorInduk.'/'.str_pad((string) $ujiKe, 2, '0', STR_PAD_LEFT);
    }
}
