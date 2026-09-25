<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Drop table user_planting_location_land_manager.
     * - Rename PK of user_planting_location_land_worker from user_planting_location_land_manager_id to user_planting_location_worker_id.
     * - Add role column (petugas_lapangan, penangkar) to user_planting_location_land_worker.
     */
    public function up(): void
    {
        // Drop manager table first (no FKs point to it from other tables in app)
        Schema::dropIfExists('user_planting_location_land_manager');

        // Worker table: rename PK column and add role
        if (Schema::hasTable('user_planting_location_land_worker')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                // Drop foreign keys that reference this table's columns
                $fks = DB::select("
                    SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_planting_location_land_worker'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($fks as $fk) {
                    Schema::table('user_planting_location_land_worker', function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    });
                }

                // Check if old PK column exists (typo name)
                $hasOldPk = Schema::hasColumn('user_planting_location_land_worker', 'user_planting_location_land_manager_id');
                $hasNewPk = Schema::hasColumn('user_planting_location_land_worker', 'user_planting_location_worker_id');

                if ($hasOldPk && !$hasNewPk) {
                    DB::statement('ALTER TABLE user_planting_location_land_worker DROP PRIMARY KEY');
                    DB::statement('ALTER TABLE user_planting_location_land_worker CHANGE COLUMN user_planting_location_land_manager_id user_planting_location_worker_id VARCHAR(36) NOT NULL');
                    DB::statement('ALTER TABLE user_planting_location_land_worker ADD PRIMARY KEY (user_planting_location_worker_id)');
                }
            }

            if (!Schema::hasColumn('user_planting_location_land_worker', 'role')) {
                Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                    $table->string('role', 50)->default('petugas_lapangan')->after('user_id');
                });
                DB::table('user_planting_location_land_worker')->whereNull('role')->update(['role' => 'petugas_lapangan']);
            }

            // Re-add foreign keys for worker table (planting_location_id, user_id)
            if ($driver === 'mysql' && !$this->foreignKeyExists('user_planting_location_land_worker', 'user_planting_location_land_worker_planting_location_id_foreign')) {
                Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                    $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
                    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                });
            }
        }
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        $result = DB::select("
            SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $name]);
        return !empty($result);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate manager table (minimal structure)
        Schema::create('user_planting_location_land_manager', function (Blueprint $table) {
            $table->string('user_planting_location_land_manager_id', 36)->primary();
            $table->string('planting_location_id', 36);
            $table->string('user_id', 36);
            $table->timestamps();
            $table->unique(['planting_location_id', 'user_id'], 'planting_location_land_manager_user_unique');
        });

        if (Schema::hasTable('user_planting_location_land_worker')) {
            Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                if (Schema::hasColumn('user_planting_location_land_worker', 'role')) {
                    $table->dropColumn('role');
                }
            });

            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql' && Schema::hasColumn('user_planting_location_land_worker', 'user_planting_location_worker_id')) {
                $fks = DB::select("
                    SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_planting_location_land_worker'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($fks as $fk) {
                    Schema::table('user_planting_location_land_worker', function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    });
                }
                DB::statement('ALTER TABLE user_planting_location_land_worker CHANGE COLUMN user_planting_location_worker_id user_planting_location_land_manager_id VARCHAR(36) NOT NULL');
                DB::statement('ALTER TABLE user_planting_location_land_worker ADD PRIMARY KEY (user_planting_location_land_manager_id)');
            }
        }
    }
};
