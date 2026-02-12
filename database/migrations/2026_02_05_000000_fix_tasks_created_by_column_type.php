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
        // Change column type from integer to varchar(36) directly
        // First check if the column exists
        if (Schema::hasColumn('tasks', 'created_by')) {
            DB::statement('ALTER TABLE `tasks` MODIFY `created_by` VARCHAR(36) NULL');
        }

        // Also fix last_edited_by if it exists and is integer
        if (Schema::hasColumn('tasks', 'last_edited_by')) {
            DB::statement('ALTER TABLE `tasks` MODIFY `last_edited_by` VARCHAR(36) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data type change, reversing might cause data loss
        // Only reverse if the column values can be converted back to integers
    }
};
