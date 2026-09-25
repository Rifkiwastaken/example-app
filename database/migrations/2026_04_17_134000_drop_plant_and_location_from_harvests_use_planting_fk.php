<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('harvests')) {
            return;
        }

        // Ensure planting_id exists and is linked to plantings.
        if (Schema::hasColumn('harvests', 'planting_id')) {
            $this->ensureForeignKey('harvests', 'planting_id', 'plantings', 'planting_id', 'set null');
        }

        foreach (['plant_id', 'planting_location_id'] as $column) {
            if (Schema::hasColumn('harvests', $column)) {
                $this->dropForeignByColumn('harvests', $column);
                $this->dropIndexesByColumn('harvests', $column);
            }
        }

        Schema::table('harvests', function (Blueprint $table) {
            if (Schema::hasColumn('harvests', 'plant_id')) {
                $table->dropColumn('plant_id');
            }
            if (Schema::hasColumn('harvests', 'planting_location_id')) {
                $table->dropColumn('planting_location_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('harvests')) {
            return;
        }

        Schema::table('harvests', function (Blueprint $table) {
            if (!Schema::hasColumn('harvests', 'plant_id')) {
                $table->string('plant_id', 36)->nullable()->after('planting_id');
            }
            if (!Schema::hasColumn('harvests', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('plant_id');
            }
        });
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete = 'set null'
    ): void {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }
        if (!Schema::hasTable($refTable) || !Schema::hasColumn($refTable, $refColumn)) {
            return;
        }

        $exists = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?",
            [$table, $column, $refTable]
        );
        if ($exists) {
            return;
        }

        $constraint = "{$table}_{$column}_foreign";
        $deleteClause = $onDelete === 'set null' ? 'ON DELETE SET NULL' : 'ON DELETE CASCADE';

        DB::statement(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$constraint}`
             FOREIGN KEY (`{$column}`)
             REFERENCES `{$refTable}`(`{$refColumn}`)
             {$deleteClause}"
        );
    }

    private function dropForeignByColumn(string $table, string $column): void
    {
        $keys = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$table, $column]
        );

        foreach ($keys as $key) {
            if (!empty($key->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
            }
        }
    }

    private function dropIndexesByColumn(string $table, string $column): void
    {
        $indexes = DB::select(
            "SELECT DISTINCT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND INDEX_NAME <> 'PRIMARY'",
            [$table, $column]
        );

        foreach ($indexes as $index) {
            if (!empty($index->INDEX_NAME)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index->INDEX_NAME}`");
            }
        }
    }
};

