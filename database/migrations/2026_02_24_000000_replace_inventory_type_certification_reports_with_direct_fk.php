<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu laporan sertifikasi hanya ke satu stok benih.
     * Ganti pivot inventory_type_certification_reports dengan kolom langsung di certification_reports.
     */
    public function up(): void
    {
        if (!Schema::hasTable('certification_reports')) {
            return;
        }

        Schema::table('certification_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('certification_reports', 'inventory_type_id')) {
                $table->string('inventory_type_id', 36)->nullable()->after('conclusion');
            }
            if (!Schema::hasColumn('certification_reports', 'quantity_added_to_stock')) {
                $table->decimal('quantity_added_to_stock', 12, 2)->nullable()->after('inventory_type_id')
                    ->comment('Jumlah benih yang ditambahkan ke stok bibit (satu laporan hanya ke satu stok)');
            }
        });

        if (Schema::hasTable('inventory_type_certification_reports')) {
            $reportIds = DB::table('inventory_type_certification_reports')->select('certification_report_id')->distinct()->pluck('certification_report_id');
            foreach ($reportIds as $certificationReportId) {
                $row = DB::table('inventory_type_certification_reports')
                    ->where('certification_report_id', $certificationReportId)
                    ->orderBy('created_at')
                    ->first();
                if ($row) {
                    DB::table('certification_reports')
                        ->where('certification_report_id', $certificationReportId)
                        ->update([
                            'inventory_type_id' => $row->inventory_type_id,
                            'quantity_added_to_stock' => $row->quantity,
                        ]);
                }
            }
            Schema::dropIfExists('inventory_type_certification_reports');
        }

        Schema::table('certification_reports', function (Blueprint $table) {
            if (Schema::hasColumn('certification_reports', 'inventory_type_id')) {
                $table->foreign('inventory_type_id', 'certification_reports_inventory_type_id_foreign')
                    ->references('inventory_type_id')
                    ->on('inventory_types')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            $table->dropForeign('certification_reports_inventory_type_id_foreign');
            $table->dropColumn(['inventory_type_id', 'quantity_added_to_stock']);
        });

        if (!Schema::hasTable('inventory_type_certification_reports')) {
            Schema::create('inventory_type_certification_reports', function (Blueprint $table) {
                $table->string('inventory_type_certification_report_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->string('certification_report_id', 36);
                $table->decimal('quantity', 12, 2)->nullable();
                $table->timestamps();
                $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
                $table->foreign('certification_report_id')->references('certification_report_id')->on('certification_reports')->onDelete('cascade');
                $table->unique(['inventory_type_id', 'certification_report_id']);
            });
        }
    }
};
