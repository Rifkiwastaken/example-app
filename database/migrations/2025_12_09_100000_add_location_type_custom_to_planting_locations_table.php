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
        Schema::table('planting_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('planting_locations', 'location_type_custom')) {
                $table->string('location_type_custom')->nullable()->after('location_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planting_locations', function (Blueprint $table) {
            if (Schema::hasColumn('planting_locations', 'location_type_custom')) {
                $table->dropColumn('location_type_custom');
            }
        });
    }
};

