<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename tables dan perbaiki relasi (nama tabel saja; kolom/FK tidak diubah).
     */
    public function up(): void
    {
        $renames = [
            'planting_location_notes' => 'planting_notes',
            'nutrients' => 'planting_nutrients',
            'treatments' => 'planting_treatments',
            'tasks' => 'planting_tasks',
            'attachments' => 'planting_attachments',
            'inventory_lots' => 'warehouse_lots',
            'bins' => 'warehouse_bins',
            'inventory_type_warehouses' => 'warehouse_type',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $renames = [
            'planting_notes' => 'planting_location_notes',
            'planting_nutrients' => 'nutrients',
            'planting_treatments' => 'treatments',
            'planting_tasks' => 'tasks',
            'planting_attachments' => 'attachments',
            'warehouse_lots' => 'inventory_lots',
            'warehouse_bins' => 'bins',
            'warehouse_type' => 'inventory_type_warehouses',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }
};
