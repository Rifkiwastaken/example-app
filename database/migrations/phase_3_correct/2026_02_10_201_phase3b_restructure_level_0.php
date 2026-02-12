<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3b - Level 0: Restructure Tables ===\n";
        echo "Tables: users, plant_types, warehouses, task_templates, landing_page_settings\n\n";
        
        // users: Restructure
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->primary('user_id');
        });

        // plant_types: Restructure
        Schema::table('plant_types', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('plant_types', function (Blueprint $table) {
            $table->primary('plant_type_id');
        });

        // warehouses: Restructure
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('responsible_person_id');
            $table->renameColumn('new_responsible_person_id', 'responsible_person_id');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->primary('warehouse_id');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign('responsible_person_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // task_templates: Restructure
        Schema::table('task_templates', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('task_templates', function (Blueprint $table) {
            $table->primary('task_template_id');
        });

        // landing_page_settings: Restructure
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->primary('landing_page_setting_id');
        });


        echo "\n✓ Level 0 restructure completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3b rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
    }
};