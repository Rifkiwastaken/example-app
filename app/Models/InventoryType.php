<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\StockHistory;

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
        'latest_certification_report_id',
        'certification_source_count',
        'certification_stock_total',
        'certification_last_synced_at',
    ];

    protected $casts = [
        'estimated_value_per_unit' => 'decimal:2',
        'estimated_kg_per_unit' => 'decimal:2',
        'track_individual_lots' => 'boolean',
        'low_stock_threshold' => 'decimal:2',
        'certification_stock_total' => 'decimal:2',
        'certification_last_synced_at' => 'datetime',
    ];

    /**
     * Get all lots for this inventory type
     */
    public function lots(): HasMany
    {
        return $this->hasMany(InventoryLot::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get stock history records that are transaction-style (stok masuk, distribusi, pengurangan, etc.)
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'inventory_type_id', 'inventory_type_id')->whereNotNull('transaction_type');
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
     * Laporan sertifikasi (benih bersertifikat) yang dihubungkan ke stok benih ini.
     * Satu laporan hanya ke satu stok (FK di certification_reports.inventory_type_id).
     */
    public function certificationReports(): HasMany
    {
        return $this->hasMany(CertificationReport::class, 'inventory_type_id', 'inventory_type_id');
    }

    public function latestCertificationReport(): BelongsTo
    {
        return $this->belongsTo(
            CertificationReport::class,
            'latest_certification_report_id',
            'certification_report_id'
        );
    }

    /**
     * Compat relation: data "seeds" kini bersumber dari certification_reports.
     */
    public function seeds(): HasMany
    {
        return $this->hasMany(CertificationReport::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStockAttribute(): float
    {
        // Stok lot di gudang: hanya lot operasional (belum habis & belum lewat kadaluarsa)
        $lotsStock = (float) $this->lots()->activeForOperationalStock()->sum('current_stock');
        
        // Calculate from certification-based records
        $seedsStock = $this->seeds()->sum('certified_seed_quantity');
        
        // Return the sum of both
        return $lotsStock + $seedsStock;
    }
    
    /**
     * Get total stock from seeds only
     */
    public function getTotalStockFromSeedsAttribute(): float
    {
        return (float) $this->seeds()->sum('certified_seed_quantity');
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
        return (float) $this->lots->filter(fn ($lot) => $lot->isActiveForOperationalStock())->sum('current_stock');
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
        return $this->belongsTo(Plant::class, 'plant_id', 'seed_varieties_id');
    }

    /**
     * Compat attribute: plant_id diturunkan dari laporan sertifikasi terbaru.
     */
    public function getPlantIdAttribute(): ?string
    {
        $report = $this->certificationReports()
            ->with('harvest')
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->first();

        return $report?->harvest?->plant_id;
    }

    /**
     * Get all sale items for this inventory type
     */
    public function saleItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            SaleItem::class,
            InventoryLot::class,
            'inventory_type_id',
            'warehouse_lot_id',
            'inventory_type_id',
            'warehouse_lot_id'
        );
    }
}

