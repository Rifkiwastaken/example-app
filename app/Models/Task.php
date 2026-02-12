<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'task_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'task_report',
        'checklist',
        'attachments',
        'association',
        'new_status',
        'assigned_to',
        'created_by',
        'new_priority',
        'start_date',
        'start_time',
        'due_date',
        'due_time',
        'template_id',
        'series_id',
        'planting_location_id',
        'planting_id',
        'task_color',
        'collaborators',
        'repeats',
        'hours_spent',
        'last_edited_at',
        'last_edited_by',
    ];

    protected $casts = [
        'checklist' => 'array',
        'attachments' => 'array',
        'collaborators' => 'array',
        'start_date' => 'date',
        'start_time' => 'datetime',
        'due_date' => 'date',
        'due_time' => 'datetime',
        'last_edited_at' => 'datetime',
    ];

    /**
     * Get the user assigned to this task
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    /**
     * Get the user who created this task
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the user who last edited this task
     */
    public function lastEditedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by', 'user_id');
    }

    /**
     * Get the template this task uses
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'template_id', 'task_template_id');
    }

    /**
     * Get the series this task belongs to
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(TaskSeries::class, 'series_id', 'task_series_id');
    }

    /**
     * Get the planting location this task is associated with
     */
    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    /**
     * Get the planting this task is associated with
     */
    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_id');
    }

    /**
     * Get the status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->new_status) {
            'selesai' => 'Telah dilakukan (Selesai)',
            'dalam_progress' => 'Dalam progress/ akan dilakukan',
            'tidak_selesai' => 'Tidak selesai',
            'dilakukan' => 'Dilakukan', // Legacy support
            'tidak_selesai' => 'Tidak Selesai', // Legacy support
            'terlewat' => 'Terlewat', // Legacy support
            'ditinggalkan' => 'Ditinggalkan', // Legacy support
            default => $this->new_status,
        };
    }

    /**
     * Get the priority label in Indonesian
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->new_priority) {
            'tertinggi' => 'Tertinggi',
            'tinggi' => 'Tinggi',
            'medium' => 'Medium',
            'rendah' => 'Rendah',
            'sangat_rendah' => 'Sangat Rendah',
            default => $this->new_priority,
        };
    }

    /**
     * Get the association label in Indonesian
     */
    public function getAssociationLabelAttribute(): string
    {
        return match($this->association) {
            'penanaman' => 'Penanaman',
            'sertifikasi' => 'Sertifikasi',
            'gudang' => 'Gudang',
            'penjualan' => 'Penjualan',
            default => $this->association,
        };
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            'dilakukan' => 'Dilakukan',
            'dalam_progress' => 'Dalam Progress',
            'selesai' => 'Selesai',
            'tidak_selesai' => 'Tidak Selesai',
            'terlewat' => 'Terlewat',
            'ditinggalkan' => 'Ditinggalkan',
        ];
    }

    /**
     * Get all available priorities
     */
    public static function getPriorities(): array
    {
        return [
            'tertinggi' => 'Tertinggi',
            'tinggi' => 'Tinggi',
            'medium' => 'Medium',
            'rendah' => 'Rendah',
            'sangat_rendah' => 'Sangat Rendah',
        ];
    }

    /**
     * Get all available associations
     */
    public static function getAssociations(): array
    {
        return [
            'penanaman' => 'Penanaman',
            'sertifikasi' => 'Sertifikasi',
            'gudang' => 'Gudang',
            'penjualan' => 'Penjualan',
        ];
    }

    /**
     * Scope for tasks assigned to specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for tasks by association
     */
    public function scopeByAssociation($query, $association)
    {
        return $query->where('association', $association);
    }

    /**
     * Scope for tasks by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('new_status', $status);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now()->toDateString() && !in_array($this->new_status, ['selesai', 'tidak_selesai']);
    }
}

