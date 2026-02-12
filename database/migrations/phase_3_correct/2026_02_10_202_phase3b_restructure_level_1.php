<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3b - Level 1: Restructure Tables ===\n";
        echo "Tables: plants, planting_locations, bins, task_series\n\n";
        
        // plants: Restructure
        Schema::table('plants', function (Blueprint $table) {
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

        // planting_locations: Restructure
        Schema::table('planting_locations', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('planting_locations', function (Blueprint $table) {
            $table->primary('planting_location_id');
        });

        // bins: Restructure
        Schema::table('bins', function (Blueprint $table) {
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

        // task_series: Restructure
        Schema::table('task_series', function (Blueprint $table) {
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


        echo "\n✓ Level 1 restructure completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3b rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
    }
};