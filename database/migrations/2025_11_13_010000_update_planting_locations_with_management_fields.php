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
            $table->string('location_summary')->nullable()->after('location_id');
            $table->text('administrative_address')->nullable()->after('location_summary');
            $table->string('google_maps_link')->nullable()->after('administrative_address');

            $table->string('land_status')->nullable()->after('description');
            $table->string('ownership_status')->nullable()->after('land_status');

            $table->string('water_source')->nullable()->after('ownership_status');
            $table->string('soil_type')->nullable()->after('water_source');
            $table->integer('elevation_masl')->nullable()->after('soil_type');

            $table->string('planting_format_custom')->nullable()->after('planting_format');
            $table->string('primary_photo_path')->nullable()->after('google_maps_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planting_locations', function (Blueprint $table) {
            $table->dropColumn([
                'location_summary',
                'administrative_address',
                'google_maps_link',
                'primary_photo_path',
                'land_status',
                'ownership_status',
                'water_source',
                'soil_type',
                'elevation_masl',
                'planting_format_custom',
            ]);
        });
    }
};










