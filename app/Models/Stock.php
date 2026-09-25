<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'no_label_resmi',
        'nomor_induk',
        'uji_ke',
        'qr_code_token',
        'stok_awal',
        'stok_saat_ini',
        'rak_gudang_id',
        'tgl_kedaluwarsa',
        'status_stok',
        'seed_varieties_id',
        'certification_report_id',
        'planting_post_harvest_id',
        'hold_for_recert',
        'updated_by',
    ];

    protected $casts = [
        'stok_awal' => 'decimal:2',
        'stok_saat_ini' => 'decimal:2',
        'tgl_kedaluwarsa' => 'date',
        'hold_for_recert' => 'boolean',
    ];

    /** Lot baru dari hasil uji lab, belum punya data label sertifikat. */
    public const STATUS_PELABELAN = 'pelabelan';
    public const STATUS_SIAP = 'siap_salur';
    public const STATUS_UJI_ULANG = 'butuh_uji_ulang';
    public const STATUS_AFKIR = 'afkir_total';

    public static function generateOfficialLabel(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        $label = 'LOT-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        while (static::where('no_label_resmi', $label)->exists()) {
            $count++;
            $label = 'LOT-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        return $label;
    }

    public static function generateToken(): string
    {
        do {
            $token = 'STK-'.Str::upper(Str::random(16));
        } while (static::where('qr_code_token', $token)->exists());

        return $token;
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'rak_gudang_id', 'warehouse_bin_id');
    }

    public function certificationReport(): BelongsTo
    {
        return $this->belongsTo(CertificationReport::class, 'certification_report_id', 'certification_report_id');
    }

    public function postHarvest(): BelongsTo
    {
        return $this->belongsTo(PlantingPostHarvest::class, 'planting_post_harvest_id', 'planting_post_harvest_id');
    }

    /**
     * Seluruh label sertifikat yang dicetak untuk hasil uji lab lot ini.
     */
    public function labels(): HasMany
    {
        return $this->hasMany(CertificationReport::class, 'planting_post_harvest_id', 'planting_post_harvest_id');
    }

    public function hasLabel(): bool
    {
        return $this->planting_post_harvest_id
            && CertificationReport::where('planting_post_harvest_id', $this->planting_post_harvest_id)->exists();
    }

    public function pendingLabelReport(): ?CertificationReport
    {
        $labels = $this->relationLoaded('labels')
            ? $this->labels
            : $this->labels()->with('packagings')->get();

        return $labels->first(function (CertificationReport $label) {
            $packagings = $label->relationLoaded('packagings')
                ? $label->packagings
                : $label->packagings()->get();

            return $packagings->isEmpty();
        });
    }

    public function isAwaitingLabel(): bool
    {
        if ($this->status_stok !== self::STATUS_PELABELAN && ! $this->planting_post_harvest_id) {
            return false;
        }

        return $this->status_stok === self::STATUS_PELABELAN && ! $this->hasLabel();
    }

    public function isAwaitingStorage(): bool
    {
        return $this->pendingLabelReport() !== null;
    }

    public function isAwaitingPackaging(): bool
    {
        return $this->isAwaitingLabel() || $this->isAwaitingStorage();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(StockPackaging::class, 'stok_benih_id');
    }

    public function availablePackagings(): HasMany
    {
        return $this->packagings()->where('status_kemasan', StockPackaging::STATUS_TERSEDIA);
    }

    /** Alias agar notifikasi masa edar bisa memakai nama atribut yang seragam. */
    public function getExpiryDateAttribute()
    {
        return $this->tgl_kedaluwarsa;
    }

    public function isPastExpiry(): bool
    {
        return (bool) $this->tgl_kedaluwarsa?->lt(Carbon::today());
    }

    public function displayStatus(): string
    {
        if ($this->isAwaitingStorage()) {
            return 'siap_stok';
        }
        if ($this->status_stok === self::STATUS_PELABELAN || $this->isAwaitingLabel()) {
            return 'pelabelan';
        }
        if ($this->isPastExpiry() && (float) $this->stok_saat_ini > 0) {
            return 'melewati_kadaluarsa';
        }
        if ($this->hold_for_recert || $this->status_stok === self::STATUS_UJI_ULANG) {
            return 'butuh_uji_ulang';
        }
        if ($this->status_stok === self::STATUS_AFKIR) {
            return 'melewati_kadaluarsa';
        }
        if (CertificationReport::isExpiryDateApproaching($this->tgl_kedaluwarsa)) {
            return 'mendekati_masa_edar';
        }

        return 'aktif';
    }

    public function displayStatusLabel(): string
    {
        return match ($this->displayStatus()) {
            'siap_stok' => 'Siap ditambahkan ke stok',
            'pelabelan' => 'Menunggu pelabelan',
            'butuh_uji_ulang' => 'Butuh uji ulang',
            'melewati_kadaluarsa' => 'Melewati kadaluarsa',
            'mendekati_masa_edar' => 'Mendekati masa edar',
            default => 'Aktif',
        };
    }

    public function displayStatusBadge(): string
    {
        return match ($this->displayStatus()) {
            'siap_stok' => 'warning',
            'pelabelan' => 'warning',
            'butuh_uji_ulang' => 'info',
            'melewati_kadaluarsa' => 'danger',
            'mendekati_masa_edar' => 'warning',
            default => 'success',
        };
    }

    public function activePackagingCount(): int
    {
        return $this->packagings()->where('status_kemasan', StockPackaging::STATUS_TERSEDIA)->count();
    }

    public function unpackagedQuantity(): float
    {
        $packaged = (float) $this->packagings()->whereIn('status_kemasan', [
            StockPackaging::STATUS_TERSEDIA,
            StockPackaging::STATUS_DISALURKAN,
        ])->sum('kapasitas_per_kemasan');

        return max(0, (float) $this->stok_awal - $packaged);
    }

    public function syncExpired(): void
    {
        if ($this->status_stok === self::STATUS_PELABELAN || ! $this->isPastExpiry()) {
            return;
        }

        if ($this->status_stok === self::STATUS_AFKIR && (float) $this->stok_saat_ini > 0) {
            $this->update(['status_stok' => self::STATUS_SIAP]);
        }
    }

    public function storageLocationNames(): string
    {
        $fromPacks = $this->relationLoaded('packagings')
            ? $this->packagings->map(fn ($pkg) => $pkg->rack?->warehouse?->name)->filter()->unique()->values()
            : collect();
        if ($fromPacks->isNotEmpty()) {
            return $fromPacks->implode(', ');
        }

        return $this->rack?->warehouse?->name ?: '-';
    }

    public function storagePlaceNames(): string
    {
        $fromPacks = $this->relationLoaded('packagings')
            ? $this->packagings->map(fn ($pkg) => $pkg->rack?->name)->filter()->unique()->values()
            : collect();
        if ($fromPacks->isNotEmpty()) {
            return $fromPacks->implode(', ');
        }

        return $this->rack?->name ?: '-';
    }

    public function productionReportRoute(): ?array
    {
        $planting = $this->postHarvest?->planting;
        $location = $planting?->field?->plantingLocation;
        if (! $planting || ! $location) {
            return null;
        }

        return [$location, $planting];
    }

    public static function expireAllPastDue(): void
    {
        static::query()
            ->whereNotNull('tgl_kedaluwarsa')
            ->whereDate('tgl_kedaluwarsa', '<', now()->toDateString())
            ->where('stok_saat_ini', '>', 0)
            ->get()
            ->each
            ->syncExpired();
    }
}
