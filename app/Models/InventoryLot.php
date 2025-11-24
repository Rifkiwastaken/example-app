<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InventoryLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_type_id',
        'production_id',
        'expiry_date',
        'status',
        'initial_stock',
        'current_stock',
        'stock_unit',
        'warehouse_id',
        'bin_id',
        'certification_id',
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
        return $this->belongsTo(InventoryType::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the bin
     */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    /**
     * Get the certification
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    /**
     * Get transactions for this lot
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
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
        } elseif ($daysUntilExpiry <= 30) {
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

