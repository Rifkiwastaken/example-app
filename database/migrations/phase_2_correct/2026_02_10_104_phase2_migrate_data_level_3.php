<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 2 - Level 3: Data Migration ===\n";
        echo "Tables: harvests, inventory_lots, certification_reports, inventory_type_seeds, tasks, sales\n\n";
        
        // harvests: Generate custom IDs
        echo "Migrating harvests...\n";
        $rows = DB::table('harvests')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'HRV-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('harvests')->where('id', $row->id)->update(['harvest_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // harvests: Update FK edited_by -> new_edited_by
        DB::statement("
            UPDATE harvests child
            INNER JOIN users parent ON child.edited_by = parent.id
            SET child.new_edited_by = parent.user_id
            WHERE child.edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_edited_by\n";

        // harvests: Update FK planting_id -> new_planting_id
        DB::statement("
            UPDATE harvests child
            INNER JOIN plantings parent ON child.planting_id = parent.id
            SET child.new_planting_id = parent.planting_id
            WHERE child.planting_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_id\n";

        // harvests: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE harvests child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // harvests: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE harvests child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";

        // harvests: Update FK recorded_by -> new_recorded_by
        DB::statement("
            UPDATE harvests child
            INNER JOIN users parent ON child.recorded_by = parent.id
            SET child.new_recorded_by = parent.user_id
            WHERE child.recorded_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_recorded_by\n";

        // inventory_lots: Generate custom IDs
        echo "Migrating inventory_lots...\n";
        $rows = DB::table('inventory_lots')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'LOT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_lots')->where('id', $row->id)->update(['inventory_lot_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_lots: Update FK bin_id -> new_bin_id
        DB::statement("
            UPDATE inventory_lots child
            INNER JOIN bins parent ON child.bin_id = parent.id
            SET child.new_bin_id = parent.bin_id
            WHERE child.bin_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_bin_id\n";

        // inventory_lots: Update FK certification_id -> new_certification_id
        DB::statement("
            UPDATE inventory_lots child
            INNER JOIN certifications parent ON child.certification_id = parent.id
            SET child.new_certification_id = parent.certification_id
            WHERE child.certification_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_certification_id\n";

        // inventory_lots: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_lots child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // inventory_lots: Update FK warehouse_id -> new_warehouse_id
        DB::statement("
            UPDATE inventory_lots child
            INNER JOIN warehouses parent ON child.warehouse_id = parent.id
            SET child.new_warehouse_id = parent.warehouse_id
            WHERE child.warehouse_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_warehouse_id\n";

        // certification_reports: Generate custom IDs
        echo "Migrating certification_reports...\n";
        $rows = DB::table('certification_reports')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'CRP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('certification_reports')->where('id', $row->id)->update(['certification_report_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // certification_reports: Update FK certification_id -> new_certification_id
        DB::statement("
            UPDATE certification_reports child
            INNER JOIN certifications parent ON child.certification_id = parent.id
            SET child.new_certification_id = parent.certification_id
            WHERE child.certification_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_certification_id\n";

        // inventory_type_seeds: Generate custom IDs
        echo "Migrating inventory_type_seeds...\n";
        $rows = DB::table('inventory_type_seeds')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'ITS-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_type_seeds')->where('id', $row->id)->update(['inventory_type_seed_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_type_seeds: Update FK edited_by -> new_edited_by
        DB::statement("
            UPDATE inventory_type_seeds child
            INNER JOIN users parent ON child.edited_by = parent.id
            SET child.new_edited_by = parent.user_id
            WHERE child.edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_edited_by\n";

        // inventory_type_seeds: Update FK certification_report_id -> new_certification_report_id
        DB::statement("
            UPDATE inventory_type_seeds child
            INNER JOIN certification_reports parent ON child.certification_report_id = parent.id
            SET child.new_certification_report_id = parent.certification_report_id
            WHERE child.certification_report_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_certification_report_id\n";

        // inventory_type_seeds: Update FK inventory_type_id -> new_inventory_type_id
        DB::statement("
            UPDATE inventory_type_seeds child
            INNER JOIN inventory_types parent ON child.inventory_type_id = parent.id
            SET child.new_inventory_type_id = parent.inventory_type_id
            WHERE child.inventory_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_inventory_type_id\n";

        // inventory_type_seeds: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE inventory_type_seeds child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // inventory_type_seeds: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE inventory_type_seeds child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";

        // inventory_type_seeds: Update FK filled_by_user_id -> new_filled_by_user_id
        DB::statement("
            UPDATE inventory_type_seeds child
            INNER JOIN users parent ON child.filled_by_user_id = parent.id
            SET child.new_filled_by_user_id = parent.user_id
            WHERE child.filled_by_user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_filled_by_user_id\n";

        // tasks: Generate custom IDs
        echo "Migrating tasks...\n";
        $rows = DB::table('tasks')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'TSK-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('tasks')->where('id', $row->id)->update(['task_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // tasks: Update FK assigned_to -> new_assigned_to
        DB::statement("
            UPDATE tasks child
            INNER JOIN users parent ON child.assigned_to = parent.id
            SET child.new_assigned_to = parent.user_id
            WHERE child.assigned_to IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_assigned_to\n";

        // tasks: Update FK created_by -> new_created_by
        DB::statement("
            UPDATE tasks child
            INNER JOIN users parent ON child.created_by = parent.id
            SET child.new_created_by = parent.user_id
            WHERE child.created_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_created_by\n";

        // tasks: Update FK last_edited_by -> new_last_edited_by
        DB::statement("
            UPDATE tasks child
            INNER JOIN users parent ON child.last_edited_by = parent.id
            SET child.new_last_edited_by = parent.user_id
            WHERE child.last_edited_by IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_last_edited_by\n";

        // tasks: Update FK planting_id -> new_planting_id
        DB::statement("
            UPDATE tasks child
            INNER JOIN plantings parent ON child.planting_id = parent.id
            SET child.new_planting_id = parent.planting_id
            WHERE child.planting_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_id\n";

        // tasks: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE tasks child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // tasks: Update FK series_id -> new_series_id
        DB::statement("
            UPDATE tasks child
            INNER JOIN task_series parent ON child.series_id = parent.id
            SET child.new_series_id = parent.task_series_id
            WHERE child.series_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_series_id\n";

        // tasks: Update FK template_id -> new_template_id
        DB::statement("
            UPDATE tasks child
            INNER JOIN task_templates parent ON child.template_id = parent.id
            SET child.new_template_id = parent.task_template_id
            WHERE child.template_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_template_id\n";

        // sales: Generate custom IDs
        echo "Migrating sales...\n";
        $rows = DB::table('sales')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'SAL-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('sales')->where('id', $row->id)->update(['sale_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // sales: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE sales child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // sales: Update FK user_id -> new_user_id
        DB::statement("
            UPDATE sales child
            INNER JOIN users parent ON child.user_id = parent.id
            SET child.new_user_id = parent.user_id
            WHERE child.user_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_user_id\n";


        echo "\n✓ Level 3 migration completed!\n\n";
    }

    public function down(): void
    {
        DB::table('harvests')->update(['harvest_id' => null]);
        DB::table('harvests')->update(['new_edited_by' => null]);
        DB::table('harvests')->update(['new_planting_id' => null]);
        DB::table('harvests')->update(['new_planting_location_id' => null]);
        DB::table('harvests')->update(['new_plant_id' => null]);
        DB::table('harvests')->update(['new_recorded_by' => null]);
        DB::table('inventory_lots')->update(['inventory_lot_id' => null]);
        DB::table('inventory_lots')->update(['new_bin_id' => null]);
        DB::table('inventory_lots')->update(['new_certification_id' => null]);
        DB::table('inventory_lots')->update(['new_inventory_type_id' => null]);
        DB::table('inventory_lots')->update(['new_warehouse_id' => null]);
        DB::table('certification_reports')->update(['certification_report_id' => null]);
        DB::table('certification_reports')->update(['new_certification_id' => null]);
        DB::table('inventory_type_seeds')->update(['inventory_type_seed_id' => null]);
        DB::table('inventory_type_seeds')->update(['new_edited_by' => null]);
        DB::table('inventory_type_seeds')->update(['new_certification_report_id' => null]);
        DB::table('inventory_type_seeds')->update(['new_inventory_type_id' => null]);
        DB::table('inventory_type_seeds')->update(['new_planting_location_id' => null]);
        DB::table('inventory_type_seeds')->update(['new_plant_id' => null]);
        DB::table('inventory_type_seeds')->update(['new_filled_by_user_id' => null]);
        DB::table('tasks')->update(['task_id' => null]);
        DB::table('tasks')->update(['new_assigned_to' => null]);
        DB::table('tasks')->update(['new_created_by' => null]);
        DB::table('tasks')->update(['new_last_edited_by' => null]);
        DB::table('tasks')->update(['new_planting_id' => null]);
        DB::table('tasks')->update(['new_planting_location_id' => null]);
        DB::table('tasks')->update(['new_series_id' => null]);
        DB::table('tasks')->update(['new_template_id' => null]);
        DB::table('sales')->update(['sale_id' => null]);
        DB::table('sales')->update(['new_planting_location_id' => null]);
        DB::table('sales')->update(['new_user_id' => null]);
    }
};