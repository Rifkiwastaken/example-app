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
        // Drop pivot tables first (they have foreign keys)
        Schema::dropIfExists('contact_planting_location_land_worker');
        Schema::dropIfExists('contact_planting_location_land_manager');
        Schema::dropIfExists('contact_planting_location');
        
        // Drop main contacts table
        Schema::dropIfExists('contacts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate contacts table
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('contact_type', [
                'pegawai_uptd_bbi_tpph',
                'pegawai_gudang',
                'petugas_sertifikasi',
                'petani',
                'penyuluh',
                'lainnya',
            ])->default('lainnya');
            $table->string('organization')->nullable();
            $table->string('position')->nullable();
            $table->string('nip')->nullable();
            $table->string('primary_phone');
            $table->boolean('primary_phone_is_whatsapp')->default(true);
            $table->string('secondary_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Recreate pivot tables
        Schema::create('contact_planting_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_location_id')->cascadeOnDelete();
            $table->foreignId('contact_id')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['planting_location_id', 'contact_id'], 'planting_location_contact_unique');
        });

        Schema::create('contact_planting_location_land_manager', function (Blueprint $table) {
            $table->id();
            $table->string('planting_location_id', 36)->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->cascadeOnDelete();
            $table->foreignId('contact_id')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['planting_location_id', 'contact_id'], 'p_loc_land_mgr_contact_uniq');
        });

        Schema::create('contact_planting_location_land_worker', function (Blueprint $table) {
            $table->id();
            $table->string('planting_location_id', 36)->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->cascadeOnDelete();
            $table->foreignId('contact_id')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['planting_location_id', 'contact_id'], 'p_loc_land_wkr_contact_uniq');
        });
    }
};
