<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_locations', function (Blueprint $table) {
            $table->string('planting_location_id', 36)->primary();
            $table->string('name'); // nama lahan
            $table->string('location_id', 36)->nullable()->nullOnDelete();
            $table->enum('location_type', ['lapangan', 'greenhouse', 'grow_room', 'padang_rumput', 'petak_ternak', 'lainnya'])->default('lapangan');
            $table->enum('planting_format', ['petak', 'cover_crop', 'row', 'lainnya'])->default('petak');
            // jika petak
            $table->unsignedInteger('num_beds')->nullable();
            $table->decimal('bed_length_m', 8, 2)->nullable();
            $table->decimal('bed_width_m', 8, 2)->nullable();
            $table->string('map_size')->nullable();
            $table->string('light_condition')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_locations');
    }
};


















