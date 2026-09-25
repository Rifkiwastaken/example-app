<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasCustomId;

    public const MODULE_PLANT = 'plant';
    public const MODULE_LOCATION = 'location';
    public const MODULE_STOCK = 'stock';

    protected $table = 'attachments';
    protected $primaryKey = 'attachment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'seed_varieties_id',
        'planting_location_id',
        'stock_id',
        'module',
        'title',
        'attachment_date',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'created_by',
    ];

    protected $casts = [
        'attachment_date' => 'date',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function scopeForPlant($query, string $plantId)
    {
        return $query->where('module', self::MODULE_PLANT)->where('seed_varieties_id', $plantId);
    }

    public function scopeForLocation($query, string $locationId)
    {
        return $query->where('module', self::MODULE_LOCATION)->where('planting_location_id', $locationId);
    }

    public function scopeForStockPlant($query, string $plantId)
    {
        return $query->where('module', self::MODULE_STOCK)->where('seed_varieties_id', $plantId);
    }
}
