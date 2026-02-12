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
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('landing_page_settings')->insert([
            ['key' => 'hero_title', 'value' => 'Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_subtitle', 'value' => 'Pantau ketersediaan stok benih padi bersertifikat secara real-time dari seluruh unit UPTD BBI TPPH.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_image', 'value' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'office_address', 'value' => 'UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura<br>Jl. Raya Padang - Bukittinggi KM 15<br>Lubuk Minturun, Padang, Sumatera Barat<br>Kode Pos: 25163', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'office_phone', 'value' => '(0751) 123456', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'office_whatsapp', 'value' => '+62 812-3456-7890', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'office_email', 'value' => 'info@bbitpph.sumbar.go.id', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'facebook_url', 'value' => '#', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'instagram_url', 'value' => '#', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'youtube_url', 'value' => '#', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
