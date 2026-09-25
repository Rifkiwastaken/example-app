<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_type_seeds')) {
            return;
        }

        $dropForeignKeysForColumn = function (string $column): void {
            $keys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'inventory_type_seeds'
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$column]);

            foreach ($keys as $key) {
                if (!empty($key->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
                }
            }
        };

        if (Schema::hasColumn('inventory_type_seeds', 'plant_id')) {
            $dropForeignKeysForColumn('plant_id');
        }
        if (Schema::hasColumn('inventory_type_seeds', 'planting_location_id')) {
            $dropForeignKeysForColumn('planting_location_id');
        }

        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_type_seeds', 'plant_id')) {
                $table->dropColumn('plant_id');
            }

            if (Schema::hasColumn('inventory_type_seeds', 'planting_location_id')) {
                $table->dropColumn('planting_location_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('inventory_type_seeds')) {
            return;
        }

        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_type_seeds', 'plant_id')) {
                $table->string('plant_id', 36)->nullable()->after('inventory_type_id');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('plant_id');
            }
        });
    }
};

