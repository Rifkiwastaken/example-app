<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('certification_reports') && Schema::hasColumn('certification_reports', 'harvest_id')) {
            $this->dropForeignKeysForColumn('certification_reports', 'harvest_id');

            Schema::table('certification_reports', function (Blueprint $table) {
                $table->foreign('harvest_id', 'certification_reports_harvest_id_foreign')
                    ->references('harvest_id')
                    ->on('harvests')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('inventory_types')) {
            if (Schema::hasColumn('inventory_types', 'latest_certification_report_id')) {
                $this->dropForeignKeysForColumn('inventory_types', 'latest_certification_report_id');
            }

            if (Schema::hasColumn('inventory_types', 'certification_report_id')) {
                $this->dropForeignKeysForColumn('inventory_types', 'certification_report_id');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('certification_reports') && Schema::hasColumn('certification_reports', 'harvest_id')) {
            $this->dropForeignKeysForColumn('certification_reports', 'harvest_id');
        }

        if (
            Schema::hasTable('inventory_types') &&
            Schema::hasColumn('inventory_types', 'latest_certification_report_id')
        ) {
            $this->dropForeignKeysForColumn('inventory_types', 'latest_certification_report_id');

            Schema::table('inventory_types', function (Blueprint $table) {
                $table->foreign('latest_certification_report_id', 'inventory_types_latest_certification_report_id_foreign')
                    ->references('certification_report_id')
                    ->on('certification_reports')
                    ->nullOnDelete();
            });
        }
    }

    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $database = DB::getDatabaseName();

        $foreignKeys = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME');

        foreach ($foreignKeys as $foreignKey) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKey}`");
            } catch (\Throwable $e) {
                // Abaikan jika FK sudah tidak ada.
            }
        }
    }
};

