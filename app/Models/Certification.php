<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'harvest_id',
        'planting_location_id',
        'plant_id',
        'certification_status',
        'seed_class_requested',
    ];

    protected $casts = [
        'certification_status' => 'string',
    ];

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CertificationReport::class);
    }

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->certification_status) {
            'dalam_proses' => 'Dalam Proses',
            'lulus' => 'Lulus',
            'tidak_lulus' => 'Tidak Lulus',
            'selesai' => 'Selesai',
            default => $this->certification_status,
        };
    }

    /**
     * Get latest report date
     */
    public function getLatestReportDateAttribute(): ?string
    {
        $latestReport = $this->reports()->orderBy('report_date', 'desc')->first();
        return $latestReport ? $latestReport->report_date->format('d M Y') : null;
    }
}



