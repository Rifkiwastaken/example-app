<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingLocationPhoto extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'planting_location_photo_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'description',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'date',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_id');
    }
}

