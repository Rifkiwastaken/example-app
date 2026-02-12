<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add new columns for advanced task management
            $table->text('task_report')->nullable()->after('description');
            $table->json('checklist')->nullable()->after('task_report');
            $table->json('attachments')->nullable()->after('checklist');
            $table->enum('association', ['penanaman', 'sertifikasi', 'gudang', 'penjualan'])->nullable()->after('attachments');
            $table->enum('new_status', ['dilakukan', 'dalam_progress', 'selesai', 'tidak_selesai', 'terlewat', 'ditinggalkan'])->default('dilakukan')->after('association');
            $table->string('assigned_to', 36)->nullable()->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->after('new_status');
            $table->enum('new_priority', ['tertinggi', 'tinggi', 'medium', 'rendah', 'sangat_rendah'])->default('medium')->after('assigned_to');
            $table->date('start_date')->nullable()->after('new_priority');
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('due_time')->nullable()->after('due_date');
            $table->string('template_id', 36)->nullable()->after('due_time');
            $table->string('series_id', 36)->nullable()->after('template_id');
        });

        // Add foreign key constraints after table modification
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('template_id')->references('task_template_id')->on('task_templates')->onDelete('set null');
            $table->foreign('series_id')->references('task_series_id')->on('task_series')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['template_id']);
            $table->dropForeign(['series_id']);
            
            $table->dropColumn([
                'task_report', 'checklist', 'attachments', 'association', 'new_status', 
                'assigned_to', 'new_priority', 'start_date', 'start_time', 'due_time', 
                'template_id', 'series_id'
            ]);
        });
    }
};


















