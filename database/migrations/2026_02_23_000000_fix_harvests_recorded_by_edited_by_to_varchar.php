<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kolom recorded_by dan edited_by harus VARCHAR(36) karena users.user_id adalah custom string (USR-xxx).
     */
    public function up(): void
    {
        if (!Schema::hasTable('harvests')) {
            return;
        }

        $dropFkAndModify = function (string $column, string $defaultFkName): void {
            if (!Schema::hasColumn('harvests', $column)) {
                return;
            }
            $fkName = DB::selectOne("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'harvests' AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$column]);
            if ($fkName && !empty($fkName->CONSTRAINT_NAME)) {
                try {
                    DB::statement("ALTER TABLE `harvests` DROP FOREIGN KEY `{$fkName->CONSTRAINT_NAME}`");
                } catch (\Throwable $e) {
                    try {
                        DB::statement("ALTER TABLE `harvests` DROP FOREIGN KEY `{$defaultFkName}`");
                    } catch (\Throwable $e2) {
                    }
                }
            } else {
                try {
                    DB::statement("ALTER TABLE `harvests` DROP FOREIGN KEY `{$defaultFkName}`");
                } catch (\Throwable $e) {
                }
            }
            DB::statement("ALTER TABLE `harvests` MODIFY `{$column}` VARCHAR(36) NULL");
            // Kosongkan nilai yang bukan format user_id (USR-xxx) agar FK tidak gagal
            DB::statement("UPDATE harvests SET `{$column}` = NULL WHERE `{$column}` IS NOT NULL AND `{$column}` NOT LIKE 'USR-%'");
            DB::statement("ALTER TABLE `harvests` ADD CONSTRAINT `{$defaultFkName}` FOREIGN KEY (`{$column}`) REFERENCES `users`(`user_id`) ON DELETE SET NULL");
        };

        $dropFkAndModify('recorded_by', 'harvests_recorded_by_foreign');
        $dropFkAndModify('edited_by', 'harvests_edited_by_foreign');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ke bigint tidak aman jika sudah ada user_id string
    }
};
