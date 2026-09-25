<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel plant_types hanya menyimpan tipe tanaman (name) dan kategori (category).
     * Varietas disimpan di plants.variety sebagai input bebas.
     */
    public function up(): void
    {
        if (Schema::hasTable('plant_types') && Schema::hasColumn('plant_types', 'variety')) {
            Schema::table('plant_types', function (Blueprint $table) {
                $table->dropColumn('variety');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plant_types') && !Schema::hasColumn('plant_types', 'variety')) {
            Schema::table('plant_types', function (Blueprint $table) {
                $table->text('variety')->nullable()->after('category');
            });
        }
    }
};
