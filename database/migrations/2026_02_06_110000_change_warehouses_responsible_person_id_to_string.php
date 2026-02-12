<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah responsible_person_id ke string(36) agar sesuai users.user_id (custom ID).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('warehouses', 'responsible_person_id')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $fkName = DB::selectOne("
                SELECT CONSTRAINT_NAME as name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'warehouses'
                  AND COLUMN_NAME = 'responsible_person_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            if ($fkName && !empty($fkName->name)) {
                DB::statement('ALTER TABLE warehouses DROP FOREIGN KEY ' . $fkName->name);
            }
        } else {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropForeign(['responsible_person_id']);
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE warehouses MODIFY responsible_person_id VARCHAR(36) NULL');
        } else {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->string('responsible_person_id', 36)->nullable()->change();
            });
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign('responsible_person_id', 'warehouses_responsible_person_fk')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign('warehouses_responsible_person_fk');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE warehouses MODIFY responsible_person_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->unsignedBigInteger('responsible_person_id')->nullable()->change();
            });
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign('responsible_person_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
