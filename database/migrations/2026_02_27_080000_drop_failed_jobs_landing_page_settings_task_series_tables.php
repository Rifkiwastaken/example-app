<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel: failed_jobs, landing_page_settings, task_series.
     */
    public function up(): void
    {
        if (Schema::hasTable('failed_jobs')) {
            Schema::dropIfExists('failed_jobs');
        }
        if (Schema::hasTable('landing_page_settings')) {
            Schema::dropIfExists('landing_page_settings');
        }
        if (Schema::hasTable('task_series')) {
            Schema::dropIfExists('task_series');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // failed_jobs - re-create minimal structure (Laravel default)
        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
        // landing_page_settings dan task_series tidak di-create ulang di down (bergantung migrasi asal)
    }
};
