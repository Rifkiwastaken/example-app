<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskTemplate extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'task_template_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'tasks_list',
        'association',
        'is_active',
    ];

    protected $casts = [
        'tasks_list' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get tasks using this template
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'template_id', 'task_template_id');
    }

    /**
     * Get series using this template
     */
    public function series(): HasMany
    {
        return $this->hasMany(TaskSeries::class, 'template_id', 'task_template_id');
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
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}


















