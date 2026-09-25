<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planting_fields') || Schema::hasColumn('planting_fields', 'peta_lahan')) {
            return;
        }

        Schema::table('planting_fields', function (Blueprint $table) {
            $table->json('peta_lahan')->nullable()->after('koordinat_gps');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('planting_fields') || ! Schema::hasColumn('planting_fields', 'peta_lahan')) {
            return;
        }

        Schema::table('planting_fields', function (Blueprint $table) {
            $table->dropColumn('peta_lahan');
        });
    }
};
