<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'treatment_type',
        'product_detail',
        'subtract_from_inventory',
        'opt_institution',
        'application_method',
        'withholding_period_days',
        'technician',
        'batch_number',
        'description',
        'treatment_date',
        'retreat_date',
        'treatment_location',
        'amount_applied',
        'inventory_amount_used',
        'inventory_unit',
        'unit_measurement',
        'total_cost',
        'record_expense',
        'keywords',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'retreat_date' => 'date',
        'record_expense' => 'boolean',
        'amount_applied' => 'decimal:2',
        'inventory_amount_used' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class);
    }

    public function inventoryType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, 'subtract_from_inventory');
    }
}