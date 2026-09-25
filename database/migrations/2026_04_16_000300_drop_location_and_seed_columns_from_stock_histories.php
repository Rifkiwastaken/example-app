<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_histories')) {
            return;
        }

        foreach (['warehouse_id', 'warehouse_bin_id', 'inventory_type_seed_id'] as $column) {
            if (Schema::hasColumn('stock_histories', $column)) {
                $this->dropForeignKeysForColumn('stock_histories', $column);
                $this->dropIndexesForColumn('stock_histories', $column);
            }
        }

        Schema::table('stock_histories', function (Blueprint $table) {
            if (Schema::hasColumn('stock_histories', 'warehouse_id')) {
                $table->dropColumn('warehouse_id');
            }
            if (Schema::hasColumn('stock_histories', 'warehouse_bin_id')) {
                $table->dropColumn('warehouse_bin_id');
            }
            if (Schema::hasColumn('stock_histories', 'inventory_type_seed_id')) {
                $table->dropColumn('inventory_type_seed_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_histories')) {
            return;
        }

        Schema::table('stock_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_histories', 'warehouse_id')) {
                $table->string('warehouse_id', 36)->nullable()->after('unit');
            }
            if (!Schema::hasColumn('stock_histories', 'warehouse_bin_id')) {
                $table->string('warehouse_bin_id', 36)->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn('stock_histories', 'inventory_type_seed_id')) {
                $table->string('inventory_type_seed_id', 36)->nullable()->after('notes');
            }
        });
    }

    private function dropForeignKeysForColumn(string $tableName, string $columnName): void
    {
        $databaseName = DB::getDatabaseName();
        $keys = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$databaseName, $tableName, $columnName]
        );

        foreach ($keys as $key) {
            if (!empty($key->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
            }
        }
    }

    private function dropIndexesForColumn(string $tableName, string $columnName): void
    {
        $databaseName = DB::getDatabaseName();
        $indexes = DB::select(
            "SELECT DISTINCT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND INDEX_NAME <> 'PRIMARY'",
            [$databaseName, $tableName, $columnName]
        );

        foreach ($indexes as $index) {
            if (!empty($index->INDEX_NAME)) {
                DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$index->INDEX_NAME}`");
            }
        }
    }
};

