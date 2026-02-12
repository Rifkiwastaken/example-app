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
            if (!Schema::hasColumn('tasks', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('location_tagged')->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tasks', 'planting_id')) {
                $table->string('planting_id', 36)->nullable()->after('planting_location_id')->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tasks', 'task_color')) {
                $table->string('task_color')->nullable()->after('planting_id');
            }
            if (!Schema::hasColumn('tasks', 'collaborators')) {
                $table->json('collaborators')->nullable()->after('assigned_to');
            }
            if (!Schema::hasColumn('tasks', 'repeats')) {
                $table->string('repeats')->nullable()->after('due_time');
            }
            if (!Schema::hasColumn('tasks', 'hours_spent')) {
                $table->decimal('hours_spent', 8, 2)->nullable()->after('repeats');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'planting_location_id')) {
                $table->dropForeign(['planting_location_id']);
                $table->dropColumn('planting_location_id');
            }
            if (Schema::hasColumn('tasks', 'planting_id')) {
                $table->dropForeign(['planting_id']);
                $table->dropColumn('planting_id');
            }
            if (Schema::hasColumn('tasks', 'task_color')) {
                $table->dropColumn('task_color');
            }
            if (Schema::hasColumn('tasks', 'collaborators')) {
                $table->dropColumn('collaborators');
            }
            if (Schema::hasColumn('tasks', 'repeats')) {
                $table->dropColumn('repeats');
            }
            if (Schema::hasColumn('tasks', 'hours_spent')) {
                $table->dropColumn('hours_spent');
            }
        });
    }
};

