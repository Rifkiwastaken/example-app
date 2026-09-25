<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus kolom planting_treatment_id, planting_nutrient_id, planting_location_id dari expenses.
     * Data lokasi diambil dari planting (expense->planting->planting_location_id).
     */
    public function up(): void
    {
        if (!Schema::hasTable('expenses')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            $cols = ['planting_treatment_id', 'planting_nutrient_id', 'planting_location_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('expenses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
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
            if (!Schema::hasColumn('expenses', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('expense_id');
            }
            if (!Schema::hasColumn('expenses', 'planting_treatment_id')) {
                $table->string('planting_treatment_id', 36)->nullable()->after('planting_id');
            }
            if (!Schema::hasColumn('expenses', 'planting_nutrient_id')) {
                $table->string('planting_nutrient_id', 36)->nullable()->after('planting_treatment_id');
            }
        });
    }
};
