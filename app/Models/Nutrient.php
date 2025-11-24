<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nutrient extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'product_applied',
        'amount_applied',
        'unit',
        'application_method',
        'application_date',
        'total_cost',
        'technician',
        'description',
    ];

    protected $casts = [
        'application_date' => 'date',
        'amount_applied' => 'decimal:2',
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
}