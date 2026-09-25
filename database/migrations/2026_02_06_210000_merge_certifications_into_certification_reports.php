<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Gabungkan data tabel certifications ke certification_reports.
     *
     * - Tambah kolom harvest_id, certification_status, seed_class_requested ke certification_reports
     * - Salin nilai dari certifications berdasarkan certification_id
     * - Lepas relasi ke certifications lalu hapus tabel certifications
     */
    public function up(): void
    {
        if (!Schema::hasTable('certification_reports')) {
            return;
        }

        // Tambah kolom baru ke certification_reports
        Schema::table('certification_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('certification_reports', 'harvest_id')) {
                $table->string('harvest_id', 36)->nullable()->after('certification_report_id');
            }
            if (!Schema::hasColumn('certification_reports', 'certification_status')) {
                $table->string('certification_status')->default('dalam_proses')->after('harvest_id');
            }
            if (!Schema::hasColumn('certification_reports', 'seed_class_requested')) {
                $table->string('seed_class_requested')->nullable()->after('certification_status');
            }
        });

        // Migrasi data dari certifications ke certification_reports (jika tabel masih ada)
        if (Schema::hasTable('certifications') && Schema::hasColumn('certification_reports', 'certification_id')) {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement("
                    UPDATE certification_reports cr
                    JOIN certifications c ON cr.certification_id = c.certification_id
                    SET
                        cr.harvest_id = COALESCE(cr.harvest_id, c.harvest_id),
                        cr.certification_status = COALESCE(cr.certification_status, c.certification_status),
                        cr.seed_class_requested = COALESCE(cr.seed_class_requested, c.seed_class_requested)
                ");
            } elseif ($driver === 'pgsql') {
                DB::statement("
                    UPDATE certification_reports cr
                    SET
                        harvest_id = COALESCE(harvest_id, c.harvest_id),
                        certification_status = COALESCE(certification_status, c.certification_status),
                        seed_class_requested = COALESCE(seed_class_requested, c.seed_class_requested)
                    FROM certifications c
                    WHERE cr.certification_id = c.certification_id
                ");
            } else {
                // Fallback untuk driver lain: lakukan dengan loop sederhana
                $rows = DB::table('certification_reports')
                    ->select('certification_report_id', 'certification_id')
                    ->whereNotNull('certification_id')
                    ->get();

                foreach ($rows as $row) {
                    $cert = DB::table('certifications')
                        ->where('certification_id', $row->certification_id)
                        ->first();

                    if ($cert) {
                        DB::table('certification_reports')
                            ->where('certification_report_id', $row->certification_report_id)
                            ->update([
                                'harvest_id' => $cert->harvest_id,
                                'certification_status' => $cert->certification_status,
                                'seed_class_requested' => $cert->seed_class_requested,
                            ]);
                    }
                }
            }
        }

        // Lepas foreign key certification_id dari certification_reports (jika ada), lalu hapus kolomnya
        if (Schema::hasColumn('certification_reports', 'certification_id')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                // Di sebagian environment, FK sudah dilepas oleh migration lain.
                // Di sini kita hanya menghapus kolomnya.
                $table->dropColumn('certification_id');
            });
        }

        // Hapus tabel certifications (data sudah dipindahkan)
        Schema::dropIfExists('certifications');
    }

    /**
     * Kembalikan struktur lama secara minimal (tanpa memulihkan data).
     */
    public function down(): void
    {
        // Buat kembali tabel certifications dengan struktur dasar
        if (!Schema::hasTable('certifications')) {
            Schema::create('certifications', function (Blueprint $table) {
                $table->string('certification_id', 36)->primary();
                $table->string('harvest_id', 36)->nullable();
                $table->string('certification_status')->default('dalam_proses');
                $table->string('seed_class_requested')->nullable();
                $table->timestamps();
            });
        }

        // Tambah kembali kolom certification_id di certification_reports
        if (Schema::hasTable('certification_reports') && !Schema::hasColumn('certification_reports', 'certification_id')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                $table->string('certification_id', 36)->nullable()->after('certification_report_id');
            });
        }
    }
};

