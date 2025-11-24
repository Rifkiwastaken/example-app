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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};







