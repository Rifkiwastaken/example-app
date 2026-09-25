<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename planting_tasks.template_id -> task_template_id agar konsisten
     * dengan task_templates.task_template_id (relasi FK).
     */
    public function up(): void
    {
        $tableName = 'planting_tasks';

        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'template_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Cari nama constraint FK pada kolom template_id
            $fkRows = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'template_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            foreach ($fkRows as $row) {
                DB::statement('ALTER TABLE `' . $tableName . '` DROP FOREIGN KEY `' . $row->CONSTRAINT_NAME . '`');
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('template_id', 'task_template_id');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('task_template_id')
                    ->references('task_template_id')
                    ->on('task_templates')
                    ->onDelete('set null');
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['template_id']);
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('template_id', 'task_template_id');
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('task_template_id')
                    ->references('task_template_id')
                    ->on('task_templates')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = 'planting_tasks';

        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'task_template_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $fkRows = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'task_template_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            foreach ($fkRows as $row) {
                DB::statement('ALTER TABLE `' . $tableName . '` DROP FOREIGN KEY `' . $row->CONSTRAINT_NAME . '`');
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('task_template_id', 'template_id');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('template_id')
                    ->references('task_template_id')
                    ->on('task_templates')
                    ->onDelete('set null');
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['task_template_id']);
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('task_template_id', 'template_id');
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('template_id')
                    ->references('task_template_id')
                    ->on('task_templates')
                    ->onDelete('set null');
            });
        }
    }
};
