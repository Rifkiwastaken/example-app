<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_types')) {
            return;
        }

        Schema::table('inventory_types', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_types', 'total_seed_quantity')) {
                $table->decimal('total_seed_quantity', 14, 2)->default(0)->after('description');
            }
            if (!Schema::hasColumn('inventory_types', 'source_certification_count')) {
                $table->unsignedInteger('source_certification_count')->default(0)->after('total_seed_quantity');
            }
            if (!Schema::hasColumn('inventory_types', 'latest_expiry_date')) {
                $table->date('latest_expiry_date')->nullable()->after('source_certification_count');
            }
        });

        if (Schema::hasTable('inventory_type_seeds')) {
            // Sinkronkan relasi report -> inventory type jika masih kosong.
            DB::statement(
                "UPDATE certification_reports cr
                 JOIN inventory_type_seeds its
                   ON its.certification_report_id = cr.certification_report_id
                 SET cr.inventory_type_id = its.inventory_type_id
                 WHERE cr.inventory_type_id IS NULL"
            );
        }

        // Hitung ringkasan stok dari laporan sertifikasi yang sudah tertaut ke inventory_type.
        DB::statement(
            "UPDATE inventory_types it
             LEFT JOIN (
               SELECT
                 inventory_type_id,
                 COALESCE(SUM(COALESCE(quantity_added_to_stock, certified_seed_quantity, 0)), 0) AS qty_sum,
                 COUNT(*) AS cert_count,
                 MAX(expiry_date) AS latest_expiry
               FROM certification_reports
               WHERE inventory_type_id IS NOT NULL
               GROUP BY inventory_type_id
             ) agg ON agg.inventory_type_id = it.inventory_type_id
             SET
               it.total_seed_quantity = COALESCE(agg.qty_sum, 0),
               it.source_certification_count = COALESCE(agg.cert_count, 0),
               it.latest_expiry_date = agg.latest_expiry"
        );

        // Hapus kolom plant_id di inventory_types (informasi plant diturunkan dari certification_reports -> harvest).
        if (Schema::hasColumn('inventory_types', 'plant_id')) {
            $this->dropForeignKeysForColumn('inventory_types', 'plant_id');
            $this->dropIndexesForColumn('inventory_types', 'plant_id');
            Schema::table('inventory_types', function (Blueprint $table) {
                $table->dropColumn('plant_id');
            });
        }

        // Drop tabel inventory_type_seeds setelah data dipindahkan.
        if (Schema::hasTable('inventory_type_seeds')) {
            Schema::drop('inventory_type_seeds');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('inventory_types')) {
            return;
        }

        if (!Schema::hasTable('inventory_type_seeds')) {
            Schema::create('inventory_type_seeds', function (Blueprint $table) {
                $table->string('inventory_type_seed_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->string('plant_id', 36)->nullable();
                $table->string('planting_location_id', 36)->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->decimal('total_seed_quantity', 12, 2)->nullable();
                $table->decimal('estimated_sale_price_per_kg', 12, 2)->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('filled_by_user_id', 36)->nullable();
                $table->string('certification_report_id', 36)->nullable();
                $table->string('storage_number', 100)->nullable();
                $table->timestamps();
            });
        }

        Schema::table('inventory_types', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_types', 'plant_id')) {
                $table->string('plant_id', 36)->nullable()->after('description');
            }
            if (Schema::hasColumn('inventory_types', 'latest_expiry_date')) {
                $table->dropColumn('latest_expiry_date');
            }
            if (Schema::hasColumn('inventory_types', 'source_certification_count')) {
                $table->dropColumn('source_certification_count');
            }
            if (Schema::hasColumn('inventory_types', 'total_seed_quantity')) {
                $table->dropColumn('total_seed_quantity');
            }
        });
    }

    private function dropForeignKeysForColumn(string $tableName, string $columnName): void
    {
        $databaseName = DB::getDatabaseName();
        $keys = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$databaseName, $tableName, $columnName]
        );

        foreach ($keys as $key) {
            if (!empty($key->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
            }
        }
    }

    private function dropIndexesForColumn(string $tableName, string $columnName): void
    {
        $databaseName = DB::getDatabaseName();
        $indexes = DB::select(
            "SELECT DISTINCT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND INDEX_NAME <> 'PRIMARY'",
            [$databaseName, $tableName, $columnName]
        );

        foreach ($indexes as $index) {
            if (!empty($index->INDEX_NAME)) {
                DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$index->INDEX_NAME}`");
            }
        }
    }
};

