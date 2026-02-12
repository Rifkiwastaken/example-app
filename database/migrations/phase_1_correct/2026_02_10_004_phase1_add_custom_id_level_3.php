<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1 - Level 3: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tables: harvests, inventory_lots, certification_reports, inventory_type_seeds, tasks, sales
     */
    public function up(): void
    {
        // HARVESTS TABLE
        if (Schema::hasTable('harvests') && !Schema::hasColumn('harvests', 'harvest_id')) {
            Schema::table('harvests', function (Blueprint $table) {
                $table->string('harvest_id', 36)->nullable()->unique('harvests_harvest_id_unq')->after('id');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_recorded_by', 36)->nullable()->after('recorded_by');
            });
        }

        // INVENTORY_LOTS TABLE
        if (Schema::hasTable('inventory_lots') && !Schema::hasColumn('inventory_lots', 'inventory_lot_id')) {
            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->string('inventory_lot_id', 36)->nullable()->unique('inventory_lots_inventory_lot_id_unq')->after('id');
                $table->string('new_bin_id', 36)->nullable()->after('bin_id');
                $table->string('new_certification_id', 36)->nullable()->after('certification_id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_warehouse_id', 36)->nullable()->after('warehouse_id');
            });
        }

        // CERTIFICATION_REPORTS TABLE
        if (Schema::hasTable('certification_reports') && !Schema::hasColumn('certification_reports', 'certification_report_id')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                $table->string('certification_report_id', 36)->nullable()->unique('certification_report_certification_report_unq')->after('id');
                $table->string('new_certification_id', 36)->nullable()->after('certification_id');
            });
        }

        // INVENTORY_TYPE_SEEDS TABLE
        if (Schema::hasTable('inventory_type_seeds') && !Schema::hasColumn('inventory_type_seeds', 'inventory_type_seed_id')) {
            Schema::table('inventory_type_seeds', function (Blueprint $table) {
                $table->string('inventory_type_seed_id', 36)->nullable()->unique('inventory_type_seeds_inventory_type_seed__unq')->after('id');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
                $table->string('new_certification_report_id', 36)->nullable()->after('certification_report_id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_filled_by_user_id', 36)->nullable()->after('filled_by_user_id');
            });
        }

        // TASKS TABLE
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'task_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('task_id', 36)->nullable()->unique('tasks_task_id_unq')->after('id');
                $table->string('new_assigned_to', 36)->nullable()->after('assigned_to');
                $table->string('new_created_by', 36)->nullable()->after('created_by');
                $table->string('new_last_edited_by', 36)->nullable()->after('last_edited_by');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_series_id', 36)->nullable()->after('series_id');
                $table->string('new_template_id', 36)->nullable()->after('template_id');
            });
        }

        // SALES TABLE
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'sale_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('sale_id', 36)->nullable()->unique('sales_sale_id_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('harvests')) {
            Schema::table('harvests', function (Blueprint $table) {
                $table->dropColumn(['harvest_id', 'new_edited_by', 'new_planting_id', 'new_planting_location_id', 'new_plant_id', 'new_recorded_by']);
            });
        }

        if (Schema::hasTable('inventory_lots')) {
            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->dropColumn(['inventory_lot_id', 'new_bin_id', 'new_certification_id', 'new_inventory_type_id', 'new_warehouse_id']);
            });
        }

        if (Schema::hasTable('certification_reports')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                $table->dropColumn(['certification_report_id', 'new_certification_id']);
            });
        }

        if (Schema::hasTable('inventory_type_seeds')) {
            Schema::table('inventory_type_seeds', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_seed_id', 'new_edited_by', 'new_certification_report_id', 'new_inventory_type_id', 'new_planting_location_id', 'new_plant_id', 'new_filled_by_user_id']);
            });
        }

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn(['task_id', 'new_assigned_to', 'new_created_by', 'new_last_edited_by', 'new_planting_id', 'new_planting_location_id', 'new_series_id', 'new_template_id']);
            });
        }

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn(['sale_id', 'new_planting_location_id', 'new_user_id']);
            });
        }

    }
};