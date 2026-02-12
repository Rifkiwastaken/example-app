<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 2 - Level 2: Data Migration ===\n";
        echo "Tables: plantings, inventory_types, certifications\n\n";
        
        // plantings: Generate custom IDs
        echo "Migrating plantings...\n";
        $rows = DB::table('plantings')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'PLN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('plantings')->where('id', $row->id)->update(['planting_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // plantings: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE plantings child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // plantings: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE plantings child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";

        // inventory_types: Generate custom IDs
        echo "Migrating inventory_types...\n";
        $rows = DB::table('inventory_types')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'INV-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('inventory_types')->where('id', $row->id)->update(['inventory_type_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // inventory_types: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE inventory_types child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";

        // inventory_types: Update FK responsible_person_id -> new_responsible_person_id
        DB::statement("
            UPDATE inventory_types child
            INNER JOIN users parent ON child.responsible_person_id = parent.id
            SET child.new_responsible_person_id = parent.user_id
            WHERE child.responsible_person_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_responsible_person_id\n";

        // certifications: Generate custom IDs
        echo "Migrating certifications...\n";
        $rows = DB::table('certifications')->select('id')->get();
        foreach ($rows as $row) {
            $customId = 'CRT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            DB::table('certifications')->where('id', $row->id)->update(['certification_id' => $customId]);
        }
        echo "  ✓ Generated " . count($rows) . " custom IDs\n";

        // certifications: Update FK harvest_id -> new_harvest_id
        DB::statement("
            UPDATE certifications child
            INNER JOIN harvests parent ON child.harvest_id = parent.id
            SET child.new_harvest_id = parent.harvest_id
            WHERE child.harvest_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_harvest_id\n";

        // certifications: Update FK planting_location_id -> new_planting_location_id
        DB::statement("
            UPDATE certifications child
            INNER JOIN planting_locations parent ON child.planting_location_id = parent.id
            SET child.new_planting_location_id = parent.planting_location_id
            WHERE child.planting_location_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_planting_location_id\n";

        // certifications: Update FK plant_id -> new_plant_id
        DB::statement("
            UPDATE certifications child
            INNER JOIN plants parent ON child.plant_id = parent.id
            SET child.new_plant_id = parent.plant_id
            WHERE child.plant_id IS NOT NULL
        ");
        echo "  ✓ Updated FK: new_plant_id\n";


        echo "\n✓ Level 2 migration completed!\n\n";
    }

    public function down(): void
    {
        DB::table('plantings')->update(['planting_id' => null]);
        DB::table('plantings')->update(['new_planting_location_id' => null]);
        DB::table('plantings')->update(['new_plant_id' => null]);
        DB::table('inventory_types')->update(['inventory_type_id' => null]);
        DB::table('inventory_types')->update(['new_plant_id' => null]);
        DB::table('inventory_types')->update(['new_responsible_person_id' => null]);
        DB::table('certifications')->update(['certification_id' => null]);
        DB::table('certifications')->update(['new_harvest_id' => null]);
        DB::table('certifications')->update(['new_planting_location_id' => null]);
        DB::table('certifications')->update(['new_plant_id' => null]);
    }
};