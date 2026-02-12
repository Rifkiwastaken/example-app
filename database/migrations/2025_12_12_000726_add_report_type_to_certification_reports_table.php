<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            $table->string('report_type')->default('Laporan Pemeriksaan Pertanaman')->after('certification_id');
        });
    }

    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            $table->dropColumn('report_type');
        });
    }
};
