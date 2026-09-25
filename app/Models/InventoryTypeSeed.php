<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTypeSeed extends Model
{
    use HasFactory;
    use HasCustomId;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_type_seed_id';

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

    protected $table = 'inventory_type_seeds';

    protected $fillable = [
        'inventory_type_id',
        'certification_report_id',
        'quantity',
        'seed_unit',
        'seed_unit_quantity',
        'seed_per_unit',
        'seed_per_unit_unit',
        'total_seed_quantity',
        'total_seed_unit',
        'estimated_sale_price_per_kg',
        'expiry_date',
        'filled_by_user_id',
        'storage_number',
        'report_type',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'seed_unit_quantity' => 'decimal:2',
        'seed_per_unit' => 'decimal:2',
        'total_seed_quantity' => 'decimal:2',
        'estimated_sale_price_per_kg' => 'decimal:2',
        'expiry_date' => 'date',
        'edited_at' => 'datetime',
    ];

    public function inventoryType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Compat relation (legacy). Nilai plant kini diambil dari certification_report.
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'plant_id');
    }

    /**
     * Compat relation (legacy). Nilai lokasi kini diambil dari certification_report/harvest.
     */
    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function filledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }

    public function histories()
    {
        // Kolom inventory_type_seed_id sudah dihapus dari stock_histories.
        // Riwayat stok benih sekarang ditautkan lewat warehouse_lot_id (lot) atau inventory_type_id.
        return $this->hasMany(StockHistory::class, 'inventory_type_id', 'inventory_type_id');
    }

    public function certificationReport(): BelongsTo
    {
        return $this->belongsTo(CertificationReport::class, 'certification_report_id', 'certification_report_id');
    }
}

