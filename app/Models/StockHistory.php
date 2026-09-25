<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat stok benih (gabungan dari inventory_transactions + seed_histories).
 *
 * Semua log pengurangan dan distribusi stok tercatat di sini:
 * - Stok benih ditambahkan ke bin gudang (stok_masuk, penyesuaian_tambah)
 * - Stok benih dihapus dari stok (stok_keluar, penyesuaian_kurang, delete, reduce_stock)
 * - Stok benih dijual (distribusi)
 *
 * Setelah setiap aksi, data stok benih di-update di bin (warehouse_bins) dan di data stok benih (inventory_lots / inventory_type_seeds).
 */
class StockHistory extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'stock_history_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'stock_histories';

    protected $fillable = [
        'inventory_type_id',
        'warehouse_lot_id',
        'stock_id',
        'stock_packaging_id',
        'seed_varieties_id',
        'transaction_type',
        'quantity',
        'unit',
        'reason',
        'notes',
        'action',
        'description',
        'old_data',
        'new_data',
        'user_id',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'quantity' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $history): void {
            if (empty($history->seed_varieties_id) && ! empty($history->stock_id)) {
                $history->seed_varieties_id = Stock::where('id', $history->stock_id)->value('seed_varieties_id');
            }

            if (empty($history->stock_id) && ! empty($history->stock_packaging_id)) {
                $history->stock_id = StockPackaging::where('id', $history->stock_packaging_id)->value('stok_benih_id');
            }
        });
    }

    /**
     * Varietas benih (menggantikan tipe inventaris lama).
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(StockPackaging::class, 'stock_packaging_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Compat attribute: gudang diturunkan dari rak kemasan / rak lot induk.
     */
    public function getWarehouseAttribute()
    {
        return $this->packaging?->rack?->warehouse ?? $this->stock?->rack?->warehouse;
    }

    /**
     * Compat attribute: rak gudang.
     */
    public function getBinAttribute()
    {
        return $this->packaging?->rack ?? $this->stock?->rack;
    }

    /**
     * Jumlah untuk tampilan ledger lot: penjualan/pengurangan/hapus sebagai nilai negatif jika di DB tersimpan positif (data lama).
     */
    public function signedQuantityForLotLedger(): float
    {
        $type = (string) ($this->transaction_type ?? $this->action ?? '');
        $q = (float) $this->quantity;
        $outTypes = ['distribusi', 'pengurangan', 'penghapusan', 'penyesuaian_kurang', 'stok_keluar', 'reduce_stock', 'delete'];

        if (in_array($type, $outTypes, true)) {
            return $q > 0 ? -abs($q) : $q;
        }

        return $q;
    }

    public function lotStockBefore(): ?float
    {
        $v = data_get($this->old_data, 'current_stock');

        return $v !== null ? (float) $v : null;
    }

    public function lotStockAfter(): ?float
    {
        $v = data_get($this->new_data, 'current_stock');

        return $v !== null ? (float) $v : null;
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        $type = $this->transaction_type ?? $this->action ?? '';
        return match($type) {
            'mendaftarkan_stok' => 'Mendaftarkan Stok',
            'stok_masuk' => 'Stok Masuk',
            'stok_keluar' => 'Stok Keluar',
            'penyesuaian_tambah' => 'Penyesuaian (+)',
            'penyesuaian_kurang' => 'Penyesuaian (-)',
            'distribusi' => 'Distribusi',
            'pindah_lokasi' => 'Pindahkan Stok',
            'pengurangan' => 'Pengurangan',
            'penghapusan' => 'Penghapusan',
            'create' => 'Tambah',
            'update' => 'Update',
            'delete' => 'Hapus',
            'reduce_stock' => 'Kurangi Stok',
            default => $type ?: '-',
        };
    }
}
