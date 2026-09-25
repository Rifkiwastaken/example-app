<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingLocationAttachment extends Model
{
    use HasCustomId;

    protected $table = 'planting_location_attachment';
    protected $primaryKey = 'planting_location_attachment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'title',
        'attachment_type',
        'plant_type',
        'description',
        'attachment_date',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'created_by',
    ];

    protected $casts = [
        'attachment_date' => 'date',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
