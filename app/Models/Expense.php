<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'expense_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'expense_name',
        'work_name',
        'amount',
        'expense_type',
        'expense_date',
        'work_date',
        'work_description',
        'worker_name',
        'planting_id',
        'description',
        'responsible_person_id',
        'treatment_id',
        'nutrient_id',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'work_date' => 'date',
        'amount' => 'decimal:2',
        'edited_at' => 'datetime',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function nutrient(): BelongsTo
    {
        return $this->belongsTo(Nutrient::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }

    /**
     * Get plant from treatment or nutrient
     */
    public function getPlantAttribute()
    {
        if ($this->treatment && $this->treatment->planting && $this->treatment->planting->plant) {
            return $this->treatment->planting->plant;
        }
        
        if ($this->nutrient && $this->nutrient->planting && $this->nutrient->planting->plant) {
            return $this->nutrient->planting->plant;
        }
        
        return null;
    }
}
