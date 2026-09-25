<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedSourceFile extends Model
{
    protected $table = 'seed_source_files';

    protected $fillable = [
        'seed_source_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    public function seedSource(): BelongsTo
    {
        return $this->belongsTo(SeedSource::class, 'seed_source_id', 'seed_source_id');
    }
}
