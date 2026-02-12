<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, set unique values for existing NULL or duplicate report_number_bpsb
        $reports = DB::table('certification_reports')->get();
        foreach ($reports as $index => $report) {
            if (empty($report->report_number_bpsb)) {
                $uniqueNumber = 'BPSB-' . date('Y') . '-' . str_pad($report->id, 6, '0', STR_PAD_LEFT);
                DB::table('certification_reports')
                    ->where('id', $report->id)
                    ->update(['report_number_bpsb' => $uniqueNumber]);
            }
        }
        
        // Remove duplicates by keeping the first one
        $duplicates = DB::table('certification_reports')
            ->select('report_number_bpsb', DB::raw('COUNT(*) as count'))
            ->groupBy('report_number_bpsb')
            ->having('count', '>', 1)
            ->get();
            
        foreach ($duplicates as $duplicate) {
            $ids = DB::table('certification_reports')
                ->where('report_number_bpsb', $duplicate->report_number_bpsb)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            
            // Keep first, update others
            array_shift($ids);
            foreach ($ids as $id) {
                $uniqueNumber = 'BPSB-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);
                DB::table('certification_reports')
                    ->where('id', $id)
                    ->update(['report_number_bpsb' => $uniqueNumber]);
            }
        }
        
        Schema::table('certification_reports', function (Blueprint $table) {
            $table->string('report_number_bpsb')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            $table->dropUnique(['report_number_bpsb']);
            $table->string('report_number_bpsb')->nullable()->change();
        });
    }
};
