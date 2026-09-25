<?php

namespace App\Models;

class PlantPhoto extends PlantAttachment
{
    protected static function booted(): void
    {
        static::addGlobalScope('photo', function ($query) {
            $query->where('type', self::TYPE_PHOTO);
        });

        static::creating(function (PlantPhoto $photo) {
            $photo->type = self::TYPE_PHOTO;
        });
    }
}
