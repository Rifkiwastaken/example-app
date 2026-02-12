<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Allow seed_histories to persist when seed is deleted (SET NULL) and add inventory_type_id for querying
     */
    public function up(): void
    {
        Schema::table('seed_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('seed_histories', 'inventory_type_id')) {
                $table->string('inventory_type_id', 36)->nullable()->after('seed_history_id');
            }
        });

        // Backfill inventory_type_id from seed
        DB::statement('
            UPDATE seed_histories sh
            INNER JOIN inventory_type_seeds its ON sh.inventory_type_seed_id = its.inventory_type_seed_id
            SET sh.inventory_type_id = its.inventory_type_id
        ');

        // Drop FK, modify column to nullable, recreate FK with SET NULL
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP FOREIGN KEY `seed_histories_inventory_type_seed_id_foreign`');
        } catch (\Exception $e) {
            try {
                DB::statement('ALTER TABLE `seed_histories` DROP FOREIGN KEY `seed_histories_inventory_type_seed_id_inventory_type_seeds_inventory_type_seed_id_foreign`');
            } catch (\Exception $e2) {
                // Try to find FK name
                $fk = DB::selectOne("
                    SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'seed_histories' AND COLUMN_NAME = 'inventory_type_seed_id' AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [DB::connection()->getDatabaseName()]);
                if ($fk) {
                    DB::statement("ALTER TABLE `seed_histories` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }
            }
        }

        DB::statement('ALTER TABLE `seed_histories` MODIFY `inventory_type_seed_id` VARCHAR(36) NULL');

        Schema::table('seed_histories', function (Blueprint $table) {
            $table->foreign('inventory_type_seed_id')
                ->references('inventory_type_seed_id')
                ->on('inventory_type_seeds')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP FOREIGN KEY `seed_histories_inventory_type_seed_id_foreign`');
        } catch (\Exception $e) {}
        DB::statement('ALTER TABLE `seed_histories` MODIFY `inventory_type_seed_id` VARCHAR(36) NOT NULL');
        Schema::table('seed_histories', function (Blueprint $table) {
            $table->foreign('inventory_type_seed_id')
                ->references('inventory_type_seed_id')
                ->on('inventory_type_seeds')
                ->onDelete('cascade');
        });
        if (Schema::hasColumn('seed_histories', 'inventory_type_id')) {
            Schema::table('seed_histories', function (Blueprint $table) {
                $table->dropColumn('inventory_type_id');
            });
        }
    }
};
