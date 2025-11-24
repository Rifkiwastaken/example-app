<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingLocationNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_location_id',
        'title',
        'description',
        'note_date',
        'keywords',
        'attachment_path',
        'user_id',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

