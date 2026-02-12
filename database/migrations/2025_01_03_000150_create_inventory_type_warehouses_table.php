<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('inventory_type_warehouses')) {
            Schema::create('inventory_type_warehouses', function (Blueprint $table) {
                $table->string('inventory_type_warehouse_id', 36)->primary();
                $table->string('inventory_type_id', 36)->onDelete('cascade');
                $table->string('warehouse_id', 36)->onDelete('cascade');
                $table->string('bin_id', 36)->nullable()->onDelete('cascade');
                $table->boolean('warehouse_only')->default(false); // Jika true, hanya di lokasi gudang (tanpa bin)
                $table->timestamps();

                $table->unique(['inventory_type_id', 'warehouse_id', 'bin_id'], 'inv_type_wh_bin_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_type_warehouses');
    }
};

