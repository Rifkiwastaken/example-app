<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantingField extends Model
{
    use HasFactory;

    public const STATUS_DIGUNAKAN = 'Digunakan';
    public const STATUS_BERA = 'Bera';
    public const STATUS_PERSIAPAN = 'Persiapan';

    protected $table = 'planting_fields';
    protected $primaryKey = 'id';

    protected $fillable = [
        'planting_location_id',
        'kode_lahan',
        'luas_ha',
        'panjang_m',
        'lebar_m',
        'koordinat_gps',
        'peta_lahan',
        'status_lahan',
        'description',
        'file_path',
        'file_name',
    ];

    protected $casts = [
        'luas_ha' => 'decimal:2',
        'panjang_m' => 'decimal:2',
        'lebar_m' => 'decimal:2',
        'peta_lahan' => 'array',
    ];

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class, 'planting_field_id', 'id');
    }

    public function statusLabel(): string
    {
        return match ($this->status_lahan) {
            self::STATUS_BERA => 'Bera (Istirahat)',
            self::STATUS_DIGUNAKAN => 'Digunakan',
            self::STATUS_PERSIAPAN => 'Persiapan',
            default => $this->status_lahan ?: '-',
        };
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIGUNAKAN => 'Digunakan',
            self::STATUS_BERA => 'Bera (Istirahat)',
            self::STATUS_PERSIAPAN => 'Persiapan',
        ];
    }

    public function hasPolygon(): bool
    {
        return count($this->polygonPoints()) === 4;
    }

    public function polygonPoints(): array
    {
        $points = $this->peta_lahan;
        if (! is_array($points)) {
            return [];
        }

        $normalized = [];
        foreach ($points as $point) {
            $lat = is_array($point) ? ($point['lat'] ?? $point[0] ?? null) : null;
            $lng = is_array($point) ? ($point['lng'] ?? $point[1] ?? null) : null;
            if (! is_numeric($lat) || ! is_numeric($lng)) {
                continue;
            }
            $normalized[] = [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
            ];
        }

        return $normalized;
    }
}
