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
        if (!Schema::hasTable('production_targets')) {
            Schema::create('production_targets', function (Blueprint $table) {
                $table->id();
                $table->year('fiscal_year'); // Tahun Anggaran
                $table->string('commodity'); // Komoditas (Padi, Palawija, Hortikultura)
                $table->string('variety_name'); // Uraian / Varietas
                $table->enum('seed_class', ['BS', 'BP', 'BR']); // Kelas Benih (BS, BP, BR)
                $table->foreignId('planting_location_id')->nullable()->constrained('planting_locations')->nullOnDelete(); // Lokasi Kebun/Balai
                $table->decimal('target_planting_area', 10, 2); // Target Luas Tanam (Ha)
                $table->decimal('target_production_volume', 10, 2); // Target Volume Produksi (Ton)
                $table->decimal('estimated_productivity', 10, 2)->nullable(); // Estimasi Produktivitas (Ton/Ha)
                $table->decimal('realized_planting_area', 10, 2)->default(0); // Realisasi Tanam (Ha) - dihitung dari data tanam
                $table->decimal('realized_production_volume', 10, 2)->default(0); // Realisasi Produksi (Ton) - dihitung dari data panen
                $table->text('notes')->nullable(); // Keterangan
                $table->timestamps();
                
                $table->index(['fiscal_year', 'commodity', 'planting_location_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_targets');
    }
};
