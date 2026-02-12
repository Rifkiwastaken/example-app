<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 2 - Level 0: Data Migration ===\n";
        echo "Tables: users, plant_types, warehouses, task_templates, landing_page_settings\n\n";
        
        // users: Generate custom IDs
        echo "Migrating users...\n";
        $rows = DB::table('users')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'USR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('users')->where('id', $row->id)->update(['user_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // plant_types: Generate custom IDs
        echo "Migrating plant_types...\n";
        $rows = DB::table('plant_types')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'PTY-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('plant_types')->where('id', $row->id)->update(['plant_type_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // warehouses: Generate custom IDs
        echo "Migrating warehouses...\n";
        $rows = DB::table('warehouses')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'WHS-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('warehouses')->where('id', $row->id)->update(['warehouse_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // warehouses: Update FK responsible_person_id -> new_responsible_person_id
        DB::statement("
            UPDATE warehouses child
            INNER JOIN users parent ON child.responsible_person_id = parent.id
            SET child.new_responsible_person_id = parent.user_id
            WHERE child.responsible_person_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_responsible_person_id\n";

        // task_templates: Generate custom IDs
        echo "Migrating task_templates...\n";
        $rows = DB::table('task_templates')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'TTP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('task_templates')->where('id', $row->id)->update(['task_template_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // landing_page_settings: Generate custom IDs
        echo "Migrating landing_page_settings...\n";
        $rows = DB::table('landing_page_settings')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'LPS-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('landing_page_settings')->where('id', $row->id)->update(['landing_page_setting_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";


        echo "\n✓ Level 0 migration completed!\n\n";
    }

    public function down(): void
    {
        DB::table('users')->update(['user_id' => null]);
        DB::table('plant_types')->update(['plant_type_id' => null]);
        DB::table('warehouses')->update(['warehouse_id' => null]);
        DB::table('warehouses')->update(['new_responsible_person_id' => null]);
        DB::table('task_templates')->update(['task_template_id' => null]);
        DB::table('landing_page_settings')->update(['landing_page_setting_id' => null]);
    }
};