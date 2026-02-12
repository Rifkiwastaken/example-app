<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryType extends Model
{
    use HasFactory;
    use HasCustomId;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_type_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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
        'responsible_person_id',
        'plant_id',
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
        return $this->hasMany(InventoryLot::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get all transactions for this inventory type
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get warehouses and bins where this inventory type can be stored
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(
            Warehouse::class,
            'inventory_type_warehouses',
            'inventory_type_id',
            'warehouse_id'
        )->withPivot('bin_id', 'warehouse_only')->withTimestamps();
    }

    /**
     * Get notes for this inventory type
     */
    public function notes(): HasMany
    {
        return $this->hasMany(InventoryNote::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get photos for this inventory type
     */
    public function photos(): HasMany
    {
        return $this->hasMany(InventoryPhoto::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get certification reports (certified seeds) linked to this inventory type
     */
    public function certificationReports(): BelongsToMany
    {
        return $this->belongsToMany(
            CertificationReport::class,
            'inventory_type_certification_reports',
            'inventory_type_id',
            'certification_report_id'
        )->withPivot('quantity')->withTimestamps();
    }

    /**
     * Get seeds (non-certified seeds) linked to this inventory type
     */
    public function seeds(): HasMany
    {
        return $this->hasMany(InventoryTypeSeed::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStockAttribute(): float
    {
        // Calculate from lots (physical stock in warehouses)
        $lotsStock = $this->lots()->sum('current_stock');
        
        // Calculate from seeds (seed records)
        $seedsStock = $this->seeds()->sum('total_seed_quantity');
        
        // Return the sum of both
        return $lotsStock + $seedsStock;
    }
    
    /**
     * Get total stock from seeds only
     */
    public function getTotalStockFromSeedsAttribute(): float
    {
        return $this->seeds()->sum('total_seed_quantity');
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

    /**
     * Get current stock from lots only (physical stock in warehouses)
     */
    public function getCurrentStockFromLotsAttribute(): float
    {
        return $this->lots->sum('current_stock');
    }

    /**
     * Get total value of current stock only (from lots)
     */
    public function getCurrentStockValueAttribute(): float
    {
        $current = $this->current_stock_from_lots;
        $perUnit = $this->estimated_value_per_unit ?? 0;
        return $current * $perUnit;
    }

    /**
     * Get total value from data stok benih (seeds) only
     */
    public function getTotalValueFromSeedsAttribute(): float
    {
        $qty = $this->total_stock_from_seeds;
        $perUnit = $this->estimated_value_per_unit ?? 0;
        return $qty * $perUnit;
    }

    /**
     * Get the responsible person (user) for this inventory type
     */
    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plant (komoditas/tanaman) for this inventory type
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Get all sale items for this inventory type
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'inventory_type_id', 'inventory_type_id');
    }
}

