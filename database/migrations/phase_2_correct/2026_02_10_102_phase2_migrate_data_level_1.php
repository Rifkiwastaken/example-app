<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 2 - Level 1: Data Migration ===\n";
        echo "Tables: plants, planting_locations, bins, task_series\n\n";
        
        // plants: Generate custom IDs
        echo "Migrating plants...\n";
        $rows = DB::table('plants')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'PLT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('plants')->where('id', $row->id)->update(['plant_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // plants: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE plants child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // plants: Update FK plant_type_id -> new_plant_type_id
        DB::statement("
            UPDATE plants child
            INNER JOIN plant_types parent ON child.plant_type_id = parent.id
            SET child.new_plant_type_id = parent.plant_type_id
            WHERE child.plant_type_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_type_id\n";

        // planting_locations: Generate custom IDs
        echo "Migrating planting_locations...\n";
        $rows = DB::table('planting_locations')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'LOC-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('planting_locations')->where('id', $row->id)->update(['planting_location_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // bins: Generate custom IDs
        echo "Migrating bins...\n";
        $rows = DB::table('bins')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'BIN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('bins')->where('id', $row->id)->update(['bin_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // bins: Update FK warehouse_id -> new_warehouse_id
        DB::statement("
            UPDATE bins child
            INNER JOIN warehouses parent ON child.warehouse_id = parent.id
            SET child.new_warehouse_id = parent.warehouse_id
            WHERE child.warehouse_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_warehouse_id\n";

        // task_series: Generate custom IDs
        echo "Migrating task_series...\n";
        $rows = DB::table('task_series')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'TSR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('task_series')->where('id', $row->id)->update(['task_series_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // task_series: Update FK template_id -> new_template_id
        DB::statement("
            UPDATE task_series child
            INNER JOIN task_templates parent ON child.template_id = parent.id
            SET child.new_template_id = parent.task_template_id
            WHERE child.template_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_template_id\n";


        echo "\n✓ Level 1 migration completed!\n\n";
    }

    public function down(): void
    {
        DB::table('plants')->update(['plant_id' => null]);
        DB::table('plants')->update(['new_planting_location_id' => null]);
        DB::table('plants')->update(['new_plant_type_id' => null]);
        DB::table('planting_locations')->update(['planting_location_id' => null]);
        DB::table('bins')->update(['bin_id' => null]);
        DB::table('bins')->update(['new_warehouse_id' => null]);
        DB::table('task_series')->update(['task_series_id' => null]);
        DB::table('task_series')->update(['new_template_id' => null]);
    }
};