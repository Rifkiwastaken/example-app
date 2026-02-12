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
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_type_seeds', 'certification_report_id')) {
                $table->string('certification_report_id', 36)->nullable()->after('planting_location_id')
                    ->comment('Laporan sertifikasi yang menjadi sumber stok benih');
                $table->foreign('certification_report_id', 'inv_type_seeds_cert_report_fk')
                    ->references('certification_report_id')
                    ->on('certification_reports')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_type_seeds', 'certification_report_id')) {
                $table->dropForeign('inv_type_seeds_cert_report_fk');
                $table->dropColumn('certification_report_id');
            }
        });
    }
};
