<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planting_attachments')) {
            return;
        }

        Schema::table('planting_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('planting_attachments', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('planting_attachment_id');
            }

            if (!Schema::hasColumn('planting_attachments', 'attachment_type')) {
                $table->string('attachment_type')->nullable()->after('title');
            }

            if (!Schema::hasColumn('planting_attachments', 'plant_type')) {
                $table->string('plant_type')->nullable()->after('attachment_type');
            }
        });

        if (Schema::hasColumn('planting_attachments', 'planting_id') && Schema::hasColumn('planting_attachments', 'planting_location_id')) {
            DB::statement("
                UPDATE planting_attachments pa
                LEFT JOIN plantings p ON p.planting_id = pa.planting_id
                SET pa.planting_location_id = p.planting_location_id
                WHERE pa.planting_location_id IS NULL
            ");
        }

        if (Schema::hasColumn('planting_attachments', 'planting_location_id') && !$this->foreignKeyExists('planting_attachments', 'planting_location_id')) {
            Schema::table('planting_attachments', function (Blueprint $table) {
                $table->foreign('planting_location_id')
                    ->references('planting_location_id')
                    ->on('planting_locations')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('planting_attachments', 'planting_id')) {
            $this->dropForeignKeysForColumn('planting_attachments', 'planting_id');
            $this->dropIndexesForColumn('planting_attachments', 'planting_id');

            Schema::table('planting_attachments', function (Blueprint $table) {
                $table->dropColumn('planting_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('planting_attachments')) {
            return;
        }

        Schema::table('planting_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('planting_attachments', 'planting_id')) {
                $table->string('planting_id', 36)->nullable()->after('planting_location_id');
            }
        });

        if (Schema::hasColumn('planting_attachments', 'planting_location_id')) {
            $this->dropForeignKeysForColumn('planting_attachments', 'planting_location_id');
            $this->dropIndexesForColumn('planting_attachments', 'planting_location_id');

            Schema::table('planting_attachments', function (Blueprint $table) {
                $table->dropColumn('planting_location_id');
            });
        }

        Schema::table('planting_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('planting_attachments', 'attachment_type')) {
                $table->dropColumn('attachment_type');
            }

            if (Schema::hasColumn('planting_attachments', 'plant_type')) {
                $table->dropColumn('plant_type');
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
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
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
            DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$index->INDEX_NAME}`");
        }
    }

    private function foreignKeyExists(string $tableName, string $columnName): bool
    {
        $databaseName = DB::getDatabaseName();
        $result = DB::selectOne(
            "SELECT COUNT(*) AS total
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$databaseName, $tableName, $columnName]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
