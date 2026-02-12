<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1 - Level 4: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tables: inventory_transactions, inventory_type_warehouses, inventory_type_certification_reports, sale_items, expenses, nutrients, treatments, attachments, seed_histories, planting_losses, plant_notes, plant_photos, planting_location_notes, planting_location_photos, inventory_notes, inventory_photos, user_planting_location_land_manager, user_planting_location_land_worker
     */
    public function up(): void
    {
        // INVENTORY_TRANSACTIONS TABLE
        if (Schema::hasTable('inventory_transactions') && !Schema::hasColumn('inventory_transactions', 'inventory_transaction_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->string('inventory_transaction_id', 36)->nullable()->unique('inventory_transactio_inventory_transactio_unq')->after('id');
                $table->string('new_bin_id', 36)->nullable()->after('bin_id');
                $table->string('new_inventory_lot_id', 36)->nullable()->after('inventory_lot_id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
                $table->string('new_warehouse_id', 36)->nullable()->after('warehouse_id');
            });
        }

        // INVENTORY_TYPE_WAREHOUSES TABLE
        if (Schema::hasTable('inventory_type_warehouses') && !Schema::hasColumn('inventory_type_warehouses', 'inventory_type_warehous_id')) {
            Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                $table->string('inventory_type_warehous_id', 36)->nullable()->unique('inventory_type_wareh_inventory_type_wareh_unq')->after('id');
                $table->string('new_bin_id', 36)->nullable()->after('bin_id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_warehouse_id', 36)->nullable()->after('warehouse_id');
            });
        }

        // INVENTORY_TYPE_CERTIFICATION_REPORTS TABLE
        if (Schema::hasTable('inventory_type_certification_reports') && !Schema::hasColumn('inventory_type_certification_reports', 'inventory_type_certification_report_id')) {
            Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                $table->string('inventory_type_certification_report_id', 36)->nullable()->unique('inventory_type_certi_inventory_type_certi_unq')->after('id');
                $table->string('new_certification_report_id', 36)->nullable()->after('certification_report_id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
            });
        }

        // SALE_ITEMS TABLE
        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'sale_item_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->string('sale_item_id', 36)->nullable()->unique('sale_items_sale_item_id_unq')->after('id');
                $table->string('new_inventory_lot_id', 36)->nullable()->after('inventory_lot_id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_sale_id', 36)->nullable()->after('sale_id');
            });
        }

        // EXPENSES TABLE
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'expense_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('expense_id', 36)->nullable()->unique('expenses_expense_id_unq')->after('id');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
                $table->string('new_nutrient_id', 36)->nullable()->after('nutrient_id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_responsible_person_id', 36)->nullable()->after('responsible_person_id');
                $table->string('new_treatment_id', 36)->nullable()->after('treatment_id');
            });
        }

        // NUTRIENTS TABLE
        if (Schema::hasTable('nutrients') && !Schema::hasColumn('nutrients', 'nutrient_id')) {
            Schema::table('nutrients', function (Blueprint $table) {
                $table->string('nutrient_id', 36)->nullable()->unique('nutrients_nutrient_id_unq')->after('id');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_responsible_person_id', 36)->nullable()->after('responsible_person_id');
            });
        }

        // TREATMENTS TABLE
        if (Schema::hasTable('treatments') && !Schema::hasColumn('treatments', 'treatment_id')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->string('treatment_id', 36)->nullable()->unique('treatments_treatment_id_unq')->after('id');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_responsible_person_id', 36)->nullable()->after('responsible_person_id');
            });
        }

        // ATTACHMENTS TABLE
        if (Schema::hasTable('attachments') && !Schema::hasColumn('attachments', 'attachment_id')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->string('attachment_id', 36)->nullable()->unique('attachments_attachment_id_unq')->after('id');
                $table->string('new_created_by', 36)->nullable()->after('created_by');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // SEED_HISTORIES TABLE
        if (Schema::hasTable('seed_histories') && !Schema::hasColumn('seed_histories', 'seed_history_id')) {
            Schema::table('seed_histories', function (Blueprint $table) {
                $table->string('seed_history_id', 36)->nullable()->unique('seed_histories_seed_history_id_unq')->after('id');
                $table->string('new_inventory_type_seed_id', 36)->nullable()->after('inventory_type_seed_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // PLANTING_LOSSES TABLE
        if (Schema::hasTable('planting_losses') && !Schema::hasColumn('planting_losses', 'planting_loss_id')) {
            Schema::table('planting_losses', function (Blueprint $table) {
                $table->string('planting_loss_id', 36)->nullable()->unique('planting_losses_planting_loss_id_unq')->after('id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
            });
        }

        // PLANT_NOTES TABLE
        if (Schema::hasTable('plant_notes') && !Schema::hasColumn('plant_notes', 'plant_note_id')) {
            Schema::table('plant_notes', function (Blueprint $table) {
                $table->string('plant_note_id', 36)->nullable()->unique('plant_notes_plant_note_id_unq')->after('id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
            });
        }

        // PLANT_PHOTOS TABLE
        if (Schema::hasTable('plant_photos') && !Schema::hasColumn('plant_photos', 'plant_photo_id')) {
            Schema::table('plant_photos', function (Blueprint $table) {
                $table->string('plant_photo_id', 36)->nullable()->unique('plant_photos_plant_photo_id_unq')->after('id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
            });
        }

        // PLANTING_LOCATION_NOTES TABLE
        if (Schema::hasTable('planting_location_notes') && !Schema::hasColumn('planting_location_notes', 'planting_location_note_id')) {
            Schema::table('planting_location_notes', function (Blueprint $table) {
                $table->string('planting_location_note_id', 36)->nullable()->unique('planting_location_no_planting_location_no_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // PLANTING_LOCATION_PHOTOS TABLE
        if (Schema::hasTable('planting_location_photos') && !Schema::hasColumn('planting_location_photos', 'planting_location_photo_id')) {
            Schema::table('planting_location_photos', function (Blueprint $table) {
                $table->string('planting_location_photo_id', 36)->nullable()->unique('planting_location_ph_planting_location_ph_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // INVENTORY_NOTES TABLE
        if (Schema::hasTable('inventory_notes') && !Schema::hasColumn('inventory_notes', 'inventory_note_id')) {
            Schema::table('inventory_notes', function (Blueprint $table) {
                $table->string('inventory_note_id', 36)->nullable()->unique('inventory_notes_inventory_note_id_unq')->after('id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // INVENTORY_PHOTOS TABLE
        if (Schema::hasTable('inventory_photos') && !Schema::hasColumn('inventory_photos', 'inventory_photo_id')) {
            Schema::table('inventory_photos', function (Blueprint $table) {
                $table->string('inventory_photo_id', 36)->nullable()->unique('inventory_photos_inventory_photo_id_unq')->after('id');
                $table->string('new_inventory_type_id', 36)->nullable()->after('inventory_type_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // USER_PLANTING_LOCATION_LAND_MANAGER TABLE
        if (Schema::hasTable('user_planting_location_land_manager') && !Schema::hasColumn('user_planting_location_land_manager', 'user_planting_location_land_manager_id')) {
            Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                $table->string('user_planting_location_land_manager_id', 36)->nullable()->unique('user_planting_locati_user_planting_locati_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // USER_PLANTING_LOCATION_LAND_WORKER TABLE
        if (Schema::hasTable('user_planting_location_land_worker') && !Schema::hasColumn('user_planting_location_land_worker', 'user_planting_location_land_worker_id')) {
            Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                $table->string('user_planting_location_land_worker_id', 36)->nullable()->unique('user_planting_locati_user_planting_locati_unq')->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_transactions')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->dropColumn(['inventory_transaction_id', 'new_bin_id', 'new_inventory_lot_id', 'new_inventory_type_id', 'new_user_id', 'new_warehouse_id']);
            });
        }

        if (Schema::hasTable('inventory_type_warehouses')) {
            Schema::table('inventory_type_warehouses', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_warehous_id', 'new_bin_id', 'new_inventory_type_id', 'new_warehouse_id']);
            });
        }

        if (Schema::hasTable('inventory_type_certification_reports')) {
            Schema::table('inventory_type_certification_reports', function (Blueprint $table) {
                $table->dropColumn(['inventory_type_certification_report_id', 'new_certification_report_id', 'new_inventory_type_id']);
            });
        }

        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn(['sale_item_id', 'new_inventory_lot_id', 'new_inventory_type_id', 'new_sale_id']);
            });
        }

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn(['expense_id', 'new_edited_by', 'new_nutrient_id', 'new_planting_id', 'new_planting_location_id', 'new_responsible_person_id', 'new_treatment_id']);
            });
        }

        if (Schema::hasTable('nutrients')) {
            Schema::table('nutrients', function (Blueprint $table) {
                $table->dropColumn(['nutrient_id', 'new_edited_by', 'new_planting_id', 'new_planting_location_id', 'new_responsible_person_id']);
            });
        }

        if (Schema::hasTable('treatments')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->dropColumn(['treatment_id', 'new_edited_by', 'new_planting_id', 'new_planting_location_id', 'new_responsible_person_id']);
            });
        }

        if (Schema::hasTable('attachments')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropColumn(['attachment_id', 'new_created_by', 'new_edited_by', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('seed_histories')) {
            Schema::table('seed_histories', function (Blueprint $table) {
                $table->dropColumn(['seed_history_id', 'new_inventory_type_seed_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('planting_losses')) {
            Schema::table('planting_losses', function (Blueprint $table) {
                $table->dropColumn(['planting_loss_id', 'new_planting_id']);
            });
        }

        if (Schema::hasTable('plant_notes')) {
            Schema::table('plant_notes', function (Blueprint $table) {
                $table->dropColumn(['plant_note_id', 'new_plant_id']);
            });
        }

        if (Schema::hasTable('plant_photos')) {
            Schema::table('plant_photos', function (Blueprint $table) {
                $table->dropColumn(['plant_photo_id', 'new_plant_id']);
            });
        }

        if (Schema::hasTable('planting_location_notes')) {
            Schema::table('planting_location_notes', function (Blueprint $table) {
                $table->dropColumn(['planting_location_note_id', 'new_planting_location_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('planting_location_photos')) {
            Schema::table('planting_location_photos', function (Blueprint $table) {
                $table->dropColumn(['planting_location_photo_id', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('inventory_notes')) {
            Schema::table('inventory_notes', function (Blueprint $table) {
                $table->dropColumn(['inventory_note_id', 'new_inventory_type_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('inventory_photos')) {
            Schema::table('inventory_photos', function (Blueprint $table) {
                $table->dropColumn(['inventory_photo_id', 'new_inventory_type_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('user_planting_location_land_manager')) {
            Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                $table->dropColumn(['user_planting_location_land_manager_id', 'new_planting_location_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('user_planting_location_land_worker')) {
            Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                $table->dropColumn(['user_planting_location_land_worker_id', 'new_planting_location_id', 'new_user_id']);
            });
        }

    }
};