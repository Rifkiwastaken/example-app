<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3 - Level 0: Finalization ===\n";
        echo "Tables: users, plant_types, warehouses, task_templates, landing_page_settings\n\n";
        echo "⚠️  WARNING: This will drop old ID columns and restructure tables!\n";
        echo "Make sure Phase 2 completed successfully before proceeding.\n\n";
        
        // users: Finalize structure
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->primary('user_id');
        });

        // plant_types: Finalize structure
        Schema::table('plant_types', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('plant_types', function (Blueprint $table) {
            $table->primary('plant_type_id');
        });

        // warehouses: Finalize structure
        Schema::table('warehouses', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
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

        // task_templates: Finalize structure
        Schema::table('task_templates', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('task_templates', function (Blueprint $table) {
            $table->primary('task_template_id');
        });

        // landing_page_settings: Finalize structure
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->primary('landing_page_setting_id');
        });


        echo "\n✓ Level 0 finalization completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3 rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
        
        // Reversing users is complex - restore from backup if needed
        // Schema::table('users', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing plant_types is complex - restore from backup if needed
        // Schema::table('plant_types', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing warehouses is complex - restore from backup if needed
        // Schema::table('warehouses', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing task_templates is complex - restore from backup if needed
        // Schema::table('task_templates', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing landing_page_settings is complex - restore from backup if needed
        // Schema::table('landing_page_settings', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

    }
};