<?php

namespace App\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location_id',
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

    public function baseLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function nutrients(): HasMany
    {
        return $this->hasMany(Nutrient::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PlantingLocationNote::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PlantingLocationPhoto::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'planting_location_id');
    }

    public function responsibleContacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_planting_location')->withTimestamps();
    }

    public function landManagerContacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_planting_location_land_manager')->withTimestamps();
    }

    public function landWorkerContacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_planting_location_land_worker')->withTimestamps();
    }
}


