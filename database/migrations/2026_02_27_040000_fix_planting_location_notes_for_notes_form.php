<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbaikan agar form Tambah Catatan Baru di data penanaman bisa menyimpan:
     * - attachment_path: diperpanjang ke 255 karakter (path file dari store() bisa panjang)
     * - assigned_to: dikembalikan ke JSON agar bisa menyimpan array user_id
     */
    public function up(): void
    {
        if (!Schema::hasTable('planting_location_notes')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('planting_location_notes', 'attachment_path')) {
                DB::statement('ALTER TABLE `planting_location_notes` MODIFY COLUMN `attachment_path` VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('planting_location_notes', 'assigned_to')) {
                DB::statement('ALTER TABLE `planting_location_notes` MODIFY COLUMN `assigned_to` JSON NULL');
            }
        } elseif ($driver === 'pgsql') {
            if (Schema::hasColumn('planting_location_notes', 'attachment_path')) {
                DB::statement('ALTER TABLE "planting_location_notes" ALTER COLUMN "attachment_path" TYPE VARCHAR(255)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('planting_location_notes')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('planting_location_notes', 'attachment_path')) {
                DB::statement('ALTER TABLE `planting_location_notes` MODIFY COLUMN `attachment_path` VARCHAR(50) NULL');
            }
            if (Schema::hasColumn('planting_location_notes', 'assigned_to')) {
                DB::statement('ALTER TABLE `planting_location_notes` MODIFY COLUMN `assigned_to` VARCHAR(36) NULL');
            }
        } elseif ($driver === 'pgsql') {
            if (Schema::hasColumn('planting_location_notes', 'attachment_path')) {
                DB::statement('ALTER TABLE "planting_location_notes" ALTER COLUMN "attachment_path" TYPE VARCHAR(50)');
            }
        }
    }
};
