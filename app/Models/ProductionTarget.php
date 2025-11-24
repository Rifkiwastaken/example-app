<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year',
        'commodity',
        'variety_name',
        'seed_class',
        'planting_location_id',
        'target_planting_area',
        'target_production_volume',
        'estimated_productivity',
        'realized_planting_area',
        'realized_production_volume',
        'notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'target_planting_area' => 'decimal:2',
        'target_production_volume' => 'decimal:2',
        'estimated_productivity' => 'decimal:2',
        'realized_planting_area' => 'decimal:2',
        'realized_production_volume' => 'decimal:2',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function getAchievementPercentageAttribute(): float
    {
        if ($this->target_production_volume == 0) {
            return 0;
        }
        return round(($this->realized_production_volume / $this->target_production_volume) * 100, 2);
    }

    public function getPlantingAreaAchievementAttribute(): float
    {
        if ($this->target_planting_area == 0) {
            return 0;
        }
        return round(($this->realized_planting_area / $this->target_planting_area) * 100, 2);
    }
}
