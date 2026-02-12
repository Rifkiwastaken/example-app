<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->string('harvest_id', 36)->primary();
            $table->string('plant_id', 36)->cascadeOnDelete();
            $table->string('planting_id', 36)->nullable()->nullOnDelete();
            $table->string('planting_location_id', 36)->nullable()->nullOnDelete();
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


















