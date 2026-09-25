<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planting_locations')) {
            return;
        }

        Schema::table('planting_locations', function (Blueprint $table) {
            if (Schema::hasColumn('planting_locations', 'administrative_address')) {
                $table->text('administrative_address')->nullable()->change();
            }
            if (Schema::hasColumn('planting_locations', 'google_maps_link')) {
                $table->string('google_maps_link', 500)->nullable()->change();
            }
            if (Schema::hasColumn('planting_locations', 'location_summary')) {
                $table->string('location_summary', 255)->nullable()->change();
            }
            if (Schema::hasColumn('planting_locations', 'name')) {
                $table->string('name', 255)->change();
            }
        });
    }

    public function down(): void
    {
        // Tidak dikembalikan ke varchar(50) agar data alamat tidak terpotong.
    }
};
