<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingLoss extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_id',
        'loss_date',
        'loss_amount',
        'loss_reason',
        'description',
    ];

    protected $casts = [
        'loss_date' => 'date',
        'loss_amount' => 'decimal:2',
    ];

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class);
    }
}


