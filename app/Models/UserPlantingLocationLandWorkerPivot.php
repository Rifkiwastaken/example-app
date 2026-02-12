<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserPlantingLocationLandWorkerPivot extends Pivot
{
    use HasCustomId;

    protected $table = 'user_planting_location_land_worker';

    /**
     * Migration uses same PK column name as land_manager table (typo).
     * Use same name so INSERT works.
     */
    protected $primaryKey = 'user_planting_location_land_manager_id';

    public $incrementing = false;

    protected $keyType = 'string';
}
