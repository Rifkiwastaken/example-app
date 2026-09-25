<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If legacy table still exists, rename it back.
        if (!Schema::hasTable('inventory_type_warehouses') && Schema::hasTable('warehouse_type')) {
            Schema::rename('warehouse_type', 'inventory_type_warehouses');
        }

        if (!Schema::hasTable('inventory_type_warehouses')) {
            Schema::create('inventory_type_warehouses', function (Blueprint $table) {
                $table->string('inventory_type_warehouse_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->string('warehouse_id', 36);
                $table->string('warehouse_bin_id', 36)->nullable();
                $table->boolean('warehouse_only')->default(false);
                $table->timestamps();

                $table->unique(
                    ['inventory_type_id', 'warehouse_id', 'warehouse_bin_id'],
                    'inv_type_wh_bin_unique'
                );
            });
        }

        // If the pivot exists but still uses old column name `bin_id`, rename it.
        if (Schema::hasTable('inventory_type_warehouses')
            && Schema::hasColumn('inventory_type_warehouses', 'bin_id')
            && !Schema::hasColumn('inventory_type_warehouses', 'warehouse_bin_id')
        ) {
            Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                $table->renameColumn('bin_id', 'warehouse_bin_id');
            });
        }

        // Add foreign keys if possible (skip if already exist / mismatched env)
        if (Schema::hasTable('inventory_type_warehouses')) {
            $this->ensureForeignKey('inventory_type_warehouses', 'inventory_type_id', 'inventory_types', 'inventory_type_id', 'cascade');
            $this->ensureForeignKey('inventory_type_warehouses', 'warehouse_id', 'warehouses', 'warehouse_id', 'cascade');
            $this->ensureForeignKey('inventory_type_warehouses', 'warehouse_bin_id', 'warehouse_bins', 'warehouse_bin_id', 'cascade', true);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_type_warehouses')) {
            Schema::drop('inventory_type_warehouses');
        }
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete,
        bool $nullable = false
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
        $deleteClause = match ($onDelete) {
            'cascade' => 'ON DELETE CASCADE',
            'set null' => 'ON DELETE SET NULL',
            default => '',
        };

        // If nullable is true, prefer SET NULL; otherwise CASCADE.
        if ($nullable && $onDelete === 'cascade') {
            $deleteClause = 'ON DELETE SET NULL';
        }

        DB::statement(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$constraint}`
             FOREIGN KEY (`{$column}`)
             REFERENCES `{$refTable}`(`{$refColumn}`)
             {$deleteClause}"
        );
    }
};

