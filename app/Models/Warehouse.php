<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'internal_id',
        'tracking_type',
        'description',
    ];

    protected $casts = [
        'tracking_type' => 'string',
    ];

    /**
     * Get all bins for this warehouse
     */
    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class);
    }

    /**
     * Get tracking type label
     */
    public function getTrackingTypeLabelAttribute(): string
    {
        return match($this->tracking_type) {
            'bin_separated' => 'Di dalam bin terpisah',
            'warehouse_only' => 'Hanya di lokasi ini',
            default => $this->tracking_type ?? 'Tidak ditentukan',
        };
    }

    /**
     * Get bins count
     */
    public function getBinsCountAttribute(): int
    {
        return $this->bins()->count();
    }
}

