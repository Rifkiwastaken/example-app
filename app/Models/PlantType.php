<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
    ];

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }
}















