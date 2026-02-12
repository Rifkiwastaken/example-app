<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('planting_location_id')->nullable()->constrained('planting_locations')->nullOnDelete();
            $table->string('bed_label')->nullable();
            // Detail Penanaman
            $table->unsignedInteger('days_to_emerge')->nullable(); // in days
            $table->string('spacing_between_plants')->nullable(); // e.g., 30 cm
            $table->string('spacing_between_rows')->nullable();
            $table->string('sowing_depth')->nullable();
            $table->string('avg_height')->nullable();
            $table->enum('start_method', ['tanam_langsung','baki_semai','pindahkan_ke_tanah','transplant','container','ditanam_di_baki_semai','batang_bawah','umbi','sambung_okulasi','lainnya'])->nullable();
            $table->enum('germination_stage', ['benih_ditanam','perkecambahan','bibit','sudah_ditanam','vegetatif','berbunga','pematangan_buah','selesai'])->nullable();
            $table->unsignedInteger('seeds_per_hole')->nullable();
            $table->enum('light_profile', ['matahari_penuh','matahari_penuh_sebagian','matahari_sebagian','matahari_setengah_teduh','setengah_teduh','teduh_sepenuhnya'])->nullable();
            $table->enum('soil_condition', ['berkapur','liat','lempung','gambut','berpasir','lanau'])->nullable();
            $table->text('planting_detail')->nullable();
            $table->text('pruning_detail')->nullable();
            $table->boolean('perennial')->default(false);
            // Panen target
            $table->unsignedInteger('days_to_flower')->nullable();
            $table->unsignedInteger('days_to_harvest')->nullable();
            $table->unsignedInteger('harvest_window_days')->nullable();
            $table->string('expected_loss_rate')->nullable();
            $table->enum('harvest_unit', ['ikat','barel','tandan','gantang','lusin','gram','batang','kilogram','kiloliter','liter','mililiter','satuan','ton'])->nullable();
            $table->decimal('expected_yield_per_hectare', 12, 2)->nullable();
            $table->unsignedInteger('quantity_planted')->nullable();
            $table->date('planted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantings');
    }
};


















