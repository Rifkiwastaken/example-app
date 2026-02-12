<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nutrient extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'nutrient_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'nutrient_name',
        'product_applied',
        'amount_applied',
        'unit',
        'application_method',
        'application_date',
        'total_cost',
        'technician',
        'institution_source',
        'responsible_person_id',
        'attachment',
        'description',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'application_date' => 'date',
        'amount_applied' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'edited_at' => 'datetime',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}