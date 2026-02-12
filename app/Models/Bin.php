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

    protected $primaryKey = 'bin_id';
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
        return $this->hasMany(InventoryLot::class, 'bin_id', 'bin_id');
    }

    /**
     * Get current stock in this bin
     */
    public function getCurrentStockAttribute(): float
    {
        return $this->inventoryLots()->sum('current_stock');
    }
}

