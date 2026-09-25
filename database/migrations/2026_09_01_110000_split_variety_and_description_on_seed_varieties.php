<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Varietas (nama unik) dan deskripsi (teks bebas) dipisah di seed_varieties.
     */
    public function up(): void
    {
        if (!Schema::hasTable('seed_varieties')) {
            return;
        }

        if (!Schema::hasColumn('seed_varieties', 'variety')) {
            Schema::table('seed_varieties', function (Blueprint $table) {
                $table->string('variety', 255)->nullable()->after('seed_commodity_id');
            });

            DB::statement('UPDATE `seed_varieties` SET `variety` = `description` WHERE `variety` IS NULL');
        }

        $indexes = collect(DB::select('SHOW INDEX FROM `seed_varieties`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (in_array('seed_varieties_description_unique', $indexes, true)) {
            Schema::table('seed_varieties', function (Blueprint $table) {
                $table->dropUnique('seed_varieties_description_unique');
            });
        }

        DB::statement('ALTER TABLE `seed_varieties` MODIFY `description` TEXT NULL');
        DB::statement('UPDATE `seed_varieties` SET `description` = NULL');
        DB::statement('ALTER TABLE `seed_varieties` MODIFY `variety` VARCHAR(255) NOT NULL');

        $indexes = collect(DB::select('SHOW INDEX FROM `seed_varieties`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (!in_array('seed_varieties_variety_unique', $indexes, true)) {
            Schema::table('seed_varieties', function (Blueprint $table) {
                $table->unique('variety', 'seed_varieties_variety_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('seed_varieties') || !Schema::hasColumn('seed_varieties', 'variety')) {
            return;
        }

        DB::statement("UPDATE `seed_varieties` SET `description` = `variety` WHERE `description` IS NULL OR `description` = ''");

        Schema::table('seed_varieties', function (Blueprint $table) {
            if (Schema::hasColumn('seed_varieties', 'variety')) {
                $table->dropUnique('seed_varieties_variety_unique');
            }
        });

        DB::statement('ALTER TABLE `seed_varieties` MODIFY `description` VARCHAR(255) NOT NULL');

        Schema::table('seed_varieties', function (Blueprint $table) {
            $table->unique('description', 'seed_varieties_description_unique');
            $table->dropColumn('variety');
        });
    }
};
