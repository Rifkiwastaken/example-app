<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 3: Finalisasi untuk tabel Certification, Inventory, Sales, dan Support.
     */
    public function up(): void
    {
        // IMPORTANT: Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // ========== CERTIFICATION TABLES ==========
            
            // 1. CERTIFICATIONS TABLE
            if (Schema::hasTable('certifications')) {
                echo "Finalizing certifications table...\n";
                Schema::table('certifications', function (Blueprint $table) {
                    $table->dropForeign(['plant_id']);
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['plant_id', 'user_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('certifications', function (Blueprint $table) {
                    $table->string('certification_id', 36)->primary()->change();
                    $table->renameColumn('new_plant_id', 'plant_id');
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('certifications', function (Blueprint $table) {
                    $table->foreign('plant_id')->references('plant_id')->on('plants')->cascadeOnDelete();
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 2. CERTIFICATION_REPORTS TABLE
            if (Schema::hasTable('certification_reports')) {
                echo "Finalizing certification_reports table...\n";
                Schema::table('certification_reports', function (Blueprint $table) {
                    $table->dropForeign(['certification_id']);
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['certification_id', 'user_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('certification_reports', function (Blueprint $table) {
                    $table->string('certification_report_id', 36)->primary()->change();
                    $table->renameColumn('new_certification_id', 'certification_id');
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('certification_reports', function (Blueprint $table) {
                    $table->foreign('certification_id')->references('certification_id')->on('certifications')->cascadeOnDelete();
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // ========== INVENTORY & WAREHOUSE TABLES ==========
            
            // 3. WAREHOUSES TABLE
            if (Schema::hasTable('warehouses')) {
                echo "Finalizing warehouses table...\n";
                Schema::table('warehouses', function (Blueprint $table) {
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('warehouses', function (Blueprint $table) {
                    $table->string('warehouse_id', 36)->primary()->change();
                });
            }

            // 4. BINS TABLE
            if (Schema::hasTable('bins')) {
                echo "Finalizing bins table...\n";
                Schema::table('bins', function (Blueprint $table) {
                    $table->dropForeign(['warehouse_id']);
                    $table->dropColumn('warehouse_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('bins', function (Blueprint $table) {
                    $table->string('bin_id', 36)->primary()->change();
                    $table->renameColumn('new_warehouse_id', 'warehouse_id');
                });
                
                Schema::table('bins', function (Blueprint $table) {
                    $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses')->cascadeOnDelete();
                });
            }

            // 5. INVENTORY_TYPES TABLE
            if (Schema::hasTable('inventory_types')) {
                echo "Finalizing inventory_types table...\n";
                Schema::table('inventory_types', function (Blueprint $table) {
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_types', function (Blueprint $table) {
                    $table->string('inventory_type_id', 36)->primary()->change();
                });
            }

            // 6. INVENTORY_LOTS TABLE
            if (Schema::hasTable('inventory_lots')) {
                echo "Finalizing inventory_lots table...\n";
                Schema::table('inventory_lots', function (Blueprint $table) {
                    $table->dropForeign(['inventory_type_id']);
                    $table->dropForeign(['bin_id']);
                    $table->dropColumn(['inventory_type_id', 'bin_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_lots', function (Blueprint $table) {
                    $table->string('inventory_lot_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
                    $table->renameColumn('new_bin_id', 'bin_id');
                });
                
                Schema::table('inventory_lots', function (Blueprint $table) {
                    $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->cascadeOnDelete();
                    $table->foreign('bin_id')->references('bin_id')->on('bins')->nullOnDelete();
                });
            }

            // 7. INVENTORY_TRANSACTIONS TABLE
            if (Schema::hasTable('inventory_transactions')) {
                echo "Finalizing inventory_transactions table...\n";
                Schema::table('inventory_transactions', function (Blueprint $table) {
                    $table->dropForeign(['inventory_lot_id']);
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['inventory_lot_id', 'user_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_transactions', function (Blueprint $table) {
                    $table->string('inventory_transaction_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_lot_id', 'inventory_lot_id');
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('inventory_transactions', function (Blueprint $table) {
                    $table->foreign('inventory_lot_id')->references('inventory_lot_id')->on('inventory_lots')->cascadeOnDelete();
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 8. INVENTORY_TYPE_WAREHOUSES TABLE
            if (Schema::hasTable('inventory_type_warehouses')) {
                echo "Finalizing inventory_type_warehouses table...\n";
                Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                    $table->dropForeign(['inventory_type_id']);
                    $table->dropForeign(['warehouse_id']);
                    $table->dropColumn(['inventory_type_id', 'warehouse_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                    $table->string('inventory_type_warehouse_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
                    $table->renameColumn('new_warehouse_id', 'warehouse_id');
                });
                
                Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                    $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->cascadeOnDelete();
                    $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses')->cascadeOnDelete();
                });
            }

            // 9. INVENTORY_TYPE_SEEDS TABLE
            if (Schema::hasTable('inventory_type_seeds')) {
                echo "Finalizing inventory_type_seeds table...\n";
                Schema::table('inventory_type_seeds', function (Blueprint $table) {
                    $table->dropForeign(['inventory_type_id']);
                    $table->dropForeign(['plant_type_id']);
                    $table->dropColumn(['inventory_type_id', 'plant_type_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_type_seeds', function (Blueprint $table) {
                    $table->string('inventory_type_seed_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
                    $table->renameColumn('new_plant_type_id', 'plant_type_id');
                });
                
                Schema::table('inventory_type_seeds', function (Blueprint $table) {
                    $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->cascadeOnDelete();
                    $table->foreign('plant_type_id')->references('plant_type_id')->on('plant_types')->cascadeOnDelete();
                });
            }

            // 10. INVENTORY_TYPE_CERTIFICATION_REPORTS TABLE
            if (Schema::hasTable('inventory_type_certification_reports')) {
                echo "Finalizing inventory_type_certification_reports table...\n";
                Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                    $table->dropForeign(['inventory_type_id']);
                    $table->dropForeign(['certification_report_id']);
                    $table->dropColumn(['inventory_type_id', 'certification_report_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                    $table->string('inventory_type_certification_report_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
                    $table->renameColumn('new_certification_report_id', 'certification_report_id');
                });
                
                Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                    $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->cascadeOnDelete();
                    $table->foreign('certification_report_id')->references('certification_report_id')->on('certification_reports')->cascadeOnDelete();
                });
            }

            // 11. INVENTORY_NOTES TABLE
            if (Schema::hasTable('inventory_notes')) {
                echo "Finalizing inventory_notes table...\n";
                Schema::table('inventory_notes', function (Blueprint $table) {
                    $table->dropForeign(['inventory_lot_id']);
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['inventory_lot_id', 'user_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_notes', function (Blueprint $table) {
                    $table->string('inventory_note_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_lot_id', 'inventory_lot_id');
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('inventory_notes', function (Blueprint $table) {
                    $table->foreign('inventory_lot_id')->references('inventory_lot_id')->on('inventory_lots')->cascadeOnDelete();
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 12. INVENTORY_PHOTOS TABLE
            if (Schema::hasTable('inventory_photos')) {
                echo "Finalizing inventory_photos table...\n";
                Schema::table('inventory_photos', function (Blueprint $table) {
                    $table->dropForeign(['inventory_lot_id']);
                    $table->dropColumn('inventory_lot_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('inventory_photos', function (Blueprint $table) {
                    $table->string('inventory_photo_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_lot_id', 'inventory_lot_id');
                });
                
                Schema::table('inventory_photos', function (Blueprint $table) {
                    $table->foreign('inventory_lot_id')->references('inventory_lot_id')->on('inventory_lots')->cascadeOnDelete();
                });
            }

            // 13. SEED_HISTORIES TABLE
            if (Schema::hasTable('seed_histories')) {
                echo "Finalizing seed_histories table...\n";
                Schema::table('seed_histories', function (Blueprint $table) {
                    $table->dropForeign(['inventory_type_seed_id']);
                    $table->dropColumn('inventory_type_seed_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('seed_histories', function (Blueprint $table) {
                    $table->string('seed_history_id', 36)->primary()->change();
                    $table->renameColumn('new_inventory_type_seed_id', 'inventory_type_seed_id');
                });
                
                Schema::table('seed_histories', function (Blueprint $table) {
                    $table->foreign('inventory_type_seed_id')->references('inventory_type_seed_id')->on('inventory_type_seeds')->cascadeOnDelete();
                });
            }

            // ========== SALES TABLES ==========
            
            // 14. SALES TABLE
            if (Schema::hasTable('sales')) {
                echo "Finalizing sales table...\n";
                Schema::table('sales', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('sales', function (Blueprint $table) {
                    $table->string('sale_id', 36)->primary()->change();
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('sales', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 15. SALE_ITEMS TABLE
            if (Schema::hasTable('sale_items')) {
                echo "Finalizing sale_items table...\n";
                Schema::table('sale_items', function (Blueprint $table) {
                    $table->dropForeign(['sale_id']);
                    $table->dropForeign(['inventory_lot_id']);
                    $table->dropColumn(['sale_id', 'inventory_lot_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('sale_items', function (Blueprint $table) {
                    $table->string('sale_item_id', 36)->primary()->change();
                    $table->renameColumn('new_sale_id', 'sale_id');
                    $table->renameColumn('new_inventory_lot_id', 'inventory_lot_id');
                });
                
                Schema::table('sale_items', function (Blueprint $table) {
                    $table->foreign('sale_id')->references('sale_id')->on('sales')->cascadeOnDelete();
                    $table->foreign('inventory_lot_id')->references('inventory_lot_id')->on('inventory_lots')->cascadeOnDelete();
                });
            }

            // ========== SUPPORT TABLES ==========
            
            // 16. TASK_SERIES TABLE
            if (Schema::hasTable('task_series')) {
                echo "Finalizing task_series table...\n";
                Schema::table('task_series', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('task_series', function (Blueprint $table) {
                    $table->string('task_series_id', 36)->primary()->change();
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('task_series', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 17. TASK_TEMPLATES TABLE
            if (Schema::hasTable('task_templates')) {
                echo "Finalizing task_templates table...\n";
                Schema::table('task_templates', function (Blueprint $table) {
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('task_templates', function (Blueprint $table) {
                    $table->string('task_template_id', 36)->primary()->change();
                });
            }

            // 18. TASKS TABLE
            if (Schema::hasTable('tasks')) {
                echo "Finalizing tasks table...\n";
                Schema::table('tasks', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropForeign(['assigned_to']);
                    $table->dropForeign(['task_series_id']);
                    $table->dropColumn(['user_id', 'assigned_to', 'task_series_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('tasks', function (Blueprint $table) {
                    $table->string('task_id', 36)->primary()->change();
                    $table->renameColumn('new_user_id', 'user_id');
                    $table->renameColumn('new_assigned_to', 'assigned_to');
                    $table->renameColumn('new_task_series_id', 'task_series_id');
                });
                
                Schema::table('tasks', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                    $table->foreign('assigned_to')->references('user_id')->on('users')->nullOnDelete();
                    $table->foreign('task_series_id')->references('task_series_id')->on('task_series')->nullOnDelete();
                });
            }

            // 19. EXPENSES TABLE
            if (Schema::hasTable('expenses')) {
                echo "Finalizing expenses table...\n";
                Schema::table('expenses', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropForeign(['planting_id']);
                    $table->dropColumn(['user_id', 'planting_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('expenses', function (Blueprint $table) {
                    $table->string('expense_id', 36)->primary()->change();
                    $table->renameColumn('new_user_id', 'user_id');
                    $table->renameColumn('new_planting_id', 'planting_id');
                });
                
                Schema::table('expenses', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                    $table->foreign('planting_id')->references('planting_id')->on('plantings')->nullOnDelete();
                });
            }

            // 20. ATTACHMENTS TABLE
            if (Schema::hasTable('attachments')) {
                echo "Finalizing attachments table...\n";
                Schema::table('attachments', function (Blueprint $table) {
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('attachments', function (Blueprint $table) {
                    $table->string('attachment_id', 36)->primary()->change();
                });
                
                // Note: attachable_id is polymorphic, will need manual update based on attachable_type
            }

            echo "Phase 3 Remaining tables finalization completed!\n";

        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive migration, rollback is not recommended
        // You should restore from backup instead
        throw new Exception('Phase 3 rollback is not supported. Please restore from backup.');
    }
};
