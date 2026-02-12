<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'attachment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'title',
        'description',
        'attachment_date',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'created_by',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'attachment_date' => 'date',
        'edited_at' => 'datetime',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }
}

