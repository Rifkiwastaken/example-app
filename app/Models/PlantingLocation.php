<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class, 'planting_location_id', 'planting_location_id');
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class, 'planting_location_id', 'planting_location_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'planting_location_id', 'planting_location_id');
    }

    public function nutrients(): HasMany
    {
        return $this->hasMany(Nutrient::class, 'planting_location_id', 'planting_location_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'planting_location_id', 'planting_location_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PlantingLocationNote::class, 'planting_location_id', 'planting_location_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PlantingLocationPhoto::class, 'planting_location_id', 'planting_location_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'planting_location_id', 'planting_location_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'planting_location_id', 'planting_location_id');
    }

    public function landManagerUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_planting_location_land_manager', 'planting_location_id', 'user_id')
            ->using(UserPlantingLocationLandManagerPivot::class)
            ->withTimestamps();
    }

    public function landWorkerUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_planting_location_land_worker', 'planting_location_id', 'user_id')
            ->using(UserPlantingLocationLandWorkerPivot::class)
            ->withTimestamps();
    }
}

