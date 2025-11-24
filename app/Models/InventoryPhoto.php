<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_type_id',
        'photo_path',
        'caption',
        'user_id',
    ];

    /**
     * Get the inventory type
     */
    public function inventoryType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class);
    }

    /**
     * Get the user who uploaded the photo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

