<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StockPackaging extends Model
{
    protected $table = 'stock_packaging';

    protected $fillable = [
        'stok_benih_id',
        'certification_report_id',
        'rak_gudang_id',
        'no_label_seri',
        'jenis_kemasan',
        'kapasitas_per_kemasan',
        'status_kemasan',
        'hold_for_recert',
        'hold_for_request',
        'hold_until',
        'seed_request_id',
        'qr_code_token',
        'alasan_penyesuaian',
        'tgl_penyesuaian',
    ];

    protected $casts = [
        'kapasitas_per_kemasan' => 'decimal:2',
        'hold_for_recert' => 'boolean',
        'hold_for_request' => 'boolean',
        'hold_until' => 'datetime',
        'tgl_penyesuaian' => 'datetime',
    ];

    public const ALASAN_PENYESUAIAN = [
        'rusak' => 'Kemasan rusak',
        'susut' => 'Susut penyimpanan',
        'kadaluarsa' => 'Melewati masa edar',
        'hilang' => 'Hilang / tidak ditemukan',
        'lainnya' => 'Lainnya',
    ];

    public const JENIS = [
        'karung_bulk' => 'Karung Besar / Bulk',
        'plastik_ritel' => 'Plastik Bag Kecil / Ritel',
        'pot_satuan' => 'Pot / Satuan',
    ];

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_DISALURKAN = 'sudah_disalurkan';
    public const STATUS_AFKIR = 'afkir_rusak';
    public const STATUS_TIDAK_AKTIF = 'tidak_aktif';

    public static function generateSerial(string $prefix, int $index): string
    {
        $serial = $prefix.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        while (static::where('no_label_seri', $serial)->exists()) {
            $index++;
            $serial = $prefix.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        }

        return $serial;
    }

    public static function generateToken(): string
    {
        do {
            $token = 'PKG-'.Str::upper(Str::random(16));
        } while (static::where('qr_code_token', $token)->exists());

        return $token;
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stok_benih_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'rak_gudang_id', 'warehouse_bin_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(CertificationReport::class, 'certification_report_id', 'certification_report_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'stock_packaging_id');
    }

    public function soldSale(): ?SaleItem
    {
        $sale = $this->relationLoaded('sales')
            ? $this->sales->sortByDesc('sale_date')->first()
            : $this->sales()->orderByDesc('sale_date')->first();

        if ($sale) {
            return $sale;
        }

        $label = trim((string) $this->no_label_seri);
        if ($label === '') {
            return null;
        }

        $history = StockHistory::query()
            ->where('notes', 'like', 'Kemasan '.$label.' %')
            ->where(function ($q) {
                $q->where('reason', 'like', 'Penjualan %')
                    ->orWhere('reason', 'like', 'Penyaluran permintaan %');
            })
            ->orderByDesc('created_at')
            ->first();

        if (! $history?->reason) {
            return null;
        }

        if (preg_match('/(PJ-[A-Z0-9-]+)/', (string) $history->reason, $matches)) {
            return SaleItem::where('receipt_number', $matches[1])->first();
        }

        if (preg_match('/Penyaluran permintaan\s+(\S+)/', (string) $history->reason, $matches)) {
            return SaleItem::whereHas('seedRequest', fn ($q) => $q->where('request_number', $matches[1]))
                ->orWhere('notes', 'like', '%'.$label.'%')
                ->first();
        }

        return null;
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis_kemasan] ?? $this->jenis_kemasan;
    }

    public function serialSortKey(): int
    {
        if (preg_match('/(\d+)$/', (string) $this->no_label_seri, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    public function isExpiredHeld(): bool
    {
        if (in_array($this->status_kemasan, [self::STATUS_DISALURKAN, self::STATUS_TIDAK_AKTIF, self::STATUS_AFKIR], true) || $this->hold_for_request) {
            return false;
        }

        return (bool) $this->stock?->isPastExpiry();
    }

    public function statusLabel(): string
    {
        if (in_array($this->status_kemasan, [self::STATUS_TIDAK_AKTIF, self::STATUS_AFKIR], true)) {
            return 'Tidak aktif';
        }

        if ($this->hold_for_request && $this->status_kemasan === self::STATUS_TERSEDIA) {
            return 'Ditahan';
        }

        if ($this->isExpiredHeld()) {
            return 'Ditahan';
        }

        if ($this->hold_for_recert && $this->status_kemasan === self::STATUS_TERSEDIA) {
            return 'Ditahan';
        }

        return match ($this->status_kemasan) {
            self::STATUS_TERSEDIA => 'Aktif',
            self::STATUS_DISALURKAN => 'Terjual',
            default => $this->status_kemasan,
        };
    }

    public function seedRequest(): BelongsTo
    {
        return $this->belongsTo(SeedRequest::class, 'seed_request_id', 'seed_request_id');
    }

    public function isSellable(): bool
    {
        return $this->status_kemasan === self::STATUS_TERSEDIA
            && ! $this->hold_for_recert
            && ! $this->hold_for_request
            && ! $this->stock?->hold_for_recert
            && $this->stock?->status_stok === Stock::STATUS_SIAP
            && $this->stock?->tgl_kedaluwarsa?->gte(now()->startOfDay());
    }

    public function statusBadge(): string
    {
        if ($this->hold_for_request && $this->status_kemasan === self::STATUS_TERSEDIA) {
            return 'warning';
        }

        if ($this->isExpiredHeld() || ($this->hold_for_recert && $this->status_kemasan !== self::STATUS_DISALURKAN)) {
            return 'warning';
        }

        return match ($this->status_kemasan) {
            self::STATUS_TERSEDIA => 'success',
            self::STATUS_DISALURKAN => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Urutan tampilan: aktif dulu, lalu terjual, lalu tidak aktif.
     */
    public function statusOrder(): int
    {
        if ($this->isExpiredHeld() || $this->hold_for_recert) {
            return 1;
        }

        return match ($this->status_kemasan) {
            self::STATUS_TERSEDIA => 0,
            self::STATUS_DISALURKAN => 2,
            default => 3,
        };
    }

    public static function sorted($packagings)
    {
        return $packagings->sortBy([
            fn (self $a, self $b) => $a->statusOrder() <=> $b->statusOrder(),
            fn (self $a, self $b) => $a->serialSortKey() <=> $b->serialSortKey(),
            fn (self $a, self $b) => strcmp((string) $a->no_label_seri, (string) $b->no_label_seri),
        ])->values();
    }

    public function alasanPenyesuaianLabel(): ?string
    {
        return $this->alasan_penyesuaian
            ? (self::ALASAN_PENYESUAIAN[$this->alasan_penyesuaian] ?? $this->alasan_penyesuaian)
            : null;
    }
}
