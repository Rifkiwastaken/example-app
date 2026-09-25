<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom sisa data detail tanaman & detail panen dari plantings.
     * (Kolom lain sudah dipindah ke plants di migrasi sebelumnya.)
     */
    public function up(): void
    {
        // Kolom ini bisa saja sudah dihapus oleh migrasi lain.
        if (Schema::hasColumn('plantings', 'expected_yield_per_hectare')
            || Schema::hasColumn('plantings', 'quantity_planted')) {
            Schema::table('plantings', function (Blueprint $table) {
                if (Schema::hasColumn('plantings', 'expected_yield_per_hectare')) {
                    $table->dropColumn('expected_yield_per_hectare');
                }
                if (Schema::hasColumn('plantings', 'quantity_planted')) {
                    $table->dropColumn('quantity_planted');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            $table->decimal('expected_yield_per_hectare', 12, 2)->nullable()->after('is_completed');
            $table->unsignedInteger('quantity_planted')->nullable()->after('expected_yield_per_hectare');
        });
    }
};
