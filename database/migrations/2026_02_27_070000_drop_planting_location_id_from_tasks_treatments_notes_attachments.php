<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus kolom planting_location_id dari tasks, treatments, planting_location_notes, attachments.
     * Relasi ke lokasi melalui planting (planting_id -> plantings.planting_location_id), sama seperti nutrient.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        $tables = ['tasks', 'treatments', 'planting_location_notes', 'attachments'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'planting_location_id')) {
                continue;
            }

            if ($driver === 'mysql') {
                $rows = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'planting_location_id' AND REFERENCED_TABLE_NAME IS NOT NULL", [$tableName]);
                if (!empty($rows)) {
                    $fkName = $rows[0]->CONSTRAINT_NAME;
                    DB::statement('ALTER TABLE `' . $tableName . '` DROP FOREIGN KEY `' . $fkName . '`');
                }
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('planting_location_id');
                });
            } elseif ($driver === 'pgsql') {
                try {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropForeign(['planting_location_id']);
                    });
                } catch (\Throwable $e) {
                    // FK might already be dropped
                }
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('planting_location_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tasks' => 'task_id',
            'treatments' => 'treatment_id',
            'planting_location_notes' => 'planting_location_note_id',
            'attachments' => 'attachment_id',
        ];

        foreach ($tables as $tableName => $pkColumn) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'planting_location_id')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($pkColumn) {
                $table->string('planting_location_id', 36)->nullable()->after($pkColumn);
            });
        }
    }
};
