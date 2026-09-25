<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pastikan tabel task_templates ada dan strukturnya sesuai relasi
     * (untuk fitur "Simpan sebagai Template").
     */
    public function up(): void
    {
        if (!Schema::hasTable('task_templates')) {
            Schema::create('task_templates', function (Blueprint $table) {
                $table->string('task_template_id', 36)->primary();
                $table->string('name');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->json('checklist')->nullable();
                $table->json('attachments')->nullable();
                $table->string('association', 50)->default('penanaman');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            return;
        }

        // Tambah kolom yang mungkin belum ada (mis. setelah migrasi lama)
        Schema::table('task_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('task_templates', 'title')) {
                $table->string('title')->nullable()->after('name');
            }
            if (!Schema::hasColumn('task_templates', 'checklist')) {
                $table->json('checklist')->nullable()->after('description');
            }
            if (!Schema::hasColumn('task_templates', 'attachments')) {
                $table->json('attachments')->nullable()->after('checklist');
            }
            if (!Schema::hasColumn('task_templates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('association');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak drop table; hanya memastikan struktur ada
    }
};
