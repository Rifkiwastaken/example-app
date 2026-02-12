<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3 - Level 3: Finalization ===\n";
        echo "Tables: harvests, inventory_lots, certification_reports, inventory_type_seeds, tasks, sales\n\n";
        echo "⚠️  WARNING: This will drop old ID columns and restructure tables!\n";
        echo "Make sure Phase 2 completed successfully before proceeding.\n\n";
        
        // harvests: Finalize structure
        Schema::table('harvests', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('edited_by');
            $table->renameColumn('new_edited_by', 'edited_by');
            $table->dropColumn('planting_id');
            $table->renameColumn('new_planting_id', 'planting_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
            $table->dropColumn('recorded_by');
            $table->renameColumn('new_recorded_by', 'recorded_by');
        });

        Schema::table('harvests', function (Blueprint $table) {
            $table->primary('harvest_id');
        });

        Schema::table('harvests', function (Blueprint $table) {
            $table->foreign('edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
            $table->foreign('recorded_by')->references('user_id')->on('users')->onDelete('cascade');
        });

        // inventory_lots: Finalize structure
        Schema::table('inventory_lots', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('bin_id');
            $table->renameColumn('new_bin_id', 'bin_id');
            $table->dropColumn('certification_id');
            $table->renameColumn('new_certification_id', 'certification_id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('warehouse_id');
            $table->renameColumn('new_warehouse_id', 'warehouse_id');
        });

        Schema::table('inventory_lots', function (Blueprint $table) {
            $table->primary('inventory_lot_id');
        });

        Schema::table('inventory_lots', function (Blueprint $table) {
            $table->foreign('bin_id')->references('bin_id')->on('bins')->onDelete('cascade');
            $table->foreign('certification_id')->references('certification_id')->on('certifications')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses')->onDelete('cascade');
        });

        // certification_reports: Finalize structure
        Schema::table('certification_reports', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('certification_id');
            $table->renameColumn('new_certification_id', 'certification_id');
        });

        Schema::table('certification_reports', function (Blueprint $table) {
            $table->primary('certification_report_id');
        });

        Schema::table('certification_reports', function (Blueprint $table) {
            $table->foreign('certification_id')->references('certification_id')->on('certifications')->onDelete('cascade');
        });

        // inventory_type_seeds: Finalize structure
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('edited_by');
            $table->renameColumn('new_edited_by', 'edited_by');
            $table->dropColumn('certification_report_id');
            $table->renameColumn('new_certification_report_id', 'certification_report_id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
            $table->dropColumn('filled_by_user_id');
            $table->renameColumn('new_filled_by_user_id', 'filled_by_user_id');
        });

        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            $table->primary('inventory_type_seed_id');
        });

        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            $table->foreign('edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('certification_report_id')->references('certification_report_id')->on('certification_reports')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
            $table->foreign('filled_by_user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // tasks: Finalize structure
        Schema::table('tasks', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('assigned_to');
            $table->renameColumn('new_assigned_to', 'assigned_to');
            $table->dropColumn('created_by');
            $table->renameColumn('new_created_by', 'created_by');
            $table->dropColumn('last_edited_by');
            $table->renameColumn('new_last_edited_by', 'last_edited_by');
            $table->dropColumn('planting_id');
            $table->renameColumn('new_planting_id', 'planting_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('series_id');
            $table->renameColumn('new_series_id', 'series_id');
            $table->dropColumn('template_id');
            $table->renameColumn('new_template_id', 'template_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->primary('task_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('last_edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('series_id')->references('task_series_id')->on('task_series')->onDelete('cascade');
            $table->foreign('template_id')->references('task_template_id')->on('task_templates')->onDelete('cascade');
        });

        // sales: Finalize structure
        Schema::table('sales', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->primary('sale_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });


        echo "\n✓ Level 3 finalization completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3 rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
        
        // Reversing harvests is complex - restore from backup if needed
        // Schema::table('harvests', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_lots is complex - restore from backup if needed
        // Schema::table('inventory_lots', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing certification_reports is complex - restore from backup if needed
        // Schema::table('certification_reports', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_type_seeds is complex - restore from backup if needed
        // Schema::table('inventory_type_seeds', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing tasks is complex - restore from backup if needed
        // Schema::table('tasks', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing sales is complex - restore from backup if needed
        // Schema::table('sales', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

    }
};