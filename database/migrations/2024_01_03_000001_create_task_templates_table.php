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
        Schema::create('task_templates', function (Blueprint $table) {
            $table->string('task_template_id', 36)->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('tasks_list')->nullable(); // JSON array of task configurations
            $table->enum('association', ['penanaman', 'sertifikasi', 'gudang', 'penjualan']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};


















