<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Label rilis sertifikat benih. Satu baris mewakili satu batch label resmi
 * yang dicetak untuk hasil uji lab (planting_post_harvest) tertentu.
 */
class CertificationReport extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'certification_report_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_label_rilis',
        'planting_post_harvest_id',
        'nomor_lot',
        'no_sertifikat_bpsb_final',
        'warna_label',
        'tipe_kemasan',
        'ukuran_kemasan_retail_kg',
        'jumlah_lembar_label_dicetak',
        'no_seri_label_awal',
        'no_seri_label_akhir',
        'tgl_pemasangan_label',
        'lampiran_qr_label',
        'created_by',
    ];

    protected $casts = [
        'tgl_pemasangan_label' => 'date',
        'ukuran_kemasan_retail_kg' => 'decimal:2',
        'jumlah_lembar_label_dicetak' => 'integer',
        'created_at' => 'datetime',
    ];

    public const WARNA_LABEL = [
        'Kuning' => 'Kuning (Benih Dasar / FS)',
        'Putih' => 'Putih (Benih Penjenis / BS)',
        'Ungu' => 'Ungu (Benih Pokok / SS)',
        'Biru' => 'Biru (Benih Sebar / ES)',
    ];

    public const TIPE_KEMASAN = [
        'karung_bulk' => 'Karung Besar / Bulk',
        'plastik_ritel' => 'Plastik Kecil / Ritel',
        'pot_satuan' => 'Pot / Satuan',
    ];

    public function postHarvest(): BelongsTo
    {
        return $this->belongsTo(PlantingPostHarvest::class, 'planting_post_harvest_id', 'planting_post_harvest_id');
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(StockPackaging::class, 'certification_report_id', 'certification_report_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function getPlantingLocationAttribute(): ?PlantingLocation
    {
        return $this->postHarvest?->planting?->field?->plantingLocation;
    }

    public function getPlantAttribute(): ?Plant
    {
        return $this->postHarvest?->planting?->seedSource?->variety;
    }

    public function warnaLabel(): string
    {
        return self::WARNA_LABEL[$this->warna_label] ?? (string) $this->warna_label;
    }

    public function tipeKemasanLabel(): string
    {
        return self::TIPE_KEMASAN[$this->tipe_kemasan] ?? (string) $this->tipe_kemasan;
    }

    public function qrLabelUrl(): ?string
    {
        return $this->lampiran_qr_label ? asset('storage/'.$this->lampiran_qr_label) : null;
    }

    public static function generateLabelId(): string
    {
        $year = date('Y');
        do {
            $count = static::whereYear('created_at', $year)->count() + 1;
            $id = 'LBL-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = static::where('id_label_rilis', $id)->exists();
            if ($exists) {
                $id = 'LBL-'.$year.'-'.str_pad((string) ($count + random_int(1, 999)), 4, '0', STR_PAD_LEFT);
            }
        } while (static::where('id_label_rilis', $id)->exists());

        return $id;
    }

    /**
     * Nomor seri dicetak dengan menempelkan urutan 1..N di akhir teks yang diisi.
     * Contoh: awalan 500 dengan 10 lembar menjadi 5001 hingga 50010.
     */
    public static function buildEndSerial(string $startSerial, int $sheets): string
    {
        if ($sheets < 1) {
            return $startSerial;
        }

        return $startSerial.(string) $sheets;
    }

    /**
     * Daftar nomor seri berurutan untuk setiap lembar label.
     *
     * @return array<int, string>
     */
    public function serialList(): array
    {
        return static::buildSerials((string) $this->no_seri_label_awal, (int) $this->jumlah_lembar_label_dicetak);
    }

    /**
     * @return array<int, string>
     */
    public static function buildSerials(string $prefix, int $sheets): array
    {
        $serials = [];
        for ($i = 1; $i <= max(0, $sheets); $i++) {
            $serials[] = $prefix.(string) $i;
        }

        return $serials;
    }

    public static function isExpiryDateApproaching($expiry): bool
    {
        if ($expiry === null) {
            return false;
        }
        $exp = $expiry instanceof Carbon ? $expiry->copy()->startOfDay() : Carbon::parse($expiry)->startOfDay();
        if ($exp->lt(Carbon::today())) {
            return false;
        }

        return Carbon::today()->diffInDays($exp) <= 30;
    }
}
