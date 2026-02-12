<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('certification_reports', 'harvest_per_unit_unit')) {
                $table->string('harvest_per_unit_unit')->nullable()->after('harvest_per_unit')->comment('Satuan untuk jumlah panen per satuan benih');
            }
            if (!Schema::hasColumn('certification_reports', 'certified_seed_unit')) {
                $table->string('certified_seed_unit')->nullable()->after('certified_seed_quantity')->comment('Satuan untuk jumlah benih yang lulus sertifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            if (Schema::hasColumn('certification_reports', 'certified_seed_unit')) {
                $table->dropColumn('certified_seed_unit');
            }
            if (Schema::hasColumn('certification_reports', 'harvest_per_unit_unit')) {
                $table->dropColumn('harvest_per_unit_unit');
            }
        });
    }
};

