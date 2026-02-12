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
        Schema::table('sales', function (Blueprint $table) {
            // Hapus field planting_location_id (opsional, bisa di-comment jika ingin tetap ada untuk backward compatibility)
            // $table->dropForeign(['planting_location_id']);
            // $table->dropColumn('planting_location_id');
            
            // Field baru untuk data pembeli
            $table->string('buyer_nik', 50)->nullable()->after('buyer_contact'); // NIK Pembeli
            $table->enum('buyer_category', ['petani_perorangan', 'kelompok_tani', 'instansi_pemerintah', 'swasta', 'lainnya'])->nullable()->after('buyer_nik'); // Kategori Pembeli
            $table->string('buyer_category_custom', 50)->nullable()->after('buyer_category'); // Kategori custom jika dipilih "lainnya"
            
            // Field baru untuk data lokasi sebaran
            $table->string('destination_province', 50)->nullable()->after('buyer_category_custom'); // Provinsi
            $table->string('destination_city', 50)->nullable()->after('destination_province'); // Kabupaten/Kota
            $table->string('destination_district', 50)->nullable()->after('destination_city'); // Kecamatan
            $table->string('destination_village', 50)->nullable()->after('destination_district'); // Desa/Kelurahan
            $table->string('planned_location_name', 50)->nullable()->after('destination_village'); // Rencana Lokasi Lahan (Nama blok/kampung)
            $table->decimal('estimated_planting_area', 10, 2)->nullable()->after('planned_location_name'); // Estimasi Luas Tanam (hektar)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_nik',
                'buyer_category',
                'buyer_category_custom',
                'destination_province',
                'destination_city',
                'destination_district',
                'destination_village',
                'planned_location_name',
                'estimated_planting_area',
            ]);
        });
    }
};
