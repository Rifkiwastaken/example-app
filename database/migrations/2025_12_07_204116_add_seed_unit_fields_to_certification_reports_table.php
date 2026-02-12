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
            if (!Schema::hasColumn('certification_reports', 'seed_unit')) {
                $table->string('seed_unit')->nullable()->after('certified_seed_quantity')->comment('Satuan benih (kg, ton, kuintal, karung, sak, liter)');
            }
            if (!Schema::hasColumn('certification_reports', 'seed_unit_quantity')) {
                $table->decimal('seed_unit_quantity', 12, 2)->nullable()->after('seed_unit')->comment('Jumlah satuan benih');
            }
            if (!Schema::hasColumn('certification_reports', 'harvest_per_unit')) {
                $table->decimal('harvest_per_unit', 12, 2)->nullable()->after('seed_unit_quantity')->comment('Jumlah panen per satuan benih');
            }
            if (!Schema::hasColumn('certification_reports', 'reporter_name')) {
                $table->string('reporter_name')->nullable()->after('inspector_name')->comment('Pengisi laporan sertifikasi (user admin)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            if (Schema::hasColumn('certification_reports', 'reporter_name')) {
                $table->dropColumn('reporter_name');
            }
            if (Schema::hasColumn('certification_reports', 'harvest_per_unit')) {
                $table->dropColumn('harvest_per_unit');
            }
            if (Schema::hasColumn('certification_reports', 'seed_unit_quantity')) {
                $table->dropColumn('seed_unit_quantity');
            }
            if (Schema::hasColumn('certification_reports', 'seed_unit')) {
                $table->dropColumn('seed_unit');
            }
        });
    }
};
