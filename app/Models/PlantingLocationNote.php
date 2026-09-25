<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PlantingLocationNote extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $table = 'planting_notes';

    protected $primaryKey = 'planting_note_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_id',
        'title',
        'description',
        'note_date',
        'keywords',
        'attachment_path',
        'user_id',
        'assigned_to',
        'read_by',
    ];

    protected $casts = [
        'note_date' => 'date',
        'assigned_to' => 'array',
        'read_by' => 'array',
    ];

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_production_id');
    }

    /** Lokasi penanaman melalui planting (setelah kolom planting_location_id dihapus). */
    public function plantingLocation(): HasOneThrough
    {
        return $this->hasOneThrough(PlantingLocation::class, Planting::class, 'planting_production_id', 'planting_location_id', 'planting_id', 'planting_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get assigned users
     */
    public function assignedUsers()
    {
        if (!$this->assigned_to) {
            return collect();
        }
        return User::whereIn('user_id', $this->assigned_to)->get();
    }

    /**
     * Check if user has read this note
     */
    public function isReadBy($userId)
    {
        if (!$this->read_by) {
            return false;
        }
        return in_array($userId, $this->read_by);
    }

    /**
     * Mark note as read by user
     */
    public function markAsReadBy($userId)
    {
        $readBy = $this->read_by ?? [];
        if (!in_array($userId, $readBy)) {
            $readBy[] = $userId;
            $this->read_by = $readBy;
            $this->save();
        }
    }

    /**
     * Check if note is assigned to user
     */
    public function isAssignedTo($userId)
    {
        if (!$this->assigned_to) {
            return false;
        }
        return in_array($userId, $this->assigned_to);
    }
}

