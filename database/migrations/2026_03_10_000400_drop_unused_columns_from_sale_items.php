<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sale_items')) {
            return;
        }

        $dropForeignKeysForColumn = function (string $column): void {
            $keys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'sale_items'
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$column]);

            foreach ($keys as $key) {
                if (!empty($key->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE `sale_items` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
                }
            }
        };

        foreach (['sale_id', 'planting_location_id', 'inventory_type_id'] as $column) {
            if (Schema::hasColumn('sale_items', $column)) {
                $dropForeignKeysForColumn($column);
            }
        }

        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'sale_id')) {
                $table->dropColumn('sale_id');
            }
            if (Schema::hasColumn('sale_items', 'planting_location_id')) {
                $table->dropColumn('planting_location_id');
            }
            if (Schema::hasColumn('sale_items', 'inventory_type_id')) {
                $table->dropColumn('inventory_type_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sale_items')) {
            return;
        }

        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'sale_id')) {
                $table->string('sale_id', 36)->nullable()->after('sale_item_id');
            }
            if (!Schema::hasColumn('sale_items', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('estimated_planting_area');
            }
            if (!Schema::hasColumn('sale_items', 'inventory_type_id')) {
                $table->string('inventory_type_id', 36)->nullable()->after('inventory_lot_id');
            }
        });
    }
};

