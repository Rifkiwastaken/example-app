<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PlantingLocation extends Model
{
    use HasFactory;
    use HasCustomId;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'planting_location_id';

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
        'name',
        'location_summary',
        'province',
        'city',
        'district',
        'village',
        'koordinat_gps',
        'administrative_address',
        'google_maps_link',
        'primary_photo_path',
        'location_type',
        'planting_format',
        'planting_format_custom',
        'num_beds',
        'bed_length_m',
        'bed_width_m',
        'map_size',
        'light_condition',
        'land_status',
        'ownership_status',
        'water_source',
        'soil_type',
        'elevation_masl',
        'description',
    ];

    /** Tanaman (katalog) yang pernah ditanam di lokasi ini; melalui plantings (bisa duplikat). */
    public function plantings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Planting::class,
            PlantingField::class,
            'planting_location_id',
            'planting_field_id',
            'planting_location_id',
            'id'
        );
    }

    public function locationAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'planting_location_id', 'planting_location_id')
            ->where('module', Attachment::MODULE_LOCATION);
    }

    public function attachments(): HasMany
    {
        return $this->locationAttachments();
    }

    public function fields(): HasMany
    {
        return $this->hasMany(PlantingField::class, 'planting_location_id', 'planting_location_id');
    }

    public function dailyReportIds()
    {
        return $this->plantings()->pluck('planting_production.planting_production_id');
    }

    public function assignedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'placement_location_id', 'planting_location_id');
    }

    /**
     * Pekerja lahan kini diambil dari penempatan pada akun user
     * (tabel pivot user_planting_location_land_worker sudah dihapus).
     */
    public function landWorkerUsers(): HasMany
    {
        return $this->hasMany(User::class, 'placement_location_id', 'planting_location_id');
    }

    public const FORMAT_PENANAMAN = [
        'ditanam_dalam_petak' => 'Ditanam dalam petak / beds',
        'cover_crop' => 'Tanaman penutup / cover crop',
        'row_crop' => 'Tanaman baris / row crop',
        'lainnya' => 'Lainnya',
    ];

    public function formatPenanamanLabel(): string
    {
        if ($this->planting_format === 'lainnya' && $this->planting_format_custom) {
            return $this->planting_format_custom;
        }

        return self::FORMAT_PENANAMAN[$this->planting_format] ?? ($this->planting_format ?: '-');
    }

    public function administrativeAddressText(): string
    {
        $parts = array_filter([
            $this->village,
            $this->district,
            $this->city,
            $this->province,
        ]);
        if ($parts) {
            return implode(', ', $parts);
        }

        return $this->administrative_address ?: ($this->location_summary ?: '-');
    }

    public function productionVarietyLabels(): array
    {
        $this->loadMissing('plantings.seedSource.plant.type');

        return $this->plantings
            ->where('is_completed', false)
            ->map(function (Planting $planting) {
                $plant = $planting->seedSource?->plant ?: $planting->plant;
                if (! $plant) {
                    return null;
                }

                return $plant->displayName();
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function workerNameLabels(): array
    {
        $fromPivot = $this->relationLoaded('landWorkerUsers')
            ? $this->landWorkerUsers
            : collect();
        $fromPlacement = $this->relationLoaded('assignedUsers')
            ? $this->assignedUsers
            : collect();

        return $fromPivot->merge($fromPlacement)
            ->map(fn ($user) => $user->name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

