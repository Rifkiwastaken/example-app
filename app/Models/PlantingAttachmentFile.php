<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingAttachmentFile extends Model
{
    protected $table = 'planting_attachment_files';

    protected $fillable = [
        'planting_attachment_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'planting_attachment_id', 'planting_attachment_id');
    }
}
