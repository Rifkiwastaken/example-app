<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan landing page (tabel landing_page_settings telah dihapus).
 * Semua method mengembalikan default tanpa akses database.
 */
class LandingPageSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key (selalu return default).
     */
    public static function getValue(string $key, string $default = ''): string
    {
        return $default;
    }

    /**
     * Set setting value by key (no-op, tabel sudah dihapus).
     */
    public static function setValue(string $key, string $value): void
    {
        // no-op
    }

    /**
     * Get all settings as array (kosong; view memakai fallback ?? default).
     */
    public static function getAllSettings(): array
    {
        return [];
    }
}
