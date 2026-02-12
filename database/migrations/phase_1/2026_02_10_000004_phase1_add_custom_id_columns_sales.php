<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1: Menambahkan kolom custom ID baru untuk tabel Sales.
     */
    public function up(): void
    {
        // 1. SALES TABLE
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'sale_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('sale_id', 36)->nullable()->unique()->after('id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 2. SALE_ITEMS TABLE
        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'sale_item_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->string('sale_item_id', 36)->nullable()->unique()->after('id');
                $table->string('new_sale_id', 36)->nullable()->after('sale_id');
                $table->string('new_inventory_lot_id', 36)->nullable()->after('inventory_lot_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn(['sale_item_id', 'new_sale_id', 'new_inventory_lot_id']);
            });
        }

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn(['sale_id', 'new_user_id']);
            });
        }
    }
};
