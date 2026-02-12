<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantPhoto extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'plant_photo_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'plant_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'description',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'plant_id');
    }
}
















