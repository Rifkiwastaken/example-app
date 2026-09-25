<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom planting_location_id dari plants.
     * Lokasi penanaman sekarang diturunkan dari plantings (satu tanaman bisa di banyak lokasi).
     */
    public function up(): void
    {
        if (!Schema::hasTable('plants') || !Schema::hasColumn('plants', 'planting_location_id')) {
            return;
        }
        if (DB::getDriverName() === 'mysql') {
            $rows = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plants' AND COLUMN_NAME = 'planting_location_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            if (!empty($rows)) {
                Schema::table('plants', function (Blueprint $table) use ($rows) {
                    $table->dropForeign($rows[0]->CONSTRAINT_NAME);
                });
            }
        }
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn('planting_location_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('plants')) {
            return;
        }
        if (Schema::hasColumn('plants', 'planting_location_id')) {
            return;
        }
        Schema::table('plants', function (Blueprint $table) {
            $table->string('planting_location_id', 36)->nullable()->after('variety');
        });
    }
};
