<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plant extends Model
{
    use HasFactory;
    use HasCustomId;

    public const AGRONOMY_ATTRIBUTES = [
        'days_to_emerge',
        'spacing_between_plants',
        'spacing_between_rows',
        'sowing_depth',
        'avg_height',
        'start_method',
        'germination_stage',
        'seeds_per_hole',
        'light_profile',
        'soil_condition',
        'planting_detail',
        'pruning_detail',
        'perennial',
        'days_to_flower',
        'days_to_harvest',
        'harvest_window_days',
        'expected_loss_rate',
        'harvest_unit',
    ];

    protected $table = 'plant_varieties';
    protected $primaryKey = 'seed_varieties_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'seed_commodity_id',
        'plant_type_id',
        'variety',
        'description',
        'days_to_emerge',
        'spacing_between_plants',
        'spacing_between_rows',
        'sowing_depth',
        'avg_height',
        'start_method',
        'germination_stage',
        'seeds_per_hole',
        'light_profile',
        'soil_condition',
        'planting_detail',
        'pruning_detail',
        'perennial',
        'days_to_flower',
        'days_to_harvest',
        'harvest_window_days',
        'expected_loss_rate',
        'harvest_unit',
        'satuan_stok_id',
        'satuan_tanam_id',
        'satuan_panen_id',
        'harga_jual',
        'minimal_stok',
        'public_photo_path',
        'public_description',
    ];

    protected $casts = [
        'perennial' => 'boolean',
        'harga_jual' => 'decimal:2',
        'minimal_stok' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Plant $plant) {
            $plant->seedSources()->delete();
            $plant->attachments()->delete();
        });
    }

    public function setAttribute($key, $value)
    {
        if ($key === 'plant_type_id') {
            return parent::setAttribute('seed_commodity_id', $value);
        }

        if ($key === 'plant_id') {
            return parent::setAttribute('seed_varieties_id', $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        if ($key === 'plant_type_id') {
            return $this->attributes['seed_commodity_id'] ?? null;
        }

        if ($key === 'plant_id') {
            return $this->attributes['seed_varieties_id'] ?? null;
        }

        return parent::getAttribute($key);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PlantType::class, 'seed_commodity_id', 'seed_commodity_id');
    }

    public function satuanStok(): BelongsTo
    {
        return $this->belongsTo(SeedUnit::class, 'satuan_stok_id', 'seed_unit_id');
    }

    public function satuanTanam(): BelongsTo
    {
        return $this->belongsTo(SeedUnit::class, 'satuan_tanam_id', 'seed_unit_id');
    }

    public function satuanPanen(): BelongsTo
    {
        return $this->belongsTo(SeedUnit::class, 'satuan_panen_id', 'seed_unit_id');
    }

    public function displayName(): string
    {
        $this->loadMissing('type');
        $type = $this->type?->name;
        $variety = $this->variety ?: $this->name;

        if ($type && $variety && strcasecmp($type, $variety) !== 0) {
            return $type.' · '.$variety;
        }

        return $variety ?: $type ?: '-';
    }

    public function commodityVarietyLabel(): string
    {
        $this->loadMissing('type');
        $commodity = $this->type?->name ?: $this->name;
        $variety = $this->variety ?: $this->name;

        if ($commodity && $variety && strcasecmp((string) $commodity, (string) $variety) !== 0) {
            return $commodity.' - '.$variety;
        }

        return $variety ?: $commodity ?: '-';
    }

    public function publicPhotoUrl(): string
    {
        if ($this->public_photo_path && \Storage::disk('public')->exists($this->public_photo_path)) {
            return asset('storage/'.$this->public_photo_path);
        }

        return asset($this->placeholderPhotoPath());
    }

    public function placeholderPhotoPath(): string
    {
        $this->loadMissing('type');
        $haystack = mb_strtolower(trim(($this->type?->category ?? '').' '.($this->type?->name ?? '').' '.($this->name ?? '')));

        if (str_contains($haystack, 'buah') || str_contains($haystack, 'durian') || str_contains($haystack, 'mangga')) {
            return 'images/placeholders/plant-buah.jpg';
        }
        if (str_contains($haystack, 'horti') || str_contains($haystack, 'sayur') || str_contains($haystack, 'cabai') || str_contains($haystack, 'bawang')) {
            return 'images/placeholders/plant-hortikultura.jpg';
        }
        if (str_contains($haystack, 'pangan') || str_contains($haystack, 'padi')) {
            return 'images/placeholders/plant-pangan.jpg';
        }

        return 'images/placeholders/plant-generic.jpg';
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function seedSources(): HasMany
    {
        return $this->hasMany(SeedSource::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function plantingLocations(): HasManyThrough
    {
        return $this->hasManyThrough(
            PlantingLocation::class,
            Planting::class,
            'seed_source_id',
            'planting_location_id',
            'seed_varieties_id',
            'planting_location_id'
        );
    }

    public function plantings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Planting::class,
            SeedSource::class,
            'seed_varieties_id',
            'seed_source_id',
            'seed_varieties_id',
            'seed_source_id'
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'seed_varieties_id', 'seed_varieties_id')
            ->where('module', Attachment::MODULE_PLANT);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PlantNote::class, 'seed_varieties_id', 'seed_varieties_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PlantPhoto::class, 'seed_varieties_id', 'seed_varieties_id');
    }
}
