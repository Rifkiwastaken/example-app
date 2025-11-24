<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryType extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'sku',
        'electronic_id',
        'unit',
        'estimated_value_per_unit',
        'estimated_kg_per_unit',
        'track_individual_lots',
        'low_stock_threshold',
        'low_stock_unit',
        'low_stock_email',
        'description',
    ];

    protected $casts = [
        'estimated_value_per_unit' => 'decimal:2',
        'estimated_kg_per_unit' => 'decimal:2',
        'track_individual_lots' => 'boolean',
        'low_stock_threshold' => 'decimal:2',
    ];

    /**
     * Get all lots for this inventory type
     */
    public function lots(): HasMany
    {
        return $this->hasMany(InventoryLot::class);
    }

    /**
     * Get all transactions for this inventory type
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Get warehouses and bins where this inventory type can be stored
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'inventory_type_warehouses')
            ->withPivot('bin_id', 'warehouse_only')
            ->withTimestamps();
    }

    /**
     * Get notes for this inventory type
     */
    public function notes(): HasMany
    {
        return $this->hasMany(InventoryNote::class);
    }

    /**
     * Get photos for this inventory type
     */
    public function photos(): HasMany
    {
        return $this->hasMany(InventoryPhoto::class);
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStockAttribute(): float
    {
        return $this->lots()->sum('current_stock');
    }

    /**
     * Get total value (in Rupiah)
     */
    public function getTotalValueAttribute(): float
    {
        if (!$this->estimated_value_per_unit) {
            return 0;
        }
        
        $totalStock = $this->total_stock;
        return $totalStock * $this->estimated_value_per_unit;
    }
}

