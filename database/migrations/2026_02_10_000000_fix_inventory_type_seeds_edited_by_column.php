<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix edited_by column in inventory_type_seeds to VARCHAR(36) for custom user_id (string)
     */
    public function up(): void
    {
        if (!Schema::hasColumn('inventory_type_seeds', 'edited_by')) {
            return;
        }

        // Get foreign key name for edited_by column
        $dbName = DB::connection()->getDatabaseName();
        $fkName = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'inventory_type_seeds'
            AND COLUMN_NAME = 'edited_by' AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName]);

        if ($fkName) {
            DB::statement("ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `{$fkName->CONSTRAINT_NAME}`");
        }

        DB::statement('ALTER TABLE `inventory_type_seeds` MODIFY `edited_by` VARCHAR(36) NULL');

        DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('inventory_type_seeds', 'edited_by')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inventory_type_seeds_edited_by_foreign`');
        } catch (\Exception $e) {
            //
        }
        DB::statement('ALTER TABLE `inventory_type_seeds` MODIFY `edited_by` BIGINT UNSIGNED NULL');
    }
};
