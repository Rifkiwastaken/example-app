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

    protected $primaryKey = 'plant_type_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'category',
        'variety',
    ];

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class, 'plant_type_id', 'plant_type_id');
    }
}


















