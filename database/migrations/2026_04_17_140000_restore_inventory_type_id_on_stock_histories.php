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

        if (!Schema::hasColumn('stock_histories', 'inventory_type_id')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                $table->string('inventory_type_id', 36)->nullable()->after('stock_history_id');
            });
        }

        // Pulihkan nilai inventory_type_id dari lot agar histori lama kembali berfungsi.
        DB::statement(
            "UPDATE stock_histories sh
             INNER JOIN warehouse_lots wl ON wl.warehouse_lot_id = sh.warehouse_lot_id
             SET sh.inventory_type_id = wl.inventory_type_id
             WHERE sh.inventory_type_id IS NULL
               AND sh.warehouse_lot_id IS NOT NULL
               AND wl.inventory_type_id IS NOT NULL"
        );

        if ($this->hasIndex('stock_histories', 'stock_histories_inventory_type_id_index') === false) {
            Schema::table('stock_histories', function (Blueprint $table) {
                $table->index('inventory_type_id', 'stock_histories_inventory_type_id_index');
            });
        }

        $this->dropForeignKeysForColumn('stock_histories', 'inventory_type_id');

        Schema::table('stock_histories', function (Blueprint $table) {
            $table->foreign('inventory_type_id', 'stock_histories_inventory_type_id_foreign')
                ->references('inventory_type_id')
                ->on('inventory_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_histories') || !Schema::hasColumn('stock_histories', 'inventory_type_id')) {
            return;
        }

        $this->dropForeignKeysForColumn('stock_histories', 'inventory_type_id');

        if ($this->hasIndex('stock_histories', 'stock_histories_inventory_type_id_index')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                $table->dropIndex('stock_histories_inventory_type_id_index');
            });
        }

        Schema::table('stock_histories', function (Blueprint $table) {
            $table->dropColumn('inventory_type_id');
        });
    }

    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $database = DB::getDatabaseName();
        $foreignKeys = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME');

        foreach ($foreignKeys as $foreignKey) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKey}`");
            } catch (\Throwable $e) {
                // Abaikan jika FK sudah tidak ada.
            }
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};

