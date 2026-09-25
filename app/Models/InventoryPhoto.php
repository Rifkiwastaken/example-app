<?php

namespace App\Models;

class InventoryPhoto extends InventoryAttachment
{
    protected static function booted(): void
    {
        static::addGlobalScope('photo', function ($query) {
            $query->where('type', self::TYPE_PHOTO);
        });

        static::creating(function (InventoryPhoto $photo) {
            $photo->type = self::TYPE_PHOTO;
        });
    }
}
