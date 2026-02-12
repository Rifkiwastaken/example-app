<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Harvest extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'harvest_id';
    public $incrementing = false;
    protected $keyType = 'string';

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
        'harvest_unit',
        'unit_quantity',
        'quantity_per_unit',
        'recorded_by',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'harvested_at' => 'date',
        'edited_at' => 'datetime',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'plant_id');
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function certification(): HasOne
    {
        return $this->hasOne(Certification::class, 'harvest_id', 'harvest_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by', 'user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }
}

