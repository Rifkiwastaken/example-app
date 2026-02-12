<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryNote extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'inventory_note_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'inventory_type_id',
        'content',
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
     * Get the user who created the note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

