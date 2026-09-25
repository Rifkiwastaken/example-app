<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingReport extends Model
{
    use HasCustomId;

    protected $table = 'planting_report';
    protected $primaryKey = 'planting_task_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const PHASES = [
        'persemaian' => 'Persemaian',
        'vegetatif' => 'Vegetatif',
        'berbunga' => 'Berbunga',
        'masak' => 'Masak',
    ];

    public const ACTIVITIES = [
        'tanam' => 'Tanam',
        'pemupukan' => 'Pemupukan',
        'opt' => 'OPT',
        'pengairan' => 'Pengairan',
        'rouging' => 'Rouging',
        'lainnya' => 'Lainnya',
    ];

    /** Jenis kegiatan yang membutuhkan rincian produk yang diaplikasikan. */
    public const ACTIVITIES_WITH_PRODUCT = ['pemupukan', 'opt'];

    /** Jenis kegiatan yang membutuhkan rincian tanaman yang dicabut. */
    public const ACTIVITIES_WITH_ROUGING = ['rouging'];

    protected $fillable = [
        'title',
        'description',
        'planting_id',
        'growth_phase',
        'activity_type',
        'activity_type_custom',
        'product_detail',
        'applied_amount',
        'application_method',
        'plants_removed',
        'characteristic',
        'external_technician',
        'officer_id',
        'activity_date',
        'due_date',
        'file_path',
        'file_name',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'plants_removed' => 'decimal:2',
    ];

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class, 'planting_id', 'planting_production_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id', 'user_id');
    }

    public function phaseLabel(): string
    {
        return self::PHASES[$this->growth_phase] ?? ($this->growth_phase ?: '-');
    }

    public function activityLabel(): string
    {
        if ($this->activity_type === 'lainnya') {
            return $this->activity_type_custom ?: 'Lainnya';
        }

        return self::ACTIVITIES[$this->activity_type] ?? ($this->activity_type ?: '-');
    }

    public function usesProductFields(): bool
    {
        return in_array($this->activity_type, self::ACTIVITIES_WITH_PRODUCT, true);
    }

    public function usesRougingFields(): bool
    {
        return in_array($this->activity_type, self::ACTIVITIES_WITH_ROUGING, true);
    }
}
