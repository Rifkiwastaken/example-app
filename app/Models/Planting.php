<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planting extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'planting_location_id',
        'bed_label',
        'days_to_emerge',
        'spacing_between_plants',
        'spacing_between_rows',
        'sowing_depth',
        'avg_height',
        'start_method',
        'germination_stage',
        'seeds_per_hole',
        'light_profile',
        'soil_condition',
        'planting_detail',
        'pruning_detail',
        'perennial',
        'days_to_flower',
        'days_to_harvest',
        'harvest_window_days',
        'expected_loss_rate',
        'harvest_unit',
        'expected_yield_per_hectare',
        'quantity_planted',
        'planted_at',
    ];

    protected $casts = [
        'perennial' => 'boolean',
        'planted_at' => 'date',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id');
    }

    public function harvest(): HasOne
    {
        return $this->hasOne(Harvest::class);
    }

    public function losses(): HasMany
    {
        return $this->hasMany(PlantingLoss::class);
    }
}







