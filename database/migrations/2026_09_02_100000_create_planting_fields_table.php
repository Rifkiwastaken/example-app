<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('planting_fields')) {
            return;
        }

        Schema::create('planting_fields', function (Blueprint $table) {
            $table->id();
            $table->string('planting_location_id', 36);
            $table->string('kode_lahan', 20);
            $table->decimal('luas_ha', 5, 2);
            $table->string('koordinat_gps', 100)->nullable();
            $table->enum('status_lahan', ['Digunakan', 'Bera', 'Persiapan']);
            $table->timestamps();

            $table->unique(['planting_location_id', 'kode_lahan'], 'planting_fields_location_kode_unique');
            $table->foreign('planting_location_id')
                ->references('planting_location_id')
                ->on('planting_locations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_fields');
    }
};
