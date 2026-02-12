<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3 - Level 1: Finalization ===\n";
        echo "Tables: plants, planting_locations, bins, task_series\n\n";
        echo "⚠️  WARNING: This will drop old ID columns and restructure tables!\n";
        echo "Make sure Phase 2 completed successfully before proceeding.\n\n";
        
        // plants: Finalize structure
        Schema::table('plants', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('plant_type_id');
            $table->renameColumn('new_plant_type_id', 'plant_type_id');
        });

        Schema::table('plants', function (Blueprint $table) {
            $table->primary('plant_id');
        });

        Schema::table('plants', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('plant_type_id')->references('plant_type_id')->on('plant_types')->onDelete('cascade');
        });

        // planting_locations: Finalize structure
        Schema::table('planting_locations', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('planting_locations', function (Blueprint $table) {
            $table->primary('planting_location_id');
        });

        // bins: Finalize structure
        Schema::table('bins', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('warehouse_id');
            $table->renameColumn('new_warehouse_id', 'warehouse_id');
        });

        Schema::table('bins', function (Blueprint $table) {
            $table->primary('bin_id');
        });

        Schema::table('bins', function (Blueprint $table) {
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses')->onDelete('cascade');
        });

        // task_series: Finalize structure
        Schema::table('task_series', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('template_id');
            $table->renameColumn('new_template_id', 'template_id');
        });

        Schema::table('task_series', function (Blueprint $table) {
            $table->primary('task_series_id');
        });

        Schema::table('task_series', function (Blueprint $table) {
            $table->foreign('template_id')->references('task_template_id')->on('task_templates')->onDelete('cascade');
        });


        echo "\n✓ Level 1 finalization completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3 rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
        
        // Reversing plants is complex - restore from backup if needed
        // Schema::table('plants', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing planting_locations is complex - restore from backup if needed
        // Schema::table('planting_locations', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing bins is complex - restore from backup if needed
        // Schema::table('bins', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing task_series is complex - restore from backup if needed
        // Schema::table('task_series', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

    }
};