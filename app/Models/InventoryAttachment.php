<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAttachment extends Model
{
    use HasFactory;
    use HasCustomId;

    public const TYPE_NOTE = 'note';
    public const TYPE_PHOTO = 'photo';

    protected $table = 'inventory_attachments';
    protected $primaryKey = 'inventory_attachment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'inventory_type_id',
        'type',
        'content',
        'caption',
        'file_path',
        'photo_path',
        'user_id',
    ];

    public function inventoryType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getPhotoPathAttribute(): ?string
    {
        return $this->attributes['file_path'] ?? null;
    }

    public function getInventoryNoteIdAttribute(): ?string
    {
        return $this->getKey();
    }

    public function getInventoryPhotoIdAttribute(): ?string
    {
        return $this->getKey();
    }
}
