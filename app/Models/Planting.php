<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Planting extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $table = 'planting_production';
    protected $primaryKey = 'planting_production_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'seed_source_id',
        'planting_field_id',
        'planting_batch_number',
        'planting_amount',
        'planting_amount_seeds',
        'progress',
        'planted_at',
        'estimated_harvest_date',
        'target_kelas',
        'no_form_bpsb',
        'tanggal_daftar_bpsb',
        'description',
        'file_path',
        'file_name',
        'is_completed',
        'completed_at',
        'status',
        'satuan_panen_id',
    ];

    protected $casts = [
        'planted_at' => 'date',
        'estimated_harvest_date' => 'date',
        'tanggal_daftar_bpsb' => 'date',
        'completed_at' => 'date',
        'is_completed' => 'boolean',
        'planting_amount' => 'decimal:2',
        'progress' => 'integer',
    ];

    public function getPlantingIdAttribute(): ?string
    {
        return $this->attributes['planting_production_id'] ?? $this->getKey();
    }

    public function setPlantingIdAttribute($value): void
    {
        $this->attributes['planting_production_id'] = $value;
    }

    public function getPlantIdAttribute(): ?string
    {
        return $this->seedSource?->seed_varieties_id;
    }

    public function getPlantingLocationIdAttribute(): ?string
    {
        return $this->field?->planting_location_id;
    }

    public function seedSource(): BelongsTo
    {
        return $this->belongsTo(SeedSource::class, 'seed_source_id', 'seed_source_id');
    }

    public function scopeForPlant($query, $plantId)
    {
        return $query->whereHas('seedSource', function ($q) use ($plantId) {
            $q->where('seed_varieties_id', $plantId);
        });
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(PlantingField::class, 'planting_field_id', 'id');
    }

    public function plant(): HasOneThrough
    {
        return $this->hasOneThrough(
            Plant::class,
            SeedSource::class,
            'seed_source_id',
            'seed_varieties_id',
            'seed_source_id',
            'seed_varieties_id'
        );
    }

    public function location(): HasOneThrough
    {
        return $this->hasOneThrough(
            PlantingLocation::class,
            PlantingField::class,
            'id',
            'planting_location_id',
            'planting_field_id',
            'planting_location_id'
        );
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PlantingReport::class, 'planting_id', 'planting_production_id');
    }

    public function notes(): HasMany
    {
        return $this->assignments();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PlantingAssignment::class, 'planting_id', 'planting_production_id');
    }

    public function certificationApplications(): HasMany
    {
        return $this->hasMany(CertificationApplication::class, 'planting_id', 'planting_production_id');
    }

    public function certificationInspections(): HasMany
    {
        return $this->hasMany(CertificationInspectionField::class, 'planting_id', 'planting_production_id');
    }

    public function certificationFieldSamples(): HasMany
    {
        return $this->hasMany(CertificationFieldSample::class, 'planting_id', 'planting_production_id');
    }

    public function certificationHarvests(): HasMany
    {
        return $this->hasMany(CertificationHarvest::class, 'planting_id', 'planting_production_id');
    }

    public function certificationPcbs(): HasMany
    {
        return $this->hasMany(CertificationPcb::class, 'planting_id', 'planting_production_id');
    }

    public const STATUS_PERENCANAAN = 'perencanaan_persiapan';
    public const STATUS_PENANAMAN = 'penanaman_pemeliharaan';
    public const STATUS_PANEN = 'panen';
    public const STATUS_PASCA_PANEN = 'pasca_panen';
    public const STATUS_STOK = 'stok';

    public const STATUSES = [
        self::STATUS_PERENCANAAN => 'Persiapan dan Perencanaan',
        self::STATUS_PENANAMAN => 'Budidaya',
        self::STATUS_PANEN => 'Panen',
        self::STATUS_PASCA_PANEN => 'Pascapanen',
        self::STATUS_STOK => 'Pascapanen',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PERENCANAAN => 'secondary',
        self::STATUS_PENANAMAN => 'info',
        self::STATUS_PANEN => 'warning',
        self::STATUS_PASCA_PANEN => 'primary',
        self::STATUS_STOK => 'success',
    ];

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'secondary';
    }

    /**
     * Tahapan sertifikasi yang sudah tercatat, terurut dari yang terbaru.
     *
     * @return \Illuminate\Support\Collection<int, array{stage: string, status: string, at: mixed, record: \Illuminate\Database\Eloquent\Model}>
     */
    public function certificationTimeline()
    {
        $map = [
            'application' => [$this->certificationApplications, self::STATUS_PENANAMAN],
            'inspection' => [$this->certificationInspections, self::STATUS_PANEN],
            'field_sample' => [$this->certificationFieldSamples, self::STATUS_PANEN],
            'harvest' => [$this->certificationHarvests, self::STATUS_PASCA_PANEN],
            'pcb' => [$this->certificationPcbs, self::STATUS_PASCA_PANEN],
        ];

        return collect($map)
            ->flatMap(function (array $pair, string $stage) {
                [$records, $status] = $pair;

                return collect($records)->map(fn ($record) => [
                    'stage' => $stage,
                    'status' => $status,
                    'at' => $record->created_at,
                    'record' => $record,
                ]);
            })
            ->sortByDesc('at')
            ->values();
    }

    public function satuanPanen(): BelongsTo
    {
        return $this->belongsTo(SeedUnit::class, 'satuan_panen_id', 'seed_unit_id');
    }

    public function postHarvests(): HasMany
    {
        return $this->hasMany(PlantingPostHarvest::class, 'planting_id', 'planting_production_id');
    }

    public function latestPostHarvest(): ?PlantingPostHarvest
    {
        if ($this->relationLoaded('postHarvests')) {
            return $this->postHarvests->sortByDesc('uji_ke')->sortByDesc('created_at')->first();
        }

        return $this->postHarvests()->orderByDesc('uji_ke')->orderByDesc('created_at')->first();
    }

    public function recertSourceId(): ?string
    {
        if (preg_match('/\[recert:([^\]]+)\]/', (string) $this->description, $match)) {
            return $match[1];
        }

        return null;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ($this->status ?: self::STATUSES[self::STATUS_PERENCANAAN]);
    }

    public function canAddReports(): bool
    {
        return ! $this->is_completed;
    }

    /**
     * Status produksi mengikuti laporan sertifikasi terakhir; hasil uji lab
     * selalu memenangkan perhitungan karena menandai benih sudah jadi stok.
     */
    public function syncProductionStatus(): void
    {
        $status = self::STATUS_PERENCANAAN;

        if ($this->postHarvests()->exists()) {
            $status = self::STATUS_STOK;
        } else {
            $latest = $this->certificationTimeline()->first();
            if ($latest) {
                $status = $latest['status'];
            }
        }

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
