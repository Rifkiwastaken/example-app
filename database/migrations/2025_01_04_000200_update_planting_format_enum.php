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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // First, update existing data to match new enum values
        if (Schema::hasTable('planting_locations')) {
            // Update existing data before changing enum
            DB::table('planting_locations')->where('planting_format', 'petak')->update(['planting_format' => 'ditanam_dalam_petak']);
            DB::table('planting_locations')->where('planting_format', 'row')->update(['planting_format' => 'row_crop']);
            
            // Update enum values for planting_format
            DB::statement("ALTER TABLE planting_locations MODIFY COLUMN planting_format ENUM('ditanam_dalam_petak', 'cover_crop', 'row_crop', 'lainnya') DEFAULT 'ditanam_dalam_petak'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('planting_locations')) {
            // Revert existing data
            DB::table('planting_locations')->where('planting_format', 'ditanam_dalam_petak')->update(['planting_format' => 'petak']);
            DB::table('planting_locations')->where('planting_format', 'row_crop')->update(['planting_format' => 'row']);
            
            // Revert enum
            DB::statement("ALTER TABLE planting_locations MODIFY COLUMN planting_format ENUM('petak', 'cover_crop', 'row', 'lainnya') DEFAULT 'petak'");
        }
    }
};

