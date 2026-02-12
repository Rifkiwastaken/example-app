<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1 - Level 2: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tables: plantings, inventory_types, certifications
     */
    public function up(): void
    {
        // PLANTINGS TABLE
        if (Schema::hasTable('plantings') && !Schema::hasColumn('plantings', 'planting_id')) {
            Schema::table('plantings', function (Blueprint $table) {
                $table->string('planting_id', 36)->nullable()->unique('plantings_planting_id_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
            });
        }

        // INVENTORY_TYPES TABLE
        if (Schema::hasTable('inventory_types') && !Schema::hasColumn('inventory_types', 'inventory_type_id')) {
            Schema::table('inventory_types', function (Blueprint $table) {
                $table->string('inventory_type_id', 36)->nullable()->unique('inventory_types_inventory_type_id_unq')->after('id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_responsible_person_id', 36)->nullable()->after('responsible_person_id');
            });
        }

        // CERTIFICATIONS TABLE
        if (Schema::hasTable('certifications') && !Schema::hasColumn('certifications', 'certification_id')) {
            Schema::table('certifications', function (Blueprint $table) {
                $table->string('certification_id', 36)->nullable()->unique('certifications_certification_id_unq')->after('id');
                $table->string('new_harvest_id', 36)->nullable()->after('harvest_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plantings')) {
            Schema::table('plantings', function (Blueprint $table) {
                $table->dropColumn(['planting_id', 'new_planting_location_id', 'new_plant_id']);
            });
        }

        if (Schema::hasTable('inventory_types')) {
            Schema::table('inventory_types', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_id', 'new_plant_id', 'new_responsible_person_id']);
            });
        }

        if (Schema::hasTable('certifications')) {
            Schema::table('certifications', function (Blueprint $table) {
                $table->dropColumn(['certification_id', 'new_harvest_id', 'new_planting_location_id', 'new_plant_id']);
            });
        }

    }
};