<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planting extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'planting_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'plant_id',
        'planting_location_id',
        'planting_batch_number',
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
        'estimated_harvest_date',
        'area_ha',
        'planting_format',
        'planting_format_custom',
        'is_completed',
    ];

    protected $casts = [
        'perennial' => 'boolean',
        'planted_at' => 'date',
        'estimated_harvest_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'plant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function harvest(): HasOne
    {
        return $this->hasOne(Harvest::class, 'planting_id', 'planting_id');
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'planting_id', 'planting_id');
    }

    public function losses(): HasMany
    {
        return $this->hasMany(PlantingLoss::class, 'planting_id', 'planting_id');
    }
}

