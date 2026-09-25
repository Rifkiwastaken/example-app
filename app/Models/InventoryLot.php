<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Models\StockHistory;

class InventoryLot extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $table = 'warehouse_lots';

    protected $primaryKey = 'warehouse_lot_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'inventory_type_id',
        'production_id',
        'expiry_date',
        'status',
        'initial_stock',
        'current_stock',
        'stock_unit',
        'warehouse_bin_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'initial_stock' => 'decimal:2',
        'current_stock' => 'decimal:2',
    ];

    /**
     * Get the inventory type
     */
    public function inventoryType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): HasOneThrough
    {
        return $this->hasOneThrough(
            Warehouse::class,
            Bin::class,
            'warehouse_bin_id',
            'warehouse_id',
            'warehouse_bin_id',
            'warehouse_id'
        );
    }

    /**
     * Get the bin
     */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'warehouse_bin_id', 'warehouse_bin_id');
    }

    /**
     * Compat attribute: warehouse_id diturunkan dari relasi bin.
     */
    public function getWarehouseIdAttribute(): ?string
    {
        return $this->bin?->warehouse_id;
    }

    /**
     * Get stock history records (transaction-style) for this lot
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'warehouse_lot_id', 'warehouse_lot_id')->whereNotNull('transaction_type');
    }

    /**
     * Stok dihitung untuk agregat gudang, ringkasan stok benih, dan FIFO penjualan:
     * kuantitas > 0 dan tanggal kadaluarsa belum lewat (hari ini masih dianggap aktif).
     */
    public function isActiveForOperationalStock(): bool
    {
        if ((float) $this->current_stock <= 0) {
            return false;
        }
        if ($this->expiry_date === null) {
            return true;
        }

        return $this->expiry_date->copy()->startOfDay()->gte(Carbon::today());
    }

    /**
     * @param  Builder<InventoryLot>  $query
     * @return Builder<InventoryLot>
     */
    public function scopeActiveForOperationalStock(Builder $query): Builder
    {
        return $query
            ->where($query->qualifyColumn('current_stock'), '>', 0)
            ->where(function (Builder $q) use ($query) {
                $col = $query->qualifyColumn('expiry_date');
                $q->whereNull($col)
                    ->orWhereDate($col, '>=', Carbon::today()->toDateString());
            });
    }

    /**
     * Update status based on expiry date
     */
    public function updateStatus(): void
    {
        if (!$this->expiry_date) {
            $this->status = 'tersedia';
            return;
        }

        $today = Carbon::today();
        $expiryDate = Carbon::parse($this->expiry_date);
        $daysUntilExpiry = $today->diffInDays($expiryDate, false);

        if ($daysUntilExpiry < 0) {
            $this->status = 'kadaluarsa';
        } elseif ($daysUntilExpiry <= 30) { // ~1 bulan sebelum kadaluarsa
            $this->status = 'segera_kadaluarsa';
        } else {
            $this->status = 'tersedia';
        }

        if ($this->current_stock <= 0) {
            $this->status = 'habis';
        }

        $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'tersedia' => 'Tersedia',
            'segera_kadaluarsa' => 'Segera Kadaluarsa',
            'kadaluarsa' => 'KADALUARSA',
            'habis' => 'Habis',
            default => $this->status,
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'tersedia' => 'success',
            'segera_kadaluarsa' => 'warning',
            'kadaluarsa' => 'danger',
            'habis' => 'secondary',
            default => 'secondary',
        };
    }
}

