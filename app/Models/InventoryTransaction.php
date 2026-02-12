<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'inventory_transaction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'inventory_type_id',
        'inventory_lot_id',
        'transaction_type',
        'quantity',
        'unit',
        'warehouse_id',
        'bin_id',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

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

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the bin
     */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    /**
     * Get the user who made the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get transaction type label
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return match($this->transaction_type) {
            'stok_masuk' => 'Stok Masuk (Lot)',
            'stok_keluar' => 'Stok Keluar',
            'penyesuaian_tambah' => 'Penyesuaian (+)',
            'penyesuaian_kurang' => 'Penyesuaian (-)',
            'distribusi' => 'Distribusi',
            'pindah_lokasi' => 'Pindahkan Stok',
            default => $this->transaction_type,
        };
    }
}

