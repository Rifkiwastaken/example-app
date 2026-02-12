<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'treatment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'treatment_name',
        'treatment_type',
        'product_detail',
        'responsible_person_id',
        'opt_institution',
        'application_method',
        'withholding_period_days',
        'technician',
        'institution_source',
        'attachment',
        'batch_number',
        'description',
        'treatment_date',
        'retreat_date',
        'treatment_location',
        'amount_applied',
        'unit_measurement',
        'total_cost',
        'keywords',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'retreat_date' => 'date',
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

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }
}