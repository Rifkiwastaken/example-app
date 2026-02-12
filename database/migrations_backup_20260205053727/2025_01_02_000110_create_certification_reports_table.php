<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certification_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained('certifications')->cascadeOnDelete();
            $table->string('report_number_bpsb')->nullable();
            $table->date('report_date');
            $table->string('growing_season')->nullable();
            $table->string('inspection_phase'); // Vegetatif, Generatif, Menjelang Panen, Lainnya
            $table->string('inspector_name')->nullable(); // Petugas Pengawas Mutu (BPSB)
            
            // Hasil Pemeriksaan
            $table->string('seed_class_result')->nullable(); // BS, BP, BR
            $table->string('isolation_north')->nullable();
            $table->string('isolation_east')->nullable();
            $table->string('isolation_south')->nullable();
            $table->string('isolation_west')->nullable();
            $table->boolean('plant_characteristics_match')->nullable(); // Ya/Tidak
            $table->text('pest_disease_condition')->nullable();
            $table->enum('weed_condition', ['Bersih', 'Cukup Bersih', 'Kotor'])->nullable();
            $table->integer('population_per_sample')->nullable();
            $table->integer('other_variety_mix_count')->nullable();
            $table->decimal('other_variety_mix_percentage', 5, 2)->nullable();
            $table->decimal('estimated_yield', 12, 2)->nullable();
            
            // Kesimpulan
            $table->enum('conclusion', ['LULUS', 'TIDAK LULUS'])->nullable();
            $table->string('scan_file_path')->nullable(); // Path untuk file scan laporan
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_reports');
    }
};














