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
        Schema::create('user_planting_location_land_manager', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['planting_location_id', 'user_id'], 'planting_location_land_manager_user_unique');
        });

        Schema::create('user_planting_location_land_worker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['planting_location_id', 'user_id'], 'planting_location_land_worker_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_planting_location_land_worker');
        Schema::dropIfExists('user_planting_location_land_manager');
    }
};




