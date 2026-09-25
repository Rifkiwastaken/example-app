<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * file_path dan file_name di plant_photos/planting_location_photos dibatasi 50 karakter
     * sehingga path dari Storage (mis. plant-photos/xxxxx.jpg) bisa terpotong.
     */
    public function up(): void
    {
        if (Schema::hasTable('plant_photos')) {
            DB::statement('ALTER TABLE `plant_photos` MODIFY COLUMN `file_path` VARCHAR(512) NULL');
            DB::statement('ALTER TABLE `plant_photos` MODIFY COLUMN `file_name` VARCHAR(255) NULL');
            if (Schema::hasColumn('plant_photos', 'mime_type')) {
                DB::statement('ALTER TABLE `plant_photos` MODIFY COLUMN `mime_type` VARCHAR(100) NULL');
            }
        }

        if (Schema::hasTable('planting_location_photos')) {
            DB::statement('ALTER TABLE `planting_location_photos` MODIFY COLUMN `file_path` VARCHAR(512) NULL');
            DB::statement('ALTER TABLE `planting_location_photos` MODIFY COLUMN `file_name` VARCHAR(255) NULL');
            if (Schema::hasColumn('planting_location_photos', 'mime_type')) {
                DB::statement('ALTER TABLE `planting_location_photos` MODIFY COLUMN `mime_type` VARCHAR(100) NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plant_photos')) {
            DB::statement('ALTER TABLE `plant_photos` MODIFY COLUMN `file_path` VARCHAR(50) NULL');
            DB::statement('ALTER TABLE `plant_photos` MODIFY COLUMN `file_name` VARCHAR(50) NULL');
            if (Schema::hasColumn('plant_photos', 'mime_type')) {
                DB::statement('ALTER TABLE `plant_photos` MODIFY COLUMN `mime_type` VARCHAR(50) NULL');
            }
        }

        if (Schema::hasTable('planting_location_photos')) {
            DB::statement('ALTER TABLE `planting_location_photos` MODIFY COLUMN `file_path` VARCHAR(50) NULL');
            DB::statement('ALTER TABLE `planting_location_photos` MODIFY COLUMN `file_name` VARCHAR(50) NULL');
            if (Schema::hasColumn('planting_location_photos', 'mime_type')) {
                DB::statement('ALTER TABLE `planting_location_photos` MODIFY COLUMN `mime_type` VARCHAR(50) NULL');
            }
        }
    }
};
