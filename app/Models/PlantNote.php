<?php

namespace App\Models;

class PlantNote extends PlantAttachment
{
    protected static function booted(): void
    {
        static::addGlobalScope('note', function ($query) {
            $query->where('type', self::TYPE_NOTE);
        });

        static::creating(function (PlantNote $note) {
            $note->type = self::TYPE_NOTE;
        });
    }
}
