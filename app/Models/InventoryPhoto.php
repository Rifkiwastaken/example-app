<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPhoto extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'inventory_photo_id';
    public $incrementing = false;
    protected $keyType = 'string';

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
        return $this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get the user who uploaded the photo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

