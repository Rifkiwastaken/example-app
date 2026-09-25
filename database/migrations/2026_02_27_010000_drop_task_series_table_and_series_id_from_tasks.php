<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus relasi dan tabel TaskSeries yang sudah tidak dipakai.
     */
    public function up(): void
    {
        // Hapus FK dan kolom series_id dari tasks (jika masih ada)
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'series_id')) {
            // Coba drop foreign key dengan beberapa kemungkinan nama, abaikan error jika tidak ada
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                foreach (['tasks_series_id_fk', 'tasks_series_id_foreign'] as $fkName) {
                    try {
                        DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `'.$fkName.'`');
                    } catch (\Throwable $e) {
                        // FK dengan nama ini mungkin tidak ada; bisa diabaikan
                    }
                }
            }

            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'series_id')) {
                    $table->dropColumn('series_id');
                }
            });
        }

        // Hapus tabel task_series jika masih ada
        if (Schema::hasTable('task_series')) {
            Schema::drop('task_series');
        }
    }

    /**
     * Rollback: buat kembali tabel & kolom minimal agar migration bisa di-rollback.
     */
    public function down(): void
    {
        // Buat ulang tabel task_series (minimal)
        if (!Schema::hasTable('task_series')) {
            Schema::create('task_series', function (Blueprint $table) {
                $table->string('task_series_id', 36)->primary();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('template_id', 36)->nullable();
                $table->json('series_tasks')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Tambah kembali kolom series_id ke tasks (tanpa FK untuk kesederhanaan)
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'series_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('series_id', 36)->nullable()->after('template_id');
            });
        }
    }
};

