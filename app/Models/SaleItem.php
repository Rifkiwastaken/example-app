<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'sale_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sale_id',
        'inventory_type_id',
        'inventory_lot_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    /**
     * Get the inventory type
     */
    public function inventoryType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');
    }

    /**
     * Get the inventory lot
     */
    public function inventoryLot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'inventory_lot_id', 'inventory_lot_id');
    }
}

