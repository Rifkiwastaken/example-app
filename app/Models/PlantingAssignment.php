<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingAssignment extends Model
{
    use HasCustomId;

    public const STATUS_ASSIGNED = 'ditugaskan';
    public const STATUS_DONE = 'selesai';

    protected $table = 'planting_assignments';
    protected $primaryKey = 'planting_assignment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'planting_location_id',
        'planting_id',
        'task_title',
        'description',
        'attachment_date',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'created_by',
        'intended_for',
        'status',
        'completed_at',
        'completed_by',
        'completion_note',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'attachment_date' => 'date',
        'completed_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_production_id');
    }

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intended_for', 'user_id');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by', 'user_id');
    }

    public function isAssignedTo(?User $user): bool
    {
        return $user && $this->intended_for === $user->getKey();
    }

    public function statusLabel(): string
    {
        return $this->status === self::STATUS_DONE ? 'Selesai' : 'Ditugaskan';
    }
}
