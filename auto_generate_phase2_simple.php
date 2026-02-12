<?php
/**
 * Auto-generate Phase 2 migration files (Data Migration) - SIMPLIFIED VERSION
 * 
 * Strategi sederhana:
 * 1. Generate custom ID untuk setiap row
 * 2. Update FK menggunakan JOIN query yang lebih efisien
 */

// Load data
$analysis = json_decode(file_get_contents('database_structure_analysis.json'), true);
$plan = json_decode(file_get_contents('migration_plan.json'), true);

// Create directory
$dir = 'database/migrations/phase_2_correct';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

echo "==========================================\n";
echo "GENERATING PHASE 2 MIGRATIONS (SIMPLIFIED)\n";
echo "==========================================\n\n";

// Group by level
$levelGroups = [];
foreach ($plan as $tableName => $tableData) {
    $level = $tableData['level'];
    if (!isset($levelGroups[$level])) {
        $levelGroups[$level] = [];
    }
    $levelGroups[$level][] = $tableName;
}

// Generate migrations
echo "Generating Phase 2 migrations...\n";
foreach ($levelGroups as $level => $tables) {
    $filename = sprintf(
        'database/migrations/phase_2_correct/2026_02_10_%03d_phase2_migrate_data_level_%d.php',
        100 + $level + 1,
        $level
    );
    
    $content = generatePhase2Migration($tables, $plan, $level);
    file_put_contents($filename, $content);
    echo "  ✓ Created: " . basename($filename) . " (" . count($tables) . " tables)\n";
}

echo "\n✓ Phase 2 migrations generated!\n";

function generatePhase2Migration($tables, $plan, $level) {
    $upCode = '';
    $downCode = '';
    
    foreach ($tables as $tableName) {
        $tableData = $plan[$tableName];
        $customIdColumn = $tableData['custom_id'];
        $prefix = $tableData['prefix'];
        
        // Generate custom IDs
        $upCode .= "        // {$tableName}: Generate custom IDs\n";
        $upCode .= "        echo \"Migrating {$tableName}...\\n\";\n";
        $upCode .= "        \$rows = DB::table('{$tableName}')->select('id')->get();\n";
        $upCode .= "        foreach (\$rows as \$row) {\n";
        $upCode .= "            \$customId = '{$prefix}-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));\n";
        $upCode .= "            DB::table('{$tableName}')->where('id', \$row->id)->update(['{$customIdColumn}' => \$customId]);\n";
        $upCode .= "        }\n";
        $upCode .= "        echo \"  ✓ Generated \" . count(\$rows) . \" custom IDs\\n\";\n\n";
        
        // Update FKs
        foreach ($tableData['fk_columns'] as $fk) {
            $parentTable = $fk['ref_table'];
            $parentCustomId = $fk['ref_column'];
            
            $upCode .= "        // {$tableName}: Update FK {$fk['old']} -> {$fk['new']}\n";
            $upCode .= "        DB::statement(\"\n";
            $upCode .= "            UPDATE {$tableName} child\n";
            $upCode .= "            INNER JOIN {$parentTable} parent ON child.{$fk['old']} = parent.id\n";
            $upCode .= "            SET child.{$fk['new']} = parent.{$parentCustomId}\n";
            $upCode .= "            WHERE child.{$fk['old']} IS NOT NULL\n";
            $upCode .= "        \");\n";
            $upCode .= "        echo \"  ✓ Updated FK: {$fk['new']}\\n\";\n\n";
        }
        
        // DOWN: Clear
        $downCode .= "        DB::table('{$tableName}')->update(['{$customIdColumn}' => null]);\n";
        foreach ($tableData['fk_columns'] as $fk) {
            $downCode .= "        DB::table('{$tableName}')->update(['{$fk['new']}' => null]);\n";
        }
    }
    
    $tableList = implode(', ', $tables);
    
    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\\n=== PHASE 2 - Level {$level}: Data Migration ===\\n";
        echo "Tables: {$tableList}\\n\\n";
        
{$upCode}
        echo "\\n✓ Level {$level} migration completed!\\n\\n";
    }

    public function down(): void
    {
{$downCode}    }
};
PHP;
}

echo "\nDone!\n";
