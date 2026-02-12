<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plants')) {
            Schema::create('plants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('plant_type_id')->nullable()->constrained('plant_types')->nullOnDelete();
                $table->string('variety')->nullable();
                $table->enum('status', ['perencanaan', 'ditanam', 'dipanen', 'selesai'])->default('perencanaan');
                $table->unsignedTinyInteger('progress')->default(0); // 0..100
                // defer FK to planting_locations until table exists; keep column for now
                $table->foreignId('planting_location_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
