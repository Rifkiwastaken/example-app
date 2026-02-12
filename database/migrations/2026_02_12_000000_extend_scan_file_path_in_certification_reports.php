<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * scan_file_path perlu > 50 karakter untuk path + nama file asli (mis. WhatsApp Image 2023-09-10 at 10.47.02.jpg)
     */
    public function up(): void
    {
        if (!Schema::hasTable('certification_reports') || !Schema::hasColumn('certification_reports', 'scan_file_path')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `certification_reports` MODIFY COLUMN `scan_file_path` VARCHAR(512) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE certification_reports ALTER COLUMN scan_file_path TYPE VARCHAR(512)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('certification_reports') || !Schema::hasColumn('certification_reports', 'scan_file_path')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `certification_reports` MODIFY COLUMN `scan_file_path` VARCHAR(50) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE certification_reports ALTER COLUMN scan_file_path TYPE VARCHAR(50)');
        }
    }
};
