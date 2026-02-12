<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CertificationReport extends Model
{
    use HasFactory;
    use HasCustomId;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'certification_report_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'certification_id',
        'report_type',
        'report_number_bpsb',
        'report_date',
        'growing_season',
        'inspection_phase',
        'inspector_name',
        'reporter_name',
        'seed_class_result',
        'planting_batch_number',
        'harvest_batch_number',
        'isolation_north',
        'isolation_east',
        'isolation_south',
        'isolation_west',
        'plant_characteristics_match',
        'pest_disease_condition',
        'weed_condition',
        'population_per_sample',
        'other_variety_mix_count',
        'other_variety_mix_percentage',
        'estimated_yield',
        'expiry_date',
        'certified_seed_quantity',
        'certified_seed_unit',
        'seed_unit',
        'seed_unit_quantity',
        'harvest_per_unit',
        'harvest_per_unit_unit',
        'estimated_sale_price_per_kg',
        'conclusion',
        'scan_file_path',
    ];

    protected $casts = [
        'report_date' => 'date',
        'expiry_date' => 'date',
        'plant_characteristics_match' => 'boolean',
        'seed_unit_quantity' => 'decimal:2',
        'harvest_per_unit' => 'decimal:2',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'certification_id', 'certification_id');
    }

    /**
     * Get phase label
     */
    public function getPhaseLabelAttribute(): string
    {
        return $this->inspection_phase;
    }

    /**
     * Get conclusion badge class
     */
    public function getConclusionBadgeClassAttribute(): string
    {
        return match($this->conclusion) {
            'LULUS' => 'badge bg-success',
            'TIDAK LULUS' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get inventory types linked to this certification report
     */
    public function inventoryTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            InventoryType::class,
            'inventory_type_certification_reports',
            'certification_report_id',
            'inventory_type_id'
        )->withPivot('quantity')->withTimestamps();
    }
}

