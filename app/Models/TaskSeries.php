<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskSeries extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'task_series_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'template_id',
        'series_tasks',
        'is_active',
    ];

    protected $casts = [
        'series_tasks' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the template this series belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'template_id', 'task_template_id');
    }

    /**
     * Get tasks in this series
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'series_id', 'task_series_id');
    }

    /**
     * Scope for active series
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}


















