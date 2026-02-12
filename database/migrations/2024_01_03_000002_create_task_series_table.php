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
        Schema::create('task_series', function (Blueprint $table) {
            $table->string('task_series_id', 36)->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template_id', 36)->onDelete('cascade');
            $table->json('series_tasks')->nullable(); // JSON array of tasks in series
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_series');
    }
};
