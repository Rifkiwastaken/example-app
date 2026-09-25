<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom terkait detail tanaman/panen yang tersisa di tabel plantings.
     *
     * Kolom lain yang berisi detail tanaman & panen sudah dipindah ke tabel plants
     * oleh migrasi sebelumnya; di sini kita hanya merapikan sisa:
     * - expected_yield_per_hectare
     * - quantity_planted
     */
    public function up(): void
    {
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

    /**
     * Kembalikan kolom jika diperlukan (rollback).
     */
    public function down(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            if (!Schema::hasColumn('plantings', 'expected_yield_per_hectare')) {
                $table->decimal('expected_yield_per_hectare', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('plantings', 'quantity_planted')) {
                $table->unsignedInteger('quantity_planted')->nullable();
            }
        });
    }
};

