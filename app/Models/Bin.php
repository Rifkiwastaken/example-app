<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bin extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $table = 'warehouse_racks';

    protected $primaryKey = 'warehouse_bin_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'warehouse_id',
        'name',
        'internal_id',
        'max_capacity',
        'capacity_unit',
        'description',
    ];

    protected $casts = [
        'max_capacity' => 'decimal:2',
    ];

    /**
     * Get the warehouse that owns this bin
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }

    /**
     * Get inventory lots stored in this bin
     */
    public function inventoryLots(): HasMany
    {
        return $this->packagings();
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(StockPackaging::class, 'rak_gudang_id', 'warehouse_bin_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'rak_gudang_id', 'warehouse_bin_id');
    }

    /**
     * Get current stock in this bin (hanya lot aktif: stok > 0 dan belum kadaluarsa).
     */
    public function getCurrentStockAttribute(): float
    {
        return (float) $this->packagings()->where('status_kemasan', StockPackaging::STATUS_TERSEDIA)->sum('kapasitas_per_kemasan');
    }

    /**
     * Nama varietas benih yang saat ini tersimpan di blok penyimpanan ini.
     *
     * @return array<int, string>
     */
    public function storedVarieties(): array
    {
        return Stock::query()
            ->whereIn('id', $this->packagings()
                ->where('status_kemasan', StockPackaging::STATUS_TERSEDIA)
                ->select('stok_benih_id'))
            ->with('plant')
            ->get()
            ->map(fn (Stock $stock) => $stock->plant?->variety ?: $stock->plant?->name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

