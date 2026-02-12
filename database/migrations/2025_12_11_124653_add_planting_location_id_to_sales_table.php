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
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('buyer_contact')->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'planting_location_id')) {
                $table->dropForeign(['planting_location_id']);
                $table->dropColumn('planting_location_id');
            }
        });
    }
};
