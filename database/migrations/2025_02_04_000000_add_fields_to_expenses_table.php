<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Add new fields for upah pekerja and lainnya
            if (!Schema::hasColumn('expenses', 'work_name')) {
                $table->string('work_name')->nullable()->after('expense_name');
            }
            if (!Schema::hasColumn('expenses', 'work_date')) {
                $table->date('work_date')->nullable()->after('expense_date');
            }
            if (!Schema::hasColumn('expenses', 'work_description')) {
                $table->text('work_description')->nullable()->after('work_date');
            }
            if (!Schema::hasColumn('expenses', 'worker_name')) {
                $table->string('worker_name')->nullable()->after('work_description');
            }
            if (!Schema::hasColumn('expenses', 'planting_id')) {
                $table->string('planting_id', 36)->nullable()->after('worker_name')->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('set null');
            }
            if (!Schema::hasColumn('expenses', 'description')) {
                $table->text('description')->nullable()->after('planting_id');
            }
            if (!Schema::hasColumn('expenses', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('expenses', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->after('edited_at')->onDelete('set null');
            }
        });

        // Update expense_type enum to include new types
        DB::statement("ALTER TABLE expenses MODIFY COLUMN expense_type ENUM('perawatan', 'nutrisi', 'upah_pekerja', 'lainnya')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'edited_by')) {
                $table->dropForeign(['edited_by']);
                $table->dropColumn('edited_by');
            }
            if (Schema::hasColumn('expenses', 'edited_at')) {
                $table->dropColumn('edited_at');
            }
            if (Schema::hasColumn('expenses', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('expenses', 'planting_id')) {
                $table->dropForeign(['planting_id']);
                $table->dropColumn('planting_id');
            }
            if (Schema::hasColumn('expenses', 'worker_name')) {
                $table->dropColumn('worker_name');
            }
            if (Schema::hasColumn('expenses', 'work_description')) {
                $table->dropColumn('work_description');
            }
            if (Schema::hasColumn('expenses', 'work_date')) {
                $table->dropColumn('work_date');
            }
            if (Schema::hasColumn('expenses', 'work_name')) {
                $table->dropColumn('work_name');
            }
        });

        // Revert expense_type enum
        DB::statement("ALTER TABLE expenses MODIFY COLUMN expense_type ENUM('perawatan', 'nutrisi')");
    }
};

