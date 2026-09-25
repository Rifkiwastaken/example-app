<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Expense extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'expense_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
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
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'work_date' => 'date',
        'amount' => 'decimal:2',
        'edited_at' => 'datetime',
    ];

    /** Lokasi penanaman melalui planting. */
    public function plantingLocation(): HasOneThrough
    {
        return $this->hasOneThrough(
            PlantingLocation::class,
            Planting::class,
            'planting_location_id', // FK on plantings -> planting_locations
            'planting_id',          // FK on expenses -> plantings
            'planting_id',          // local key on expenses
            'planting_id'           // local key on plantings
        );
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_production_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }

    /** Tanaman (plant) melalui planting. */
    public function getPlantAttribute()
    {
        return $this->planting && $this->planting->plant ? $this->planting->plant : null;
    }
}
