<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3a: Drop ALL Foreign Key Constraints ===\n";
        echo "This is necessary before we can drop old ID columns.\n\n";
        
        // Disable FK checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // inventory_transactions: Drop FK constraints
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign('inventory_transactions_bin_id_foreign');
            $table->dropForeign('inventory_transactions_inventory_lot_id_foreign');
            $table->dropForeign('inventory_transactions_inventory_type_id_foreign');
            $table->dropForeign('inventory_transactions_user_id_foreign');
            $table->dropForeign('inventory_transactions_warehouse_id_foreign');
        });

        // inventory_type_warehouses: Drop FK constraints
        Schema::table('inventory_type_warehouses', function (Blueprint $table) {
            $table->dropForeign('inventory_type_warehouses_bin_id_foreign');
            $table->dropForeign('inventory_type_warehouses_inventory_type_id_foreign');
            $table->dropForeign('inventory_type_warehouses_warehouse_id_foreign');
        });

        // inventory_type_certification_reports: Drop FK constraints
        Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
            $table->dropForeign('inventory_type_certification_reports_certification_report_id_foreign');
            $table->dropForeign('inventory_type_certification_reports_inventory_type_id_foreign');
        });

        // sale_items: Drop FK constraints
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign('sale_items_inventory_lot_id_foreign');
            $table->dropForeign('sale_items_inventory_type_id_foreign');
            $table->dropForeign('sale_items_sale_id_foreign');
        });

        // expenses: Drop FK constraints
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign('expenses_edited_by_foreign');
            $table->dropForeign('expenses_nutrient_id_foreign');
            $table->dropForeign('expenses_planting_id_foreign');
            $table->dropForeign('expenses_planting_location_id_foreign');
            $table->dropForeign('expenses_responsible_person_id_foreign');
            $table->dropForeign('expenses_treatment_id_foreign');
        });

        // nutrients: Drop FK constraints
        Schema::table('nutrients', function (Blueprint $table) {
            $table->dropForeign('nutrients_edited_by_foreign');
            $table->dropForeign('nutrients_planting_id_foreign');
            $table->dropForeign('nutrients_planting_location_id_foreign');
            $table->dropForeign('nutrients_responsible_person_id_foreign');
        });

        // treatments: Drop FK constraints
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign('treatments_edited_by_foreign');
            $table->dropForeign('treatments_planting_id_foreign');
            $table->dropForeign('treatments_planting_location_id_foreign');
            $table->dropForeign('treatments_responsible_person_id_foreign');
        });

        // attachments: Drop FK constraints
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign('attachments_created_by_foreign');
            $table->dropForeign('attachments_edited_by_foreign');
            $table->dropForeign('attachments_planting_location_id_foreign');
        });

        // seed_histories: Drop FK constraints
        Schema::table('seed_histories', function (Blueprint $table) {
            $table->dropForeign('seed_histories_inventory_type_seed_id_foreign');
            $table->dropForeign('seed_histories_user_id_foreign');
        });

        // planting_losses: Drop FK constraints
        Schema::table('planting_losses', function (Blueprint $table) {
            $table->dropForeign('planting_losses_planting_id_foreign');
        });

        // plant_notes: Drop FK constraints
        Schema::table('plant_notes', function (Blueprint $table) {
            $table->dropForeign('plant_notes_plant_id_foreign');
        });

        // plant_photos: Drop FK constraints
        Schema::table('plant_photos', function (Blueprint $table) {
            $table->dropForeign('plant_photos_plant_id_foreign');
        });

        // planting_location_notes: Drop FK constraints
        Schema::table('planting_location_notes', function (Blueprint $table) {
            $table->dropForeign('planting_location_notes_planting_location_id_foreign');
            $table->dropForeign('planting_location_notes_user_id_foreign');
        });

        // planting_location_photos: Drop FK constraints
        Schema::table('planting_location_photos', function (Blueprint $table) {
            $table->dropForeign('planting_location_photos_planting_location_id_foreign');
        });

        // inventory_notes: Drop FK constraints
        Schema::table('inventory_notes', function (Blueprint $table) {
            $table->dropForeign('inventory_notes_inventory_type_id_foreign');
            $table->dropForeign('inventory_notes_user_id_foreign');
        });

        // inventory_photos: Drop FK constraints
        Schema::table('inventory_photos', function (Blueprint $table) {
            $table->dropForeign('inventory_photos_inventory_type_id_foreign');
            $table->dropForeign('inventory_photos_user_id_foreign');
        });

        // user_planting_location_land_manager: Drop FK constraints
        Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
            $table->dropForeign('user_planting_location_land_manager_planting_location_id_foreign');
            $table->dropForeign('user_planting_location_land_manager_user_id_foreign');
        });

        // user_planting_location_land_worker: Drop FK constraints
        Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
            $table->dropForeign('user_planting_location_land_worker_planting_location_id_foreign');
            $table->dropForeign('user_planting_location_land_worker_user_id_foreign');
        });

        // harvests: Drop FK constraints
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropForeign('harvests_edited_by_foreign');
            $table->dropForeign('harvests_planting_id_foreign');
            $table->dropForeign('harvests_planting_location_id_foreign');
            $table->dropForeign('harvests_plant_id_foreign');
            $table->dropForeign('harvests_recorded_by_foreign');
        });

        // inventory_lots: Drop FK constraints
        Schema::table('inventory_lots', function (Blueprint $table) {
            $table->dropForeign('inventory_lots_bin_id_foreign');
            $table->dropForeign('inventory_lots_certification_id_foreign');
            $table->dropForeign('inventory_lots_inventory_type_id_foreign');
            $table->dropForeign('inventory_lots_warehouse_id_foreign');
        });

        // certification_reports: Drop FK constraints
        Schema::table('certification_reports', function (Blueprint $table) {
            $table->dropForeign('certification_reports_certification_id_foreign');
        });

        // inventory_type_seeds: Drop FK constraints
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            $table->dropForeign('inventory_type_seeds_edited_by_foreign');
            $table->dropForeign('inventory_type_seeds_certification_report_id_foreign');
            $table->dropForeign('inventory_type_seeds_inventory_type_id_foreign');
            $table->dropForeign('inventory_type_seeds_planting_location_id_foreign');
            $table->dropForeign('inventory_type_seeds_plant_id_foreign');
            $table->dropForeign('inventory_type_seeds_filled_by_user_id_foreign');
        });

        // tasks: Drop FK constraints
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_assigned_to_foreign');
            $table->dropForeign('tasks_created_by_foreign');
            $table->dropForeign('tasks_last_edited_by_foreign');
            $table->dropForeign('tasks_planting_id_foreign');
            $table->dropForeign('tasks_planting_location_id_foreign');
            $table->dropForeign('tasks_series_id_foreign');
            $table->dropForeign('tasks_template_id_foreign');
        });

        // sales: Drop FK constraints
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_planting_location_id_foreign');
            $table->dropForeign('sales_user_id_foreign');
        });

        // plantings: Drop FK constraints
        Schema::table('plantings', function (Blueprint $table) {
            $table->dropForeign('plantings_planting_location_id_foreign');
            $table->dropForeign('plantings_plant_id_foreign');
        });

        // inventory_types: Drop FK constraints
        Schema::table('inventory_types', function (Blueprint $table) {
            $table->dropForeign('inventory_types_plant_id_foreign');
            $table->dropForeign('inventory_types_responsible_person_id_foreign');
        });

        // certifications: Drop FK constraints
        Schema::table('certifications', function (Blueprint $table) {
            $table->dropForeign('certifications_harvest_id_foreign');
            $table->dropForeign('certifications_planting_location_id_foreign');
            $table->dropForeign('certifications_plant_id_foreign');
        });

        // plants: Drop FK constraints
        Schema::table('plants', function (Blueprint $table) {
            $table->dropForeign('plants_planting_location_id_foreign');
            $table->dropForeign('plants_plant_type_id_foreign');
        });

        // bins: Drop FK constraints
        Schema::table('bins', function (Blueprint $table) {
            $table->dropForeign('bins_warehouse_id_foreign');
        });

        // task_series: Drop FK constraints
        Schema::table('task_series', function (Blueprint $table) {
            $table->dropForeign('task_series_template_id_foreign');
        });

        // warehouses: Drop FK constraints
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign('warehouses_responsible_person_id_foreign');
        });


        // Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        echo "\n✓ All FK constraints dropped!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  Rollback: FK constraints will be recreated in Phase 3b rollback\n\n";
    }
};