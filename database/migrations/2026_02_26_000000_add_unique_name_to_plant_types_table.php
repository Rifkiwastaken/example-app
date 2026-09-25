<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nama tipe tanaman harus unik.
     */
    public function up(): void
    {
        if (!Schema::hasTable('plant_types')) {
            return;
        }
        Schema::table('plant_types', function (Blueprint $table) {
            $table->unique('name', 'plant_types_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('plant_types', function (Blueprint $table) {
            $table->dropUnique('plant_types_name_unique');
        });
    }
};
