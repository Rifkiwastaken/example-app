<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserPlantingLocationLandWorkerPivot extends Pivot
{
    use HasCustomId;

    protected $table = 'user_planting_location_land_worker';

    protected $primaryKey = 'user_planting_location_worker_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['planting_location_id', 'user_id', 'role'];

    /** Jabatan: petugas_lapangan | penangkar */
    const ROLE_PETUGAS_LAPANGAN = 'petugas_lapangan';
    const ROLE_PENANGKAR = 'penangkar';

    public static function roleLabels(): array
    {
        return [
            self::ROLE_PETUGAS_LAPANGAN => 'Petugas Lapangan',
            self::ROLE_PENANGKAR => 'Penangkar',
        ];
    }
}
