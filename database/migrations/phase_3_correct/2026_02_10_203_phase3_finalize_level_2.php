<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3 - Level 2: Finalization ===\n";
        echo "Tables: plantings, inventory_types, certifications\n\n";
        echo "⚠️  WARNING: This will drop old ID columns and restructure tables!\n";
        echo "Make sure Phase 2 completed successfully before proceeding.\n\n";
        
        // plantings: Finalize structure
        Schema::table('plantings', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
        });

        Schema::table('plantings', function (Blueprint $table) {
            $table->primary('planting_id');
        });

        Schema::table('plantings', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
        });

        // inventory_types: Finalize structure
        Schema::table('inventory_types', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
            $table->dropColumn('responsible_person_id');
            $table->renameColumn('new_responsible_person_id', 'responsible_person_id');
        });

        Schema::table('inventory_types', function (Blueprint $table) {
            $table->primary('inventory_type_id');
        });

        Schema::table('inventory_types', function (Blueprint $table) {
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
            $table->foreign('responsible_person_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // certifications: Finalize structure
        Schema::table('certifications', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('harvest_id');
            $table->renameColumn('new_harvest_id', 'harvest_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->primary('certification_id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->foreign('harvest_id')->references('harvest_id')->on('harvests')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
        });


        echo "\n✓ Level 2 finalization completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3 rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
        
        // Reversing plantings is complex - restore from backup if needed
        // Schema::table('plantings', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_types is complex - restore from backup if needed
        // Schema::table('inventory_types', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing certifications is complex - restore from backup if needed
        // Schema::table('certifications', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

    }
};