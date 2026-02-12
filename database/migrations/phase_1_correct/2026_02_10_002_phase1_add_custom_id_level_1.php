<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1 - Level 1: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tables: plants, planting_locations, bins, task_series
     */
    public function up(): void
    {
        // PLANTS TABLE
        if (Schema::hasTable('plants') && !Schema::hasColumn('plants', 'plant_id')) {
            Schema::table('plants', function (Blueprint $table) {
                $table->string('plant_id', 36)->nullable()->unique('plants_plant_id_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_plant_type_id', 36)->nullable()->after('plant_type_id');
            });
        }

        // PLANTING_LOCATIONS TABLE
        if (Schema::hasTable('planting_locations') && !Schema::hasColumn('planting_locations', 'planting_location_id')) {
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->string('planting_location_id', 36)->nullable()->unique('planting_locations_planting_location_id_unq')->after('id');
            });
        }

        // BINS TABLE
        if (Schema::hasTable('bins') && !Schema::hasColumn('bins', 'bin_id')) {
            Schema::table('bins', function (Blueprint $table) {
                $table->string('bin_id', 36)->nullable()->unique('bins_bin_id_unq')->after('id');
                $table->string('new_warehouse_id', 36)->nullable()->after('warehouse_id');
            });
        }

        // TASK_SERIES TABLE
        if (Schema::hasTable('task_series') && !Schema::hasColumn('task_series', 'task_series_id')) {
            Schema::table('task_series', function (Blueprint $table) {
                $table->string('task_series_id', 36)->nullable()->unique('task_series_task_series_id_unq')->after('id');
                $table->string('new_template_id', 36)->nullable()->after('template_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plants')) {
            Schema::table('plants', function (Blueprint $table) {
                $table->dropColumn(['plant_id', 'new_planting_location_id', 'new_plant_type_id']);
            });
        }

        if (Schema::hasTable('planting_locations')) {
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->dropColumn(['planting_location_id']);
            });
        }

        if (Schema::hasTable('bins')) {
            Schema::table('bins', function (Blueprint $table) {
                $table->dropColumn(['bin_id', 'new_warehouse_id']);
            });
        }

        if (Schema::hasTable('task_series')) {
            Schema::table('task_series', function (Blueprint $table) {
                $table->dropColumn(['task_series_id', 'new_template_id']);
            });
        }

    }
};