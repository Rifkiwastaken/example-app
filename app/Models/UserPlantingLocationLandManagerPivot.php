<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserPlantingLocationLandManagerPivot extends Pivot
{
    use HasCustomId;

    protected $table = 'user_planting_location_land_manager';

    protected $primaryKey = 'user_planting_location_land_manager_id';

    public $incrementing = false;

    protected $keyType = 'string';
}
