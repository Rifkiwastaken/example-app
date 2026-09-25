<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus kolom planting_location_id dari tabel nutrients.
     * Nutrient tetap terhubung ke lokasi melalui planting (planting_id -> plantings.planting_location_id).
     */
    public function up(): void
    {
        if (!Schema::hasTable('nutrients')) {
            return;
        }

        if (!Schema::hasColumn('nutrients', 'planting_location_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Drop foreign key if exists (nama bisa berbeda di tiap env)
            $fkName = null;
            $rows = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nutrients' AND COLUMN_NAME = 'planting_location_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (!empty($rows)) {
                $fkName = $rows[0]->CONSTRAINT_NAME;
                DB::statement('ALTER TABLE `nutrients` DROP FOREIGN KEY `' . $fkName . '`');
            }
            Schema::table('nutrients', function (Blueprint $table) {
                $table->dropColumn('planting_location_id');
            });
        } elseif ($driver === 'pgsql') {
            Schema::table('nutrients', function (Blueprint $table) {
                $table->dropForeign(['planting_location_id']);
                $table->dropColumn('planting_location_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('nutrients')) {
            return;
        }

        if (Schema::hasColumn('nutrients', 'planting_location_id')) {
            return;
        }

        Schema::table('nutrients', function (Blueprint $table) {
            $table->string('planting_location_id', 36)->nullable()->after('nutrient_id');
        });
    }
};
