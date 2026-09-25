<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus objek legacy inventory_type_seeds (table/view) karena sumber stok pindah ke certification_reports.
        DB::statement('DROP VIEW IF EXISTS inventory_type_seeds');
        if (Schema::hasTable('inventory_type_seeds')) {
            Schema::drop('inventory_type_seeds');
        }

        if (!Schema::hasTable('inventory_types') || !Schema::hasTable('certification_reports')) {
            return;
        }

        Schema::table('inventory_types', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_types', 'latest_certification_report_id')) {
                $table->string('latest_certification_report_id', 36)->nullable()->after('latest_expiry_date');
            }
            if (!Schema::hasColumn('inventory_types', 'certification_source_count')) {
                $table->unsignedInteger('certification_source_count')->default(0)->after('latest_certification_report_id');
            }
            if (!Schema::hasColumn('inventory_types', 'certification_stock_total')) {
                $table->decimal('certification_stock_total', 14, 2)->default(0)->after('certification_source_count');
            }
            if (!Schema::hasColumn('inventory_types', 'certification_last_synced_at')) {
                $table->timestamp('certification_last_synced_at')->nullable()->after('certification_stock_total');
            }
        });

        // Backfill ringkasan stok sertifikasi per inventory type.
        DB::statement(
            "UPDATE inventory_types it
             LEFT JOIN (
                SELECT
                    inventory_type_id,
                    COUNT(*) AS source_count,
                    COALESCE(SUM(COALESCE(quantity_added_to_stock, certified_seed_quantity, 0)), 0) AS stock_total,
                    MAX(created_at) AS last_synced_at
                FROM certification_reports
                WHERE inventory_type_id IS NOT NULL
                GROUP BY inventory_type_id
             ) agg ON agg.inventory_type_id = it.inventory_type_id
             SET
                it.certification_source_count = COALESCE(agg.source_count, 0),
                it.certification_stock_total = COALESCE(agg.stock_total, 0),
                it.certification_last_synced_at = agg.last_synced_at"
        );

        // Isi latest_certification_report_id berdasarkan laporan terbaru.
        DB::statement(
            "UPDATE inventory_types it
             JOIN (
                SELECT cr1.inventory_type_id, cr1.certification_report_id
                FROM certification_reports cr1
                INNER JOIN (
                    SELECT inventory_type_id, MAX(created_at) AS max_created
                    FROM certification_reports
                    WHERE inventory_type_id IS NOT NULL
                    GROUP BY inventory_type_id
                ) latest
                ON latest.inventory_type_id = cr1.inventory_type_id
                AND latest.max_created = cr1.created_at
             ) x ON x.inventory_type_id = it.inventory_type_id
             SET it.latest_certification_report_id = x.certification_report_id"
        );

        $this->ensureForeignKey(
            'certification_reports',
            'inventory_type_id',
            'inventory_types',
            'inventory_type_id',
            'set null'
        );
        $this->ensureForeignKey(
            'inventory_types',
            'latest_certification_report_id',
            'certification_reports',
            'certification_report_id',
            'set null'
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('inventory_types')) {
            return;
        }

        $this->dropForeignByColumn('inventory_types', 'latest_certification_report_id');

        Schema::table('inventory_types', function (Blueprint $table) {
            foreach ([
                'certification_last_synced_at',
                'certification_stock_total',
                'certification_source_count',
                'latest_certification_report_id',
            ] as $column) {
                if (Schema::hasColumn('inventory_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete = 'set null'
    ): void {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }
        if (!Schema::hasTable($refTable) || !Schema::hasColumn($refTable, $refColumn)) {
            return;
        }

        $exists = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?",
            [$table, $column, $refTable]
        );
        if ($exists) {
            return;
        }

        $constraint = "{$table}_{$column}_foreign";
        $deleteClause = $onDelete === 'set null' ? 'ON DELETE SET NULL' : 'ON DELETE CASCADE';

        DB::statement(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$constraint}`
             FOREIGN KEY (`{$column}`)
             REFERENCES `{$refTable}`(`{$refColumn}`)
             {$deleteClause}"
        );
    }

    private function dropForeignByColumn(string $table, string $column): void
    {
        $keys = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$table, $column]
        );

        foreach ($keys as $key) {
            if (!empty($key->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
            }
        }
    }
};

