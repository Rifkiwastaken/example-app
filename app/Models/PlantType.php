<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantType extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $table = 'plant_commodities';
    protected $primaryKey = 'seed_commodity_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'category',
    ];

    protected $appends = [
        'plant_type_id',
    ];

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class, 'seed_commodity_id', 'seed_commodity_id');
    }

    public function getPlantTypeIdAttribute(): ?string
    {
        return $this->attributes['seed_commodity_id'] ?? $this->getKey();
    }

    public function setPlantTypeIdAttribute($value): void
    {
        $this->attributes['seed_commodity_id'] = $value;
    }
}
