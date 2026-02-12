<?php
/**
 * Auto-generate Phase 2 migration files (Data Migration)
 * 
 * Phase 2 akan:
 * 1. Generate custom ID untuk setiap row di tabel
 * 2. Update semua FK references dengan custom ID yang sesuai
 */

// Load data
$analysis = json_decode(file_get_contents('database_structure_analysis.json'), true);
$plan = json_decode(file_get_contents('migration_plan.json'), true);

// Create directory if not exist
$dir = 'database/migrations/phase_2_correct';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

echo "==========================================\n";
echo "GENERATING PHASE 2 MIGRATIONS (DATA)\n";
echo "==========================================\n\n";

// Group tables by level
$levelGroups = [];
foreach ($plan as $tableName => $tableData) {
    $level = $tableData['level'];
    if (!isset($levelGroups[$level])) {
        $levelGroups[$level] = [];
    }
    $levelGroups[$level][] = $tableName;
}

// Generate Phase 2 migrations (one file per level)
echo "Generating Phase 2 migrations...\n";
foreach ($levelGroups as $level => $tables) {
    $filename = sprintf(
        'database/migrations/phase_2_correct/2026_02_10_%03d_phase2_migrate_data_level_%d.php',
        100 + $level + 1,
        $level
    );
    
    $content = generatePhase2Migration($tables, $plan, $analysis, $level);
    file_put_contents($filename, $content);
    echo "  ✓ Created: " . basename($filename) . " (" . count($tables) . " tables)\n";
}

echo "\n✓ Phase 2 migrations generated!\n";
echo "\nNext: Review the generated files in database/migrations/phase_2_correct/\n";

