<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify role enum to include 'penangkar'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'kepala_satuan_tugas', 'petugas_sertifikasi', 'petugas_gudang', 'petugas_bbi', 'penangkar') DEFAULT 'petugas_bbi'");
        
        // Change location_id to location_placement (text field)
        Schema::table('users', function (Blueprint $table) {
            $table->string('location_placement')->nullable()->after('location_id');
        });
        
        // Copy existing location_id data to location_placement if needed
        DB::statement("UPDATE users SET location_placement = (SELECT name FROM locations WHERE locations.location_id = users.location_id) WHERE location_id IS NOT NULL");
        
        // Add new fields for user details
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('email');
            $table->string('full_name')->nullable()->after('name');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('full_name');
            $table->enum('contact_type', [
                'pegawai_uptd_bbi_tpph',
                'pegawai_gudang',
                'petugas_sertifikasi',
                'petani',
                'penyuluh',
                'penangkar',
                'lainnya',
            ])->nullable()->after('status');
            $table->string('organization')->nullable()->after('contact_type');
            $table->string('position')->nullable()->after('organization');
            $table->string('nip')->nullable()->after('position');
            $table->string('primary_phone')->nullable()->after('nip');
            $table->boolean('primary_phone_is_whatsapp')->default(true)->after('primary_phone');
            $table->string('secondary_phone')->nullable()->after('primary_phone_is_whatsapp');
            $table->text('address')->nullable()->after('secondary_phone');
            $table->string('province')->nullable()->after('address');
            $table->string('city')->nullable()->after('province');
            $table->string('district')->nullable()->after('city');
            $table->string('village')->nullable()->after('district');
            $table->text('notes')->nullable()->after('village');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'full_name',
                'status',
                'contact_type',
                'organization',
                'position',
                'nip',
                'primary_phone',
                'primary_phone_is_whatsapp',
                'secondary_phone',
                'address',
                'province',
                'city',
                'district',
                'village',
                'notes',
                'location_placement',
            ]);
        });
        
        // Revert role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'kepala_satuan_tugas', 'petugas_sertifikasi', 'petugas_gudang', 'petugas_bbi') DEFAULT 'petugas_bbi'");
    }
};




