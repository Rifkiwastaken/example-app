<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbesar kolom di tabel inventory_photos
     * supaya penyimpanan foto di halaman stok benih tidak error.
     */
    public function up(): void
    {
        if (!Schema::hasTable('inventory_photos')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('inventory_photos', 'photo_path')) {
                DB::statement('ALTER TABLE `inventory_photos` MODIFY COLUMN `photo_path` VARCHAR(255)');
            }

            if (Schema::hasColumn('inventory_photos', 'caption')) {
                DB::statement('ALTER TABLE `inventory_photos` MODIFY COLUMN `caption` VARCHAR(255) NULL');
            }
        } elseif ($driver === 'pgsql') {
            if (Schema::hasColumn('inventory_photos', 'photo_path')) {
                DB::statement('ALTER TABLE "inventory_photos" ALTER COLUMN "photo_path" TYPE VARCHAR(255)');
            }

            if (Schema::hasColumn('inventory_photos', 'caption')) {
                DB::statement('ALTER TABLE "inventory_photos" ALTER COLUMN "caption" TYPE VARCHAR(255)');
            }
        }
    }

    /**
     * Kembalikan panjang kolom ke 50 karakter
     * (mengikuti migrasi limit_all_varchar_fields_to_50_characters).
     */
    public function down(): void
    {
        if (!Schema::hasTable('inventory_photos')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('inventory_photos', 'photo_path')) {
                DB::statement('ALTER TABLE `inventory_photos` MODIFY COLUMN `photo_path` VARCHAR(50)');
            }

            if (Schema::hasColumn('inventory_photos', 'caption')) {
                DB::statement('ALTER TABLE `inventory_photos` MODIFY COLUMN `caption` VARCHAR(50) NULL');
            }
        } elseif ($driver === 'pgsql') {
            if (Schema::hasColumn('inventory_photos', 'photo_path')) {
                DB::statement('ALTER TABLE "inventory_photos" ALTER COLUMN "photo_path" TYPE VARCHAR(50)');
            }

            if (Schema::hasColumn('inventory_photos', 'caption')) {
                DB::statement('ALTER TABLE "inventory_photos" ALTER COLUMN "caption" TYPE VARCHAR(50)');
            }
        }
    }
};

