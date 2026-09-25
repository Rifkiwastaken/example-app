<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel legacy jika masih ada
        Schema::dropIfExists('warehouse_type');
        Schema::dropIfExists('warehouse_types');

        if (!Schema::hasTable('warehouse_lots')) {
            return;
        }

        $dropForeignKeysForColumn = function (string $column): void {
            $keys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'warehouse_lots'
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$column]);

            foreach ($keys as $key) {
                if (!empty($key->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE `warehouse_lots` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
                }
            }
        };

        if (Schema::hasColumn('warehouse_lots', 'certification_id')) {
            $dropForeignKeysForColumn('certification_id');
        }
        if (Schema::hasColumn('warehouse_lots', 'warehouse_id')) {
            $dropForeignKeysForColumn('warehouse_id');
        }

        Schema::table('warehouse_lots', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_lots', 'certification_id')) {
                $table->dropColumn('certification_id');
            }
            if (Schema::hasColumn('warehouse_lots', 'warehouse_id')) {
                $table->dropColumn('warehouse_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('warehouse_lots')) {
            return;
        }

        Schema::table('warehouse_lots', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_lots', 'warehouse_id')) {
                $table->string('warehouse_id', 36)->nullable()->after('inventory_type_id');
            }
            if (!Schema::hasColumn('warehouse_lots', 'certification_id')) {
                $table->string('certification_id', 36)->nullable()->after('warehouse_bin_id');
            }
        });
    }
};

