<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesuaikan struktur task_templates agar mirip dengan tasks:
     * simpan langsung title, description, checklist, attachments.
     */
    public function up(): void
    {
        if (Schema::hasTable('task_templates')) {
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
                if (Schema::hasColumn('task_templates', 'tasks_list')) {
                    $table->dropColumn('tasks_list');
                }
            });
        }
    }

    /**
     * Rollback: kembalikan kolom tasks_list, hapus kolom baru.
     */
    public function down(): void
    {
        if (Schema::hasTable('task_templates')) {
            Schema::table('task_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('task_templates', 'tasks_list')) {
                    $table->json('tasks_list')->nullable();
                }
                if (Schema::hasColumn('task_templates', 'attachments')) {
                    $table->dropColumn('attachments');
                }
                if (Schema::hasColumn('task_templates', 'checklist')) {
                    $table->dropColumn('checklist');
                }
                if (Schema::hasColumn('task_templates', 'title')) {
                    $table->dropColumn('title');
                }
            });
        }
    }
};