// Helper function to generate Phase 2 migration
function generatePhase2Migration($tables, $plan, $analysis, $level) {
    $upCode = '';
    $downCode = '';
    
    foreach ($tables as $tableName) {
        $tableData = $plan[$tableName];
        $customIdColumn = $tableData['custom_id'];
        $prefix = $tableData['prefix'];
        
        // UP: Generate custom IDs and update FKs
        $upCode .= "        // " . strtoupper($tableName) . " - Generate Custom IDs\n";
        $upCode .= "        \$this->migrateTable('{$tableName}', '{$customIdColumn}', '{$prefix}', [\n";
        
        // Add FK mappings
        foreach ($tableData['fk_columns'] as $fk) {
            $upCode .= "            '{$fk['old']}' => '{$fk['new']}',  // {$fk['ref_table']}.{$fk['ref_column']}\n";
        }
        
        $upCode .= "        ]);\n\n";
        
        // DOWN: Clear custom IDs
        $downCode .= "        DB::table('{$tableName}')->update(['{$customIdColumn}' => null]);\n";
        foreach ($tableData['fk_columns'] as $fk) {
            $downCode .= "        DB::table('{$tableName}')->update(['{$fk['new']}' => null]);\n";
        }
        $downCode .= "\n";
    }
    
    $tableList = implode(', ', $tables);
    
    return <<<PHP
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
     * FASE 2 - Level {$level}: Migrasi data - Generate custom IDs dan update FK references.
     * Tables: {$tableList}
     */
    public function up(): void
    {
{$upCode}    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
{$downCode}    }

    /**
     * Migrate a single table: generate custom IDs and update FK references
     */
    private function migrateTable(string \$tableName, string \$customIdColumn, string \$prefix, array \$fkMappings): void
    {
        echo "  Migrating {\$tableName}...\\n";
        
        // Step 1: Generate custom IDs for all rows and create mapping
        \$idMapping = [];
        \$rows = DB::table(\$tableName)->select('id')->get();
        \$totalRows = count(\$rows);
        \$processed = 0;
        
        foreach (\$rows as \$row) {
            // Generate custom ID
            \$customId = \$this->generateCustomId(\$prefix);
            
            // Store mapping
            \$idMapping[\$row->id] = \$customId;
            
            // Update the row with custom ID
            DB::table(\$tableName)
                ->where('id', \$row->id)
                ->update([\$customIdColumn => \$customId]);
            
            \$processed++;
            if (\$processed % 100 == 0) {
                echo "    Progress: {\$processed}/{\$totalRows}\\n";
            }
        }
        
        echo "  ✓ Generated custom IDs for {\$tableName}: {\$totalRows} rows\\n";
        
        // Step 2: Update FK references in THIS table (pointing to parent tables)
        foreach (\$fkMappings as \$oldFkColumn => \$newFkColumn) {
            echo "    Updating FK: {\$oldFkColumn} -> {\$newFkColumn}\\n";
            
            // Get all rows with FK values
            \$fkRows = DB::table(\$tableName)
                ->whereNotNull(\$oldFkColumn)
                ->select('id', \$oldFkColumn)
                ->get();
            
            foreach (\$fkRows as \$fkRow) {
                // We need to find the custom ID from the parent table
                // This will be done by looking up the parent table's custom ID column
                // For now, we'll use a subquery approach
                
                DB::statement("
                    UPDATE {\$tableName} AS child
                    SET child.{\$newFkColumn} = (
                        SELECT parent_custom_id
                        FROM (
                            SELECT id, {\$this->getParentCustomIdColumn(\$oldFkColumn)} AS parent_custom_id
                            FROM {\$this->getParentTableName(\$oldFkColumn)}
                        ) AS parent
                        WHERE parent.id = child.{\$oldFkColumn}
                    )
                    WHERE child.id = ?
                ", [\$fkRow->id]);
            }
            
            echo "    ✓ Updated FK: {\$newFkColumn}\\n";
        }
        
        echo "  ✓ Completed migration for {\$tableName}\\n\\n";
    }

    /**
     * Get parent table name from FK column name
     */
    private function getParentTableName(string \$fkColumn): string
    {
        // Remove _id suffix and pluralize
        \$singular = str_replace('_id', '', \$fkColumn);
        
        // Simple pluralization (you might need to adjust this)
        \$pluralMap = [
            'user' => 'users',
            'plant_type' => 'plant_types',
            'plant' => 'plants',
            'planting_location' => 'planting_locations',
            'planting' => 'plantings',
            'harvest' => 'harvests',
            'certification' => 'certifications',
            'certification_report' => 'certification_reports',
            'warehouse' => 'warehouses',
            'bin' => 'bins',
            'inventory_type' => 'inventory_types',
            'inventory_lot' => 'inventory_lots',
            'task' => 'tasks',
            'task_series' => 'task_series',
            'sale' => 'sales',
            'inventory_type_seed' => 'inventory_type_seeds',
        ];
        
        return \$pluralMap[\$singular] ?? \$singular . 's';
    }

    /**
     * Get parent custom ID column name
     */
    private function getParentCustomIdColumn(string \$fkColumn): string
    {
        // The custom ID column is the same as FK column for parent table
        return \$fkColumn;
    }

    /**
     * Generate a unique custom ID
     */
    private function generateCustomId(string \$prefix): string
    {
        \$maxAttempts = 10;
        \$attempt = 0;
        
        do {
            // Generate random alphanumeric string (8 characters)
            \$randomString = \$this->generateRandomString(8);
            \$customId = "{\$prefix}-{\$randomString}";
            
            \$attempt++;
            
            // For simplicity, we'll assume no collision in migration
            // In production, you might want to check for uniqueness
            return \$customId;
            
        } while (\$attempt < \$maxAttempts);
        
        // Fallback: add timestamp
        \$timestamp = substr(time(), -4);
        \$randomString = \$this->generateRandomString(4);
        return "{\$prefix}-{\$randomString}{\$timestamp}";
    }

    /**
     * Generate random alphanumeric string
     */
    private function generateRandomString(int \$length = 8): string
    {
        \$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        \$charactersLength = strlen(\$characters);
        \$randomString = '';

        for (\$i = 0; \$i < \$length; \$i++) {
            \$randomString .= \$characters[random_int(0, \$charactersLength - 1)];
        }

        return \$randomString;
    }
};
PHP;
}

echo "\nDone!\n";
