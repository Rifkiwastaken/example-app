<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'description',
        'note_date',
        'keywords',
        'attachment_path',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
}













