<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedHistory extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'seed_history_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'seed_histories';

    protected $fillable = [
        'inventory_type_seed_id',
        'inventory_type_id',
        'action',
        'description',
        'old_data',
        'new_data',
        'user_id',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function seed(): BelongsTo
    {
        return $this->belongsTo(InventoryTypeSeed::class, 'inventory_type_seed_id', 'inventory_type_seed_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

