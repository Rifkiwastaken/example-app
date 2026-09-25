<?php

namespace App\Models;

class InventoryNote extends InventoryAttachment
{
    protected static function booted(): void
    {
        static::addGlobalScope('note', function ($query) {
            $query->where('type', self::TYPE_NOTE);
        });

        static::creating(function (InventoryNote $note) {
            $note->type = self::TYPE_NOTE;
        });
    }
}
