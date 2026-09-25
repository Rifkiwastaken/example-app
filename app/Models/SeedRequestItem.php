<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedRequestItem extends Model
{
    use HasCustomId;

    protected $table = 'seed_request_items';
    protected $primaryKey = 'seed_request_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'seed_request_id',
        'seed_varieties_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeedRequest::class, 'seed_request_id', 'seed_request_id');
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'seed_varieties_id', 'seed_varieties_id');
    }
}
