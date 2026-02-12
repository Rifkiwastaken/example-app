<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3 - Level 4: Finalization ===\n";
        echo "Tables: inventory_transactions, inventory_type_warehouses, inventory_type_certification_reports, sale_items, expenses, nutrients, treatments, attachments, seed_histories, planting_losses, plant_notes, plant_photos, planting_location_notes, planting_location_photos, inventory_notes, inventory_photos, user_planting_location_land_manager, user_planting_location_land_worker\n\n";
        echo "⚠️  WARNING: This will drop old ID columns and restructure tables!\n";
        echo "Make sure Phase 2 completed successfully before proceeding.\n\n";
        
        // inventory_transactions: Finalize structure
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('bin_id');
            $table->renameColumn('new_bin_id', 'bin_id');
            $table->dropColumn('inventory_lot_id');
            $table->renameColumn('new_inventory_lot_id', 'inventory_lot_id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
            $table->dropColumn('warehouse_id');
            $table->renameColumn('new_warehouse_id', 'warehouse_id');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->primary('inventory_transaction_id');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign('bin_id')->references('bin_id')->on('bins')->onDelete('cascade');
            $table->foreign('inventory_lot_id')->references('inventory_lot_id')->on('inventory_lots')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses')->onDelete('cascade');
        });

        // inventory_type_warehouses: Finalize structure
        Schema::table('inventory_type_warehouses', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('bin_id');
            $table->renameColumn('new_bin_id', 'bin_id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('warehouse_id');
            $table->renameColumn('new_warehouse_id', 'warehouse_id');
        });

        Schema::table('inventory_type_warehouses', function (Blueprint $table) {
            $table->primary('inventory_type_warehous_id');
        });

        Schema::table('inventory_type_warehouses', function (Blueprint $table) {
            $table->foreign('bin_id')->references('bin_id')->on('bins')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses')->onDelete('cascade');
        });

        // inventory_type_certification_reports: Finalize structure
        Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('certification_report_id');
            $table->renameColumn('new_certification_report_id', 'certification_report_id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
        });

        Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
            $table->primary('inventory_type_certification_report_id');
        });

        Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
            $table->foreign('certification_report_id')->references('certification_report_id')->on('certification_reports')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
        });

        // sale_items: Finalize structure
        Schema::table('sale_items', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('inventory_lot_id');
            $table->renameColumn('new_inventory_lot_id', 'inventory_lot_id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('sale_id');
            $table->renameColumn('new_sale_id', 'sale_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->primary('sale_item_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreign('inventory_lot_id')->references('inventory_lot_id')->on('inventory_lots')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('sale_id')->references('sale_id')->on('sales')->onDelete('cascade');
        });

        // expenses: Finalize structure
        Schema::table('expenses', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('edited_by');
            $table->renameColumn('new_edited_by', 'edited_by');
            $table->dropColumn('nutrient_id');
            $table->renameColumn('new_nutrient_id', 'nutrient_id');
            $table->dropColumn('planting_id');
            $table->renameColumn('new_planting_id', 'planting_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('responsible_person_id');
            $table->renameColumn('new_responsible_person_id', 'responsible_person_id');
            $table->dropColumn('treatment_id');
            $table->renameColumn('new_treatment_id', 'treatment_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->primary('expense_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('nutrient_id')->references('nutrient_id')->on('nutrients')->onDelete('cascade');
            $table->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('responsible_person_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('treatment_id')->references('treatment_id')->on('treatments')->onDelete('cascade');
        });

        // nutrients: Finalize structure
        Schema::table('nutrients', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('edited_by');
            $table->renameColumn('new_edited_by', 'edited_by');
            $table->dropColumn('planting_id');
            $table->renameColumn('new_planting_id', 'planting_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('responsible_person_id');
            $table->renameColumn('new_responsible_person_id', 'responsible_person_id');
        });

        Schema::table('nutrients', function (Blueprint $table) {
            $table->primary('nutrient_id');
        });

        Schema::table('nutrients', function (Blueprint $table) {
            $table->foreign('edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('responsible_person_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // treatments: Finalize structure
        Schema::table('treatments', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('edited_by');
            $table->renameColumn('new_edited_by', 'edited_by');
            $table->dropColumn('planting_id');
            $table->renameColumn('new_planting_id', 'planting_id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('responsible_person_id');
            $table->renameColumn('new_responsible_person_id', 'responsible_person_id');
        });

        Schema::table('treatments', function (Blueprint $table) {
            $table->primary('treatment_id');
        });

        Schema::table('treatments', function (Blueprint $table) {
            $table->foreign('edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('responsible_person_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // attachments: Finalize structure
        Schema::table('attachments', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('created_by');
            $table->renameColumn('new_created_by', 'created_by');
            $table->dropColumn('edited_by');
            $table->renameColumn('new_edited_by', 'edited_by');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->primary('attachment_id');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('edited_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
        });

        // seed_histories: Finalize structure
        Schema::table('seed_histories', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('inventory_type_seed_id');
            $table->renameColumn('new_inventory_type_seed_id', 'inventory_type_seed_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('seed_histories', function (Blueprint $table) {
            $table->primary('seed_history_id');
        });

        Schema::table('seed_histories', function (Blueprint $table) {
            $table->foreign('inventory_type_seed_id')->references('inventory_type_seed_id')->on('inventory_type_seeds')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // planting_losses: Finalize structure
        Schema::table('planting_losses', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_id');
            $table->renameColumn('new_planting_id', 'planting_id');
        });

        Schema::table('planting_losses', function (Blueprint $table) {
            $table->primary('planting_loss_id');
        });

        Schema::table('planting_losses', function (Blueprint $table) {
            $table->foreign('planting_id')->references('planting_id')->on('plantings')->onDelete('cascade');
        });

        // plant_notes: Finalize structure
        Schema::table('plant_notes', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
        });

        Schema::table('plant_notes', function (Blueprint $table) {
            $table->primary('plant_note_id');
        });

        Schema::table('plant_notes', function (Blueprint $table) {
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
        });

        // plant_photos: Finalize structure
        Schema::table('plant_photos', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('plant_id');
            $table->renameColumn('new_plant_id', 'plant_id');
        });

        Schema::table('plant_photos', function (Blueprint $table) {
            $table->primary('plant_photo_id');
        });

        Schema::table('plant_photos', function (Blueprint $table) {
            $table->foreign('plant_id')->references('plant_id')->on('plants')->onDelete('cascade');
        });

        // planting_location_notes: Finalize structure
        Schema::table('planting_location_notes', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('planting_location_notes', function (Blueprint $table) {
            $table->primary('planting_location_note_id');
        });

        Schema::table('planting_location_notes', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // planting_location_photos: Finalize structure
        Schema::table('planting_location_photos', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
        });

        Schema::table('planting_location_photos', function (Blueprint $table) {
            $table->primary('planting_location_photo_id');
        });

        Schema::table('planting_location_photos', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
        });

        // inventory_notes: Finalize structure
        Schema::table('inventory_notes', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('inventory_notes', function (Blueprint $table) {
            $table->primary('inventory_note_id');
        });

        Schema::table('inventory_notes', function (Blueprint $table) {
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // inventory_photos: Finalize structure
        Schema::table('inventory_photos', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('inventory_type_id');
            $table->renameColumn('new_inventory_type_id', 'inventory_type_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('inventory_photos', function (Blueprint $table) {
            $table->primary('inventory_photo_id');
        });

        Schema::table('inventory_photos', function (Blueprint $table) {
            $table->foreign('inventory_type_id')->references('inventory_type_id')->on('inventory_types')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // user_planting_location_land_manager: Finalize structure
        Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
            $table->primary('user_planting_location_land_manager_id');
        });

        Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // user_planting_location_land_worker: Finalize structure
        Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
            // $table->dropForeign(''); // Will be recreated
            // $table->dropForeign(''); // Will be recreated
            $table->dropColumn('id');
            $table->dropColumn('planting_location_id');
            $table->renameColumn('new_planting_location_id', 'planting_location_id');
            $table->dropColumn('user_id');
            $table->renameColumn('new_user_id', 'user_id');
        });

        Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
            $table->primary('user_planting_location_land_worker_id');
        });

        Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });


        echo "\n✓ Level 4 finalization completed!\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  WARNING: Phase 3 rollback is complex!\n";
        echo "It's recommended to restore from backup instead.\n\n";
        
        // Reversing inventory_transactions is complex - restore from backup if needed
        // Schema::table('inventory_transactions', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_type_warehouses is complex - restore from backup if needed
        // Schema::table('inventory_type_warehouses', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_type_certification_reports is complex - restore from backup if needed
        // Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing sale_items is complex - restore from backup if needed
        // Schema::table('sale_items', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing expenses is complex - restore from backup if needed
        // Schema::table('expenses', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing nutrients is complex - restore from backup if needed
        // Schema::table('nutrients', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing treatments is complex - restore from backup if needed
        // Schema::table('treatments', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing attachments is complex - restore from backup if needed
        // Schema::table('attachments', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing seed_histories is complex - restore from backup if needed
        // Schema::table('seed_histories', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing planting_losses is complex - restore from backup if needed
        // Schema::table('planting_losses', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing plant_notes is complex - restore from backup if needed
        // Schema::table('plant_notes', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing plant_photos is complex - restore from backup if needed
        // Schema::table('plant_photos', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing planting_location_notes is complex - restore from backup if needed
        // Schema::table('planting_location_notes', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing planting_location_photos is complex - restore from backup if needed
        // Schema::table('planting_location_photos', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_notes is complex - restore from backup if needed
        // Schema::table('inventory_notes', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing inventory_photos is complex - restore from backup if needed
        // Schema::table('inventory_photos', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing user_planting_location_land_manager is complex - restore from backup if needed
        // Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

        // Reversing user_planting_location_land_worker is complex - restore from backup if needed
        // Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
        //     // Add back old id column, etc.
        // });

    }
};