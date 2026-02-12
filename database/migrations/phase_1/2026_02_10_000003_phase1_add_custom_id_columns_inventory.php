<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1: Menambahkan kolom custom ID baru untuk tabel Inventory & Warehouse.
     */
    public function up(): void
    {
        // 1. WAREHOUSES TABLE
        if (Schema::hasTable('warehouses') && !Schema::hasColumn('warehouses', 'warehouse_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->string('warehouse_id', 36)->nullable()->unique()->after('id');
            });
        }

        // 2. BINS TABLE
        if (Schema::hasTable('bins') && !Schema::hasColumn('bins', 'bin_id')) {
            Schema::table('bins', function (Blueprint $table) {
                $table->string('bin_id', 36)->nullable()->unique()->after('id');
                $table->string('new_warehouse_id', 36)->nullable()->after('warehouse_id');
            });
        }

        // 3. INVENTORY_TYPES TABLE
        if (Schema::hasTable('inventory_types') && !Schema::hasColumn('inventory_types', 'inventory_type_id')) {
            Schema::table('inventory_types', function (Blueprint $table) {
                $table->string('inventory_type_id', 36)->nullable()->unique()->after('id');
            });
        }

        // 4. INVENTORY_LOTS TABLE
        if (Schema::hasTable('inventory_lots') && !Schema::hasColumn('inventory_lots', 'inventory_lot_id')) {
            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->string('inventory_lot_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_bin_id', 36)->nullable()->after('bin_id');
            });
        }

        // 5. INVENTORY_TRANSACTIONS TABLE
        if (Schema::hasTable('inventory_transactions') && !Schema::hasColumn('inventory_transactions', 'inventory_transaction_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->string('inventory_transaction_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_lot_id', 36)->nullable()->after('inventory_lot_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 6. INVENTORY_TYPE_WAREHOUSES TABLE
        if (Schema::hasTable('inventory_type_warehouses') && !Schema::hasColumn('inventory_type_warehouses', 'inventory_type_warehouse_id')) {
            Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                $table->string('inventory_type_warehouse_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_warehouse_id', 36)->nullable()->after('warehouse_id');
            });
        }

        // 7. INVENTORY_TYPE_SEEDS TABLE
        if (Schema::hasTable('inventory_type_seeds') && !Schema::hasColumn('inventory_type_seeds', 'inventory_type_seed_id')) {
            Schema::table('inventory_type_seeds', function (Blueprint $table) {
                $table->string('inventory_type_seed_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_plant_type_id', 36)->nullable()->after('plant_type_id');
            });
        }

        // 8. INVENTORY_TYPE_CERTIFICATION_REPORTS TABLE
        if (Schema::hasTable('inventory_type_certification_reports') && !Schema::hasColumn('inventory_type_certification_reports', 'inventory_type_certification_report_id')) {
            Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                $table->string('inventory_type_certification_report_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_certification_report_id', 36)->nullable()->after('certification_report_id');
            });
        }

        // 9. INVENTORY_NOTES TABLE
        if (Schema::hasTable('inventory_notes') && !Schema::hasColumn('inventory_notes', 'inventory_note_id')) {
            Schema::table('inventory_notes', function (Blueprint $table) {
                $table->string('inventory_note_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_lot_id', 36)->nullable()->after('inventory_lot_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 10. INVENTORY_PHOTOS TABLE
        if (Schema::hasTable('inventory_photos') && !Schema::hasColumn('inventory_photos', 'inventory_photo_id')) {
            Schema::table('inventory_photos', function (Blueprint $table) {
                $table->string('inventory_photo_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_lot_id', 36)->nullable()->after('inventory_lot_id');
            });
        }

        // 11. SEED_HISTORIES TABLE
        if (Schema::hasTable('seed_histories') && !Schema::hasColumn('seed_histories', 'seed_history_id')) {
            Schema::table('seed_histories', function (Blueprint $table) {
                $table->string('seed_history_id', 36)->nullable()->unique()->after('id');
                $table->string('new_inventory_type_seed_id', 36)->nullable()->after('inventory_type_seed_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('seed_histories')) {
            Schema::table('seed_histories', function (Blueprint $table) {
                $table->dropColumn(['seed_history_id', 'new_inventory_type_seed_id']);
            });
        }

        if (Schema::hasTable('inventory_photos')) {
            Schema::table('inventory_photos', function (Blueprint $table) {
                $table->dropColumn(['inventory_photo_id', 'new_inventory_lot_id']);
            });
        }

        if (Schema::hasTable('inventory_notes')) {
            Schema::table('inventory_notes', function (Blueprint $table) {
                $table->dropColumn(['inventory_note_id', 'new_inventory_lot_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('inventory_type_certification_reports')) {
            Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_certification_report_id', 'new_inventory_type_id', 'new_certification_report_id']);
            });
        }

        if (Schema::hasTable('inventory_type_seeds')) {
            Schema::table('inventory_type_seeds', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_seed_id', 'new_inventory_type_id', 'new_plant_type_id']);
            });
        }

        if (Schema::hasTable('inventory_type_warehouses')) {
            Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_warehouse_id', 'new_inventory_type_id', 'new_warehouse_id']);
            });
        }

        if (Schema::hasTable('inventory_transactions')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->dropColumn(['inventory_transaction_id', 'new_inventory_lot_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('inventory_lots')) {
            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->dropColumn(['inventory_lot_id', 'new_inventory_type_id', 'new_bin_id']);
            });
        }

        if (Schema::hasTable('inventory_types')) {
            Schema::table('inventory_types', function (Blueprint $table) {
                $table->dropColumn('inventory_type_id');
            });
        }

        if (Schema::hasTable('bins')) {
            Schema::table('bins', function (Blueprint $table) {
                $table->dropColumn(['bin_id', 'new_warehouse_id']);
            });
        }

        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
