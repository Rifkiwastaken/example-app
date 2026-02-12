<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1: Menambahkan kolom custom ID baru untuk tabel Support (Tasks, Expenses, Attachments).
     */
    public function up(): void
    {
        // 1. TASKS TABLE
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'task_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('task_id', 36)->nullable()->unique()->after('id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
                $table->string('new_assigned_to', 36)->nullable()->after('assigned_to');
                $table->string('new_task_series_id', 36)->nullable()->after('task_series_id');
            });
        }

        // 2. TASK_SERIES TABLE
        if (Schema::hasTable('task_series') && !Schema::hasColumn('task_series', 'task_series_id')) {
            Schema::table('task_series', function (Blueprint $table) {
                $table->string('task_series_id', 36)->nullable()->unique()->after('id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 3. TASK_TEMPLATES TABLE
        if (Schema::hasTable('task_templates') && !Schema::hasColumn('task_templates', 'task_template_id')) {
            Schema::table('task_templates', function (Blueprint $table) {
                $table->string('task_template_id', 36)->nullable()->unique()->after('id');
            });
        }

        // 4. EXPENSES TABLE
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'expense_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('expense_id', 36)->nullable()->unique()->after('id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
            });
        }

        // 5. ATTACHMENTS TABLE
        if (Schema::hasTable('attachments') && !Schema::hasColumn('attachments', 'attachment_id')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->string('attachment_id', 36)->nullable()->unique()->after('id');
                // attachable_id akan diupdate di fase 2 berdasarkan attachable_type
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attachments')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropColumn('attachment_id');
            });
        }

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn(['expense_id', 'new_user_id', 'new_planting_id']);
            });
        }

        if (Schema::hasTable('task_templates')) {
            Schema::table('task_templates', function (Blueprint $table) {
                $table->dropColumn('task_template_id');
            });
        }

        if (Schema::hasTable('task_series')) {
            Schema::table('task_series', function (Blueprint $table) {
                $table->dropColumn(['task_series_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn(['task_id', 'new_user_id', 'new_assigned_to', 'new_task_series_id']);
            });
        }
    }
};
