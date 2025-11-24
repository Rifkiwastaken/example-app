<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Harvest extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'planting_id',
        'planting_location_id',
        'harvested_at',
        'batch_no',
        'note',
        'source',
        'quality',
        'quantity',
        'unit',
        'loss_quantity',
    ];

    protected $casts = [
        'harvested_at' => 'date',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id');
    }

    public function certification(): HasOne
    {
        return $this->hasOne(Certification::class);
    }
}





