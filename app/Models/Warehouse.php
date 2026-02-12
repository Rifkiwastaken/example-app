<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Warehouse extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'warehouse_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'internal_id',
        'tracking_type',
        'description',
        'responsible_person_id',
    ];

    protected $casts = [
        'tracking_type' => 'string',
    ];

    /**
     * Get all bins for this warehouse
     */
    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class, 'warehouse_id', 'warehouse_id');
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

    /**
     * Get the responsible person (user) for this warehouse
     */
    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

