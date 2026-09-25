<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantAttachment extends Model
{
    use HasFactory;
    use HasCustomId;

    public const TYPE_NOTE = 'note';
    public const TYPE_PHOTO = 'photo';

    protected $table = 'plant_attachments';
    protected $primaryKey = 'plant_attachment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'seed_varieties_id',
        'plant_id',
        'type',
        'description',
        'note_date',
        'keywords',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'attachment_path',
        'taken_at',
    ];

    protected $casts = [
        'note_date' => 'date',
        'taken_at' => 'datetime',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function getPlantIdAttribute(): ?string
    {
        return $this->attributes['seed_varieties_id'] ?? null;
    }

    public function setPlantIdAttribute($value): void
    {
        $this->attributes['seed_varieties_id'] = $value;
    }

    public function getAttachmentPathAttribute(): ?string
    {
        return $this->attributes['file_path'] ?? null;
    }

    public function getPlantNoteIdAttribute(): ?string
    {
        return $this->getKey();
    }

    public function getPlantPhotoIdAttribute(): ?string
    {
        return $this->getKey();
    }
}
