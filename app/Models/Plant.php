<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plant_type_id',
        'variety',
        'status',
        'progress',
        'planting_location_id',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PlantType::class, 'plant_type_id');
    }

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id');
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PlantNote::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PlantPhoto::class);
    }
}


