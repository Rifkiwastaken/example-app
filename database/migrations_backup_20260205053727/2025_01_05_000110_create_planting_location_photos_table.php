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
        if (!Schema::hasTable('planting_location_photos')) {
            Schema::create('planting_location_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('planting_location_id')->constrained('planting_locations')->onDelete('cascade');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->integer('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->text('description')->nullable();
                $table->date('taken_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_location_photos');
    }
};

