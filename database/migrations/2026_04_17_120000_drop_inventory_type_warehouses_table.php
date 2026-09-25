<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus pivot tipe inventaris ↔ gudang (diganti penempatan lewat lot di gudang).
     */
    public function up(): void
    {
        $tables = array_values(array_filter(
            ['inventory_type_warehouses', 'inventory_types_warehouse'],
            fn (string $table) => Schema::hasTable($table)
        ));

        if ($tables === []) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            Schema::drop($table);
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_type_warehouses')) {
            return;
        }

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
};
