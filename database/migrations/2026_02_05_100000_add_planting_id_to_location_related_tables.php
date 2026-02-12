<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add planting_id column to tables that only have planting_location_id
     * This allows filtering data by specific planting record
     */
    public function up(): void
    {
        // Add planting_id to attachments table
        if (Schema::hasTable('attachments') && !Schema::hasColumn('attachments', 'planting_id')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->string('planting_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // Add planting_id to planting_location_notes table
        if (Schema::hasTable('planting_location_notes') && !Schema::hasColumn('planting_location_notes', 'planting_id')) {
            Schema::table('planting_location_notes', function (Blueprint $table) {
                $table->string('planting_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // Add planting_id to planting_location_photos table
        if (Schema::hasTable('planting_location_photos') && !Schema::hasColumn('planting_location_photos', 'planting_id')) {
            Schema::table('planting_location_photos', function (Blueprint $table) {
                $table->string('planting_id', 36)->nullable()->after('planting_location_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('attachments', 'planting_id')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropColumn('planting_id');
            });
        }

        if (Schema::hasColumn('planting_location_notes', 'planting_id')) {
            Schema::table('planting_location_notes', function (Blueprint $table) {
                $table->dropColumn('planting_id');
            });
        }

        if (Schema::hasColumn('planting_location_photos', 'planting_id')) {
            Schema::table('planting_location_photos', function (Blueprint $table) {
                $table->dropColumn('planting_id');
            });
        }
    }
};
