<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'certification_id',
        'report_number_bpsb',
        'report_date',
        'growing_season',
        'inspection_phase',
        'inspector_name',
        'seed_class_result',
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
        'conclusion',
        'scan_file_path',
    ];

    protected $casts = [
        'report_date' => 'date',
        'expiry_date' => 'date',
        'plant_characteristics_match' => 'boolean',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
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
}



