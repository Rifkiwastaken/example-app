<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('planting_id')->nullable()->constrained('plantings')->nullOnDelete();
            $table->foreignId('planting_location_id')->nullable()->constrained('planting_locations')->nullOnDelete();
            $table->date('harvested_at');
            $table->string('batch_no')->nullable();
            $table->text('note')->nullable();
            $table->string('source')->nullable(); // from which bed/location
            $table->string('quality')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('loss_quantity', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};















