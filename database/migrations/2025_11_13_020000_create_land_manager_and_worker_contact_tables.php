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
        if (!Schema::hasTable('contact_planting_location_land_manager')) {
            Schema::create('contact_planting_location_land_manager', function (Blueprint $table) {
                $table->id();
                $table->foreignId('planting_location_id')->constrained('planting_locations')->cascadeOnDelete();
                $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['planting_location_id', 'contact_id'], 'p_loc_land_mgr_contact_uniq');
            });
        }

        if (!Schema::hasTable('contact_planting_location_land_worker')) {
            Schema::create('contact_planting_location_land_worker', function (Blueprint $table) {
                $table->id();
                $table->foreignId('planting_location_id')->constrained('planting_locations')->cascadeOnDelete();
                $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['planting_location_id', 'contact_id'], 'p_loc_land_wkr_contact_uniq');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_planting_location_land_worker');
        Schema::dropIfExists('contact_planting_location_land_manager');
    }
};

