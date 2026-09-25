<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_lots') || !Schema::hasColumn('warehouse_lots', 'production_id')) {
            return;
        }

        // Normalisasi nilai agar constraint unik konsisten.
        DB::statement("UPDATE warehouse_lots SET production_id = TRIM(production_id) WHERE production_id IS NOT NULL");

        $duplicates = DB::table('warehouse_lots')
            ->select('inventory_type_id', 'production_id', DB::raw('COUNT(*) as c'))
            ->whereNotNull('production_id')
            ->groupBy('inventory_type_id', 'production_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        // Tambahkan unique key hanya jika data saat ini sudah valid.
        if ($duplicates === 0) {
            Schema::table('warehouse_lots', function (Blueprint $table) {
                $table->unique(['inventory_type_id', 'production_id'], 'warehouse_lots_inv_type_prod_unique');
            });
        } else {
            Schema::table('warehouse_lots', function (Blueprint $table) {
                $table->index(['inventory_type_id', 'production_id'], 'warehouse_lots_inv_type_prod_idx');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('warehouse_lots')) {
            return;
        }

        Schema::table('warehouse_lots', function (Blueprint $table) {
            try {
                $table->dropUnique('warehouse_lots_inv_type_prod_unique');
            } catch (\Throwable $e) {
                // no-op
            }

            try {
                $table->dropIndex('warehouse_lots_inv_type_prod_idx');
            } catch (\Throwable $e) {
                // no-op
            }
        });
    }
};

