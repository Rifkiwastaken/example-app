<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1: Menambahkan kolom custom ID baru untuk tabel Certification.
     */
    public function up(): void
    {
        // 1. CERTIFICATIONS TABLE
        if (Schema::hasTable('certifications') && !Schema::hasColumn('certifications', 'certification_id')) {
            Schema::table('certifications', function (Blueprint $table) {
                $table->string('certification_id', 36)->nullable()->unique()->after('id');
                // Tambahkan kolom FK baru (temporary)
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 2. CERTIFICATION_REPORTS TABLE
        if (Schema::hasTable('certification_reports') && !Schema::hasColumn('certification_reports', 'certification_report_id')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                $table->string('certification_report_id', 36)->nullable()->unique()->after('id');
                // Tambahkan kolom FK baru (temporary)
                $table->string('new_certification_id', 36)->nullable()->after('certification_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('certification_reports')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                $table->dropColumn(['certification_report_id', 'new_certification_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('certifications')) {
            Schema::table('certifications', function (Blueprint $table) {
                $table->dropColumn(['certification_id', 'new_plant_id', 'new_user_id']);
            });
        }
    }
};
