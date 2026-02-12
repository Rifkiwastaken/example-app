<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 2 - Level 4: Data Migration ===\n";
        echo "Tables: inventory_transactions, inventory_type_warehouses, inventory_type_certification_reports, sale_items, expenses, nutrients, treatments, attachments, seed_histories, planting_losses, plant_notes, plant_photos, planting_location_notes, planting_location_photos, inventory_notes, inventory_photos, user_planting_location_land_manager, user_planting_location_land_worker\n\n";
        
        // inventory_transactions: Generate custom IDs
        echo "Migrating inventory_transactions...\n";
        $rows = DB::table('inventory_transactions')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'TRX-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_transactions')->where('id', $row->id)->update(['inventory_transaction_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_transactions: Update FK bin_id -> new_bin_id
        DB::statement("
            UPDATE inventory_transactions child
            INNER JOIN bins parent ON child.bin_id = parent.id
            SET child.new_bin_id = parent.bin_id
            WHERE child.bin_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_bin_id\n";

        // inventory_transactions: Update FK inventory_lot_id -> new_inventory_lot_id
        DB::statement("
            UPDATE inventory_transactions child
            INNER JOIN inventory_lots parent ON child.inventory_lot_id = parent.id
            SET child.new_inventory_lot_id = parent.inventory_lot_id
            WHERE child.inventory_lot_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_lot_id\n";

        // inventory_transactions: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_transactions child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // inventory_transactions: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE inventory_transactions child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";

        // inventory_transactions: Update FK warehouse_id -> new_warehouse_id
        DB::statement("
            UPDATE inventory_transactions child
            INNER JOIN warehouses parent ON child.warehouse_id = parent.id
            SET child.new_warehouse_id = parent.warehouse_id
            WHERE child.warehouse_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_warehouse_id\n";

        // inventory_type_warehouses: Generate custom IDs
        echo "Migrating inventory_type_warehouses...\n";
        $rows = DB::table('inventory_type_warehouses')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'ITW-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_type_warehouses')->where('id', $row->id)->update(['inventory_type_warehous_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_type_warehouses: Update FK bin_id -> new_bin_id
        DB::statement("
            UPDATE inventory_type_warehouses child
            INNER JOIN bins parent ON child.bin_id = parent.id
            SET child.new_bin_id = parent.bin_id
            WHERE child.bin_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_bin_id\n";

        // inventory_type_warehouses: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_type_warehouses child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // inventory_type_warehouses: Update FK warehouse_id -> new_warehouse_id
        DB::statement("
            UPDATE inventory_type_warehouses child
            INNER JOIN warehouses parent ON child.warehouse_id = parent.id
            SET child.new_warehouse_id = parent.warehouse_id
            WHERE child.warehouse_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_warehouse_id\n";

        // inventory_type_certification_reports: Generate custom IDs
        echo "Migrating inventory_type_certification_reports...\n";
        $rows = DB::table('inventory_type_certification_reports')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'ICR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_type_certification_reports')->where('id', $row->id)->update(['inventory_type_certification_report_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_type_certification_reports: Update FK certification_report_id -> new_certification_report_id
        DB::statement("
            UPDATE inventory_type_certification_reports child
            INNER JOIN certification_reports parent ON child.certification_report_id = parent.id
            SET child.new_certification_report_id = parent.certification_report_id
            WHERE child.certification_report_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_certification_report_id\n";

        // inventory_type_certification_reports: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_type_certification_reports child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // sale_items: Generate custom IDs
        echo "Migrating sale_items...\n";
        $rows = DB::table('sale_items')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'SIT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('sale_items')->where('id', $row->id)->update(['sale_item_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // sale_items: Update FK inventory_lot_id -> new_inventory_lot_id
        DB::statement("
            UPDATE sale_items child
            INNER JOIN inventory_lots parent ON child.inventory_lot_id = parent.id
            SET child.new_inventory_lot_id = parent.inventory_lot_id
            WHERE child.inventory_lot_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_lot_id\n";

        // sale_items: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE sale_items child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // sale_items: Update FK sale_id -> new_sale_id
        DB::statement("
            UPDATE sale_items child
            INNER JOIN sales parent ON child.sale_id = parent.id
            SET child.new_sale_id = parent.sale_id
            WHERE child.sale_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_sale_id\n";

        // expenses: Generate custom IDs
        echo "Migrating expenses...\n";
        $rows = DB::table('expenses')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'EXP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('expenses')->where('id', $row->id)->update(['expense_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // expenses: Update FK edited_by -> new_edited_by
        DB::statement("
            UPDATE expenses child
            INNER JOIN users parent ON child.edited_by = parent.id
            SET child.new_edited_by = parent.user_id
            WHERE child.edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_edited_by\n";

        // expenses: Update FK nutrient_id -> new_nutrient_id
        DB::statement("
            UPDATE expenses child
            INNER JOIN nutrients parent ON child.nutrient_id = parent.id
            SET child.new_nutrient_id = parent.nutrient_id
            WHERE child.nutrient_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_nutrient_id\n";

        // expenses: Update FK planting_id -> new_planting_id
        DB::statement("
            UPDATE expenses child
            INNER JOIN plantings parent ON child.planting_id = parent.id
            SET child.new_planting_id = parent.planting_id
            WHERE child.planting_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_id\n";

        // expenses: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE expenses child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // expenses: Update FK responsible_person_id -> new_responsible_person_id
        DB::statement("
            UPDATE expenses child
            INNER JOIN users parent ON child.responsible_person_id = parent.id
            SET child.new_responsible_person_id = parent.user_id
            WHERE child.responsible_person_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_responsible_person_id\n";

        // expenses: Update FK treatment_id -> new_treatment_id
        DB::statement("
            UPDATE expenses child
            INNER JOIN treatments parent ON child.treatment_id = parent.id
            SET child.new_treatment_id = parent.treatment_id
            WHERE child.treatment_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_treatment_id\n";

        // nutrients: Generate custom IDs
        echo "Migrating nutrients...\n";
        $rows = DB::table('nutrients')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'NTR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('nutrients')->where('id', $row->id)->update(['nutrient_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // nutrients: Update FK edited_by -> new_edited_by
        DB::statement("
            UPDATE nutrients child
            INNER JOIN users parent ON child.edited_by = parent.id
            SET child.new_edited_by = parent.user_id
            WHERE child.edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_edited_by\n";

        // nutrients: Update FK planting_id -> new_planting_id
        DB::statement("
            UPDATE nutrients child
            INNER JOIN plantings parent ON child.planting_id = parent.id
            SET child.new_planting_id = parent.planting_id
            WHERE child.planting_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_id\n";

        // nutrients: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE nutrients child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // nutrients: Update FK responsible_person_id -> new_responsible_person_id
        DB::statement("
            UPDATE nutrients child
            INNER JOIN users parent ON child.responsible_person_id = parent.id
            SET child.new_responsible_person_id = parent.user_id
            WHERE child.responsible_person_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_responsible_person_id\n";

        // treatments: Generate custom IDs
        echo "Migrating treatments...\n";
        $rows = DB::table('treatments')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'TRT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('treatments')->where('id', $row->id)->update(['treatment_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // treatments: Update FK edited_by -> new_edited_by
        DB::statement("
            UPDATE treatments child
            INNER JOIN users parent ON child.edited_by = parent.id
            SET child.new_edited_by = parent.user_id
            WHERE child.edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_edited_by\n";

        // treatments: Update FK planting_id -> new_planting_id
        DB::statement("
            UPDATE treatments child
            INNER JOIN plantings parent ON child.planting_id = parent.id
            SET child.new_planting_id = parent.planting_id
            WHERE child.planting_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_id\n";

        // treatments: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE treatments child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // treatments: Update FK responsible_person_id -> new_responsible_person_id
        DB::statement("
            UPDATE treatments child
            INNER JOIN users parent ON child.responsible_person_id = parent.id
            SET child.new_responsible_person_id = parent.user_id
            WHERE child.responsible_person_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_responsible_person_id\n";

        // attachments: Generate custom IDs
        echo "Migrating attachments...\n";
        $rows = DB::table('attachments')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'ATT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('attachments')->where('id', $row->id)->update(['attachment_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // attachments: Update FK created_by -> new_created_by
        DB::statement("
            UPDATE attachments child
            INNER JOIN users parent ON child.created_by = parent.id
            SET child.new_created_by = parent.user_id
            WHERE child.created_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_created_by\n";

        // attachments: Update FK edited_by -> new_edited_by
        DB::statement("
            UPDATE attachments child
            INNER JOIN users parent ON child.edited_by = parent.id
            SET child.new_edited_by = parent.user_id
            WHERE child.edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_edited_by\n";

        // attachments: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE attachments child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // seed_histories: Generate custom IDs
        echo "Migrating seed_histories...\n";
        $rows = DB::table('seed_histories')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'SDH-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('seed_histories')->where('id', $row->id)->update(['seed_history_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // seed_histories: Update FK inventory_type_seed_id -> new_inventory_type_seed_id
        DB::statement("
            UPDATE seed_histories child
            INNER JOIN inventory_type_seeds parent ON child.inventory_type_seed_id = parent.id
            SET child.new_inventory_type_seed_id = parent.inventory_type_seed_id
            WHERE child.inventory_type_seed_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_seed_id\n";

        // seed_histories: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE seed_histories child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";

        // planting_losses: Generate custom IDs
        echo "Migrating planting_losses...\n";
        $rows = DB::table('planting_losses')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'PLS-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('planting_losses')->where('id', $row->id)->update(['planting_loss_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // planting_losses: Update FK planting_id -> new_planting_id
        DB::statement("
            UPDATE planting_losses child
            INNER JOIN plantings parent ON child.planting_id = parent.id
            SET child.new_planting_id = parent.planting_id
            WHERE child.planting_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_id\n";

        // plant_notes: Generate custom IDs
        echo "Migrating plant_notes...\n";
        $rows = DB::table('plant_notes')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'PLN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('plant_notes')->where('id', $row->id)->update(['plant_note_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // plant_notes: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE plant_notes child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";

        // plant_photos: Generate custom IDs
        echo "Migrating plant_photos...\n";
        $rows = DB::table('plant_photos')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'PHP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('plant_photos')->where('id', $row->id)->update(['plant_photo_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // plant_photos: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE plant_photos child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";

        // planting_location_notes: Generate custom IDs
        echo "Migrating planting_location_notes...\n";
        $rows = DB::table('planting_location_notes')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'LCN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('planting_location_notes')->where('id', $row->id)->update(['planting_location_note_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // planting_location_notes: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE planting_location_notes child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // planting_location_notes: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE planting_location_notes child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";

        // planting_location_photos: Generate custom IDs
        echo "Migrating planting_location_photos...\n";
        $rows = DB::table('planting_location_photos')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'LCP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('planting_location_photos')->where('id', $row->id)->update(['planting_location_photo_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // planting_location_photos: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE planting_location_photos child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // inventory_notes: Generate custom IDs
        echo "Migrating inventory_notes...\n";
        $rows = DB::table('inventory_notes')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'INN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_notes')->where('id', $row->id)->update(['inventory_note_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_notes: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_notes child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // inventory_notes: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE inventory_notes child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";

        // inventory_photos: Generate custom IDs
        echo "Migrating inventory_photos...\n";
        $rows = DB::table('inventory_photos')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'INP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_photos')->where('id', $row->id)->update(['inventory_photo_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_photos: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_photos child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // inventory_photos: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE inventory_photos child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";

        // user_planting_location_land_manager: Generate custom IDs
        echo "Migrating user_planting_location_land_manager...\n";
        $rows = DB::table('user_planting_location_land_manager')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'ULM-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('user_planting_location_land_manager')->where('id', $row->id)->update(['user_planting_location_land_manager_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // user_planting_location_land_manager: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE user_planting_location_land_manager child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // user_planting_location_land_manager: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE user_planting_location_land_manager child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";

        // user_planting_location_land_worker: Generate custom IDs
        echo "Migrating user_planting_location_land_worker...\n";
        $rows = DB::table('user_planting_location_land_worker')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'ULW-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('user_planting_location_land_worker')->where('id', $row->id)->update(['user_planting_location_land_worker_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // user_planting_location_land_worker: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE user_planting_location_land_worker child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // user_planting_location_land_worker: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE user_planting_location_land_worker child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";


        echo "\n✓ Level 4 migration completed!\n\n";
    }

    public function down(): void
    {
        DB::table('inventory_transactions')->update(['inventory_transaction_id' => null]);
        DB::table('inventory_transactions')->update(['new_bin_id' => null]);
        DB::table('inventory_transactions')->update(['new_inventory_lot_id' => null]);
        DB::table('inventory_transactions')->update(['new_inventory_type_id' => null]);
        DB::table('inventory_transactions')->update(['new_user_id' => null]);
        DB::table('inventory_transactions')->update(['new_warehouse_id' => null]);
        DB::table('inventory_type_warehouses')->update(['inventory_type_warehous_id' => null]);
        DB::table('inventory_type_warehouses')->update(['new_bin_id' => null]);
        DB::table('inventory_type_warehouses')->update(['new_inventory_type_id' => null]);
        DB::table('inventory_type_warehouses')->update(['new_warehouse_id' => null]);
        DB::table('inventory_type_certification_reports')->update(['inventory_type_certification_report_id' => null]);
        DB::table('inventory_type_certification_reports')->update(['new_certification_report_id' => null]);
        DB::table('inventory_type_certification_reports')->update(['new_inventory_type_id' => null]);
        DB::table('sale_items')->update(['sale_item_id' => null]);
        DB::table('sale_items')->update(['new_inventory_lot_id' => null]);
        DB::table('sale_items')->update(['new_inventory_type_id' => null]);
        DB::table('sale_items')->update(['new_sale_id' => null]);
        DB::table('expenses')->update(['expense_id' => null]);
        DB::table('expenses')->update(['new_edited_by' => null]);
        DB::table('expenses')->update(['new_nutrient_id' => null]);
        DB::table('expenses')->update(['new_planting_id' => null]);
        DB::table('expenses')->update(['new_planting_location_id' => null]);
        DB::table('expenses')->update(['new_responsible_person_id' => null]);
        DB::table('expenses')->update(['new_treatment_id' => null]);
        DB::table('nutrients')->update(['nutrient_id' => null]);
        DB::table('nutrients')->update(['new_edited_by' => null]);
        DB::table('nutrients')->update(['new_planting_id' => null]);
        DB::table('nutrients')->update(['new_planting_location_id' => null]);
        DB::table('nutrients')->update(['new_responsible_person_id' => null]);
        DB::table('treatments')->update(['treatment_id' => null]);
        DB::table('treatments')->update(['new_edited_by' => null]);
        DB::table('treatments')->update(['new_planting_id' => null]);
        DB::table('treatments')->update(['new_planting_location_id' => null]);
        DB::table('treatments')->update(['new_responsible_person_id' => null]);
        DB::table('attachments')->update(['attachment_id' => null]);
        DB::table('attachments')->update(['new_created_by' => null]);
        DB::table('attachments')->update(['new_edited_by' => null]);
        DB::table('attachments')->update(['new_planting_location_id' => null]);
        DB::table('seed_histories')->update(['seed_history_id' => null]);
        DB::table('seed_histories')->update(['new_inventory_type_seed_id' => null]);
        DB::table('seed_histories')->update(['new_user_id' => null]);
        DB::table('planting_losses')->update(['planting_loss_id' => null]);
        DB::table('planting_losses')->update(['new_planting_id' => null]);
        DB::table('plant_notes')->update(['plant_note_id' => null]);
        DB::table('plant_notes')->update(['new_plant_id' => null]);
        DB::table('plant_photos')->update(['plant_photo_id' => null]);
        DB::table('plant_photos')->update(['new_plant_id' => null]);
        DB::table('planting_location_notes')->update(['planting_location_note_id' => null]);
        DB::table('planting_location_notes')->update(['new_planting_location_id' => null]);
        DB::table('planting_location_notes')->update(['new_user_id' => null]);
        DB::table('planting_location_photos')->update(['planting_location_photo_id' => null]);
        DB::table('planting_location_photos')->update(['new_planting_location_id' => null]);
        DB::table('inventory_notes')->update(['inventory_note_id' => null]);
        DB::table('inventory_notes')->update(['new_inventory_type_id' => null]);
        DB::table('inventory_notes')->update(['new_user_id' => null]);
        DB::table('inventory_photos')->update(['inventory_photo_id' => null]);
        DB::table('inventory_photos')->update(['new_inventory_type_id' => null]);
        DB::table('inventory_photos')->update(['new_user_id' => null]);
        DB::table('user_planting_location_land_manager')->update(['user_planting_location_land_manager_id' => null]);
        DB::table('user_planting_location_land_manager')->update(['new_planting_location_id' => null]);
        DB::table('user_planting_location_land_manager')->update(['new_user_id' => null]);
        DB::table('user_planting_location_land_worker')->update(['user_planting_location_land_worker_id' => null]);
        DB::table('user_planting_location_land_worker')->update(['new_planting_location_id' => null]);
        DB::table('user_planting_location_land_worker')->update(['new_user_id' => null]);
    }
};