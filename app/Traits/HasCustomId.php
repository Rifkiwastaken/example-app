<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasCustomId
{
    /**
     * Boot the trait.
     */
    protected static function bootHasCustomId(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = $model->generateCustomId();
            }
        });
    }

    /**
     * Get the custom ID prefix for this model.
     * Override this method in your model to set a custom prefix.
     *
     * @return string
     */
    public function getCustomIdPrefix(): string
    {
        // Default: ambil 3 huruf pertama dari nama tabel dalam uppercase
        $tableName = $this->getTable();
        
        // Mapping khusus untuk tabel-tabel tertentu
        $prefixMap = [
            'users' => 'USR',
            'plant_types' => 'PTY',
            'plants' => 'PLT',
            'planting_locations' => 'LOC',
            'plantings' => 'PLN',
            'harvests' => 'HRV',
            'certifications' => 'CRT',
            'certification_reports' => 'CRP',
            'warehouses' => 'WHS',
            'bins' => 'BIN',
            'inventory_types' => 'INV',
            'inventory_lots' => 'LOT',
            'inventory_transactions' => 'TRX',
            'inventory_type_warehouses' => 'ITW',
            'inventory_type_seeds' => 'ITS',
            'inventory_type_certification_reports' => 'ICR',
            'inventory_notes' => 'INN',
            'inventory_photos' => 'INP',
            'sales' => 'SAL',
            'sale_items' => 'SIT',
            'tasks' => 'TSK',
            'task_series' => 'TSR',
            'task_templates' => 'TTP',
            'locations' => 'LCT',
            'nutrients' => 'NTR',
            'treatments' => 'TRT',
            'expenses' => 'EXP',
            'attachments' => 'ATT',
            'seed_histories' => 'SDH',
            'planting_losses' => 'PLS',
            'plant_notes' => 'PLN',
            'plant_photos' => 'PHP',
            'planting_location_notes' => 'LCN',
            'planting_location_photos' => 'LCP',
            'user_planting_location_land_manager' => 'ULM',
            'user_planting_location_land_worker' => 'ULW',
        ];

        return $prefixMap[$tableName] ?? strtoupper(substr($tableName, 0, 3));
    }

    /**
     * Generate a unique custom ID.
     *
     * @return string
     */
    public function generateCustomId(): string
    {
        $prefix = $this->getCustomIdPrefix();
        $maxAttempts = 10;
        $attempt = 0;

        do {
            // Generate random alphanumeric string (8 characters)
            $randomString = $this->generateRandomString(8);
            $customId = "{$prefix}-{$randomString}";
            
            $attempt++;
            
            // Check if ID already exists
            $exists = static::where($this->getKeyName(), $customId)->exists();
            
            if (!$exists) {
                return $customId;
            }
            
        } while ($attempt < $maxAttempts);

        // Fallback: tambahkan timestamp jika masih collision
        $timestamp = substr(time(), -4);
        $randomString = $this->generateRandomString(4);
        return "{$prefix}-{$randomString}{$timestamp}";
    }

    /**
     * Generate random alphanumeric string.
     *
     * @param int $length
     * @return string
     */
    protected function generateRandomString(int $length = 8): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Get the route key for the model.
     * This enables proper route model binding with custom primary keys.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return $this->getKeyName();
    }
}
