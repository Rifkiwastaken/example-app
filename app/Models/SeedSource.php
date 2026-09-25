<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedSource extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $table = 'plant_seed_source';
    protected $primaryKey = 'seed_source_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const SEED_CLASS_BS = 'BS';
    public const SEED_CLASS_FS = 'FS';
    public const SEED_CLASS_SS = 'SS';
    public const SEED_CLASS_ES = 'ES';

    public const KELAS_BENIH = [
        'BS' => 'Benih Penjenis (BS)',
        'FS' => 'Benih Dasar (BD/FS)',
        'SS' => 'Benih Pokok (BP)',
        'ES' => 'Benih Sebar (BR)',
    ];

    public const KELAS_WARNA = [
        'BS' => 'kuning',
        'FS' => 'putih',
        'SS' => 'ungu',
        'ES' => 'biru',
    ];

    protected $fillable = [
        'seed_varieties_id',
        'seed_class',
        'origin_lot_number',
        'origin_producer',
        'quantity_kg',
        'quantity_awal',
        'description',
        'file_path',
        'file_name',
    ];

    protected $casts = [
        'quantity_kg' => 'decimal:2',
        'quantity_awal' => 'decimal:2',
    ];

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function plant(): BelongsTo
    {
        return $this->variety();
    }

    public function seedClassBadgeClass(): string
    {
        return match ($this->seed_class) {
            'BS' => 'badge-kelas-bs',
            'FS' => 'badge-kelas-fs',
            'SS' => 'badge-kelas-ss',
            'ES' => 'badge-kelas-es',
            default => 'bg-secondary text-white',
        };
    }

    public function remainingQuantity(): float
    {
        return (float) $this->quantity_kg;
    }

    public function stockStatus(): string
    {
        return $this->remainingQuantity() > 0 ? 'tersedia' : 'tidak_tersedia';
    }

    public function stockStatusLabel(): string
    {
        return $this->stockStatus() === 'tersedia' ? 'Tersedia' : 'Tidak tersedia';
    }

    public function stockUnitLabel(): string
    {
        $this->loadMissing('variety.satuanStok');

        return $this->variety?->satuanStok?->code ?: 'kg';
    }

    public function plantings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Planting::class, 'seed_source_id', 'seed_source_id');
    }

    public function plantedFieldLabels(): array
    {
        $this->loadMissing('plantings.field.plantingLocation');

        return $this->plantings
            ->map(function (Planting $planting) {
                $location = $planting->field?->plantingLocation?->name;
                $field = $planting->field?->kode_lahan;
                if (! $location && ! $field) {
                    return null;
                }

                return trim(($location ?: '-').' / '.($field ?: '-'));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getPlantIdAttribute(): ?string
    {
        return $this->attributes['seed_varieties_id'] ?? null;
    }

    public function setPlantIdAttribute($value): void
    {
        $this->attributes['seed_varieties_id'] = $value;
    }

    public function seedClassLabel(): string
    {
        return self::KELAS_BENIH[$this->seed_class] ?? ($this->seed_class ?: '-');
    }

    public function seedClassColor(): string
    {
        return self::KELAS_WARNA[$this->seed_class] ?? 'abu';
    }

    public function totalQuantity(): float
    {
        return (float) ($this->quantity_awal ?? $this->quantity_kg);
    }
}
