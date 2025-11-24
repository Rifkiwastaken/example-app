<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingLocationPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_location_id',
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
        return $this->belongsTo(PlantingLocation::class);
    }
}

