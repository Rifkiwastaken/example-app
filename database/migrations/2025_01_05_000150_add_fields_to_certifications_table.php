<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            if (!Schema::hasColumn('certifications', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('harvest_id')->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('set null');
            }
            if (!Schema::hasColumn('certifications', 'plant_id')) {
                $table->string('plant_id', 36)->nullable()->after('planting_location_id')->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            if (Schema::hasColumn('certifications', 'planting_location_id')) {
                $table->dropForeign(['planting_location_id']);
                $table->dropColumn('planting_location_id');
            }
            if (Schema::hasColumn('certifications', 'plant_id')) {
                $table->dropForeign(['plant_id']);
                $table->dropColumn('plant_id');
            }
        });
    }
};












