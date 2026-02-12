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
        // Drop foreign key dari users.location_id
        if (Schema::hasColumn('users', 'location_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            });
        }

        // Drop foreign key dari planting_locations.location_id
        if (Schema::hasColumn('planting_locations', 'location_id')) {
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            });
        }

        // Drop tabel locations
        Schema::dropIfExists('locations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate locations table
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('district');
            $table->enum('type', ['lokasi_lahan', 'lokasi_sertifikasi', 'lokasi_gudang', 'lokasi_kantor_utama']);
            $table->text('description')->nullable();
            $table->string('google_maps_link')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        // Recreate location_id di users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('role')->constrained('locations')->onDelete('set null');
        });

        // Recreate location_id di planting_locations
        Schema::table('planting_locations', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('name')->constrained('locations')->nullOnDelete();
        });
    }
};
