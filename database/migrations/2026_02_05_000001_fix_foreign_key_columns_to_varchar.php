<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix all foreign key columns that should be VARCHAR(36) for custom string IDs
     */
    public function up(): void
    {
        // Fix treatments table
        if (Schema::hasColumn('treatments', 'responsible_person_id')) {
            DB::statement('ALTER TABLE `treatments` MODIFY `responsible_person_id` VARCHAR(36) NULL');
        }
        if (Schema::hasColumn('treatments', 'edited_by')) {
            DB::statement('ALTER TABLE `treatments` MODIFY `edited_by` VARCHAR(36) NULL');
        }

        // Fix nutrients table
        if (Schema::hasColumn('nutrients', 'responsible_person_id')) {
            DB::statement('ALTER TABLE `nutrients` MODIFY `responsible_person_id` VARCHAR(36) NULL');
        }
        if (Schema::hasColumn('nutrients', 'edited_by')) {
            DB::statement('ALTER TABLE `nutrients` MODIFY `edited_by` VARCHAR(36) NULL');
        }

        // Fix expenses table
        if (Schema::hasColumn('expenses', 'responsible_person_id')) {
            DB::statement('ALTER TABLE `expenses` MODIFY `responsible_person_id` VARCHAR(36) NULL');
        }
        if (Schema::hasColumn('expenses', 'edited_by')) {
            DB::statement('ALTER TABLE `expenses` MODIFY `edited_by` VARCHAR(36) NULL');
        }

        // Fix attachments table
        if (Schema::hasColumn('attachments', 'created_by')) {
            DB::statement('ALTER TABLE `attachments` MODIFY `created_by` VARCHAR(36) NULL');
        }
        if (Schema::hasColumn('attachments', 'edited_by')) {
            DB::statement('ALTER TABLE `attachments` MODIFY `edited_by` VARCHAR(36) NULL');
        }

        // Fix planting_location_notes table
        if (Schema::hasColumn('planting_location_notes', 'user_id')) {
            DB::statement('ALTER TABLE `planting_location_notes` MODIFY `user_id` VARCHAR(36) NULL');
        }
        if (Schema::hasColumn('planting_location_notes', 'assigned_to')) {
            DB::statement('ALTER TABLE `planting_location_notes` MODIFY `assigned_to` VARCHAR(36) NULL');
        }

        // Fix planting_location_photos table
        if (Schema::hasColumn('planting_location_photos', 'uploaded_by')) {
            DB::statement('ALTER TABLE `planting_location_photos` MODIFY `uploaded_by` VARCHAR(36) NULL');
        }

        // Fix plant_notes table
        if (Schema::hasColumn('plant_notes', 'user_id')) {
            DB::statement('ALTER TABLE `plant_notes` MODIFY `user_id` VARCHAR(36) NULL');
        }

        // Fix plant_photos table
        if (Schema::hasColumn('plant_photos', 'uploaded_by')) {
            DB::statement('ALTER TABLE `plant_photos` MODIFY `uploaded_by` VARCHAR(36) NULL');
        }

        // Fix harvests table
        if (Schema::hasColumn('harvests', 'harvested_by')) {
            DB::statement('ALTER TABLE `harvests` MODIFY `harvested_by` VARCHAR(36) NULL');
        }

        // Fix certification_reports table
        if (Schema::hasColumn('certification_reports', 'inspector_id')) {
            DB::statement('ALTER TABLE `certification_reports` MODIFY `inspector_id` VARCHAR(36) NULL');
        }
        if (Schema::hasColumn('certification_reports', 'created_by')) {
            DB::statement('ALTER TABLE `certification_reports` MODIFY `created_by` VARCHAR(36) NULL');
        }

        // Fix inventory_transactions table
        if (Schema::hasColumn('inventory_transactions', 'performed_by')) {
            DB::statement('ALTER TABLE `inventory_transactions` MODIFY `performed_by` VARCHAR(36) NULL');
        }

        // Fix sales table
        if (Schema::hasColumn('sales', 'created_by')) {
            DB::statement('ALTER TABLE `sales` MODIFY `created_by` VARCHAR(36) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting would require converting data back to integers which may cause data loss
    }
};
