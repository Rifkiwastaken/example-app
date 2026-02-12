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
        // Update enum to include 'sawah'
        DB::statement("ALTER TABLE planting_locations MODIFY COLUMN location_type ENUM('lapangan', 'sawah', 'greenhouse', 'grow_room', 'padang_rumput', 'petak_ternak', 'lainnya') DEFAULT 'lapangan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original values (without 'sawah')
        DB::statement("ALTER TABLE planting_locations MODIFY COLUMN location_type ENUM('lapangan', 'greenhouse', 'grow_room', 'padang_rumput', 'petak_ternak', 'lainnya') DEFAULT 'lapangan'");
    }
};

