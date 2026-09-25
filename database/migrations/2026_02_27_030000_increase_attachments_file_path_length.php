<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbesar kolom file_path (dan file_name) di tabel attachments
     * agar muat path file storage yang lebih panjang.
     */
    public function up(): void
    {
        if (!Schema::hasTable('attachments')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `attachments` MODIFY COLUMN `file_path` VARCHAR(255)');
            DB::statement('ALTER TABLE `attachments` MODIFY COLUMN `file_name` VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "attachments" ALTER COLUMN "file_path" TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE "attachments" ALTER COLUMN "file_name" TYPE VARCHAR(255)');
        }
    }

    /**
     * Kembalikan panjang kolom ke 50 karakter (seperti migrasi limit).
     */
    public function down(): void
    {
        if (!Schema::hasTable('attachments')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `attachments` MODIFY COLUMN `file_path` VARCHAR(50)');
            DB::statement('ALTER TABLE `attachments` MODIFY COLUMN `file_name` VARCHAR(50) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "attachments" ALTER COLUMN "file_path" TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE "attachments" ALTER COLUMN "file_name" TYPE VARCHAR(50)');
        }
    }
};

