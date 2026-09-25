<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus FK dari tabel expenses ke planting_treatments, planting_nutrients, dan planting_locations.
     * Data expense sudah terhubung melalui kolom planting_id.
     */
    public function up(): void
    {
        if (!Schema::hasTable('expenses')) {
            return;
        }

        $columns = ['planting_treatment_id', 'planting_nutrient_id', 'planting_location_id'];
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'expenses'
              AND COLUMN_NAME IN ('" . implode("','", $columns) . "')
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($constraints as $row) {
            try {
                Schema::table('expenses', function (Blueprint $table) use ($row) {
                    $table->dropForeign($row->CONSTRAINT_NAME);
                });
            } catch (\Throwable $e) {
                // Abaikan jika constraint sudah tidak ada
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('expenses')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'planting_treatment_id')) {
                $table->foreign('planting_treatment_id')
                    ->references('planting_treatment_id')
                    ->on('planting_treatments')
                    ->onDelete('cascade');
            }
            if (Schema::hasColumn('expenses', 'planting_nutrient_id')) {
                $table->foreign('planting_nutrient_id')
                    ->references('planting_nutrient_id')
                    ->on('planting_nutrients')
                    ->onDelete('cascade');
            }
            if (Schema::hasColumn('expenses', 'planting_location_id')) {
                $table->foreign('planting_location_id')
                    ->references('planting_location_id')
                    ->on('planting_locations')
                    ->onDelete('cascade');
            }
        });
    }
};
