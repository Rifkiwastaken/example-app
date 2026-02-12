<?php
/**
 * Auto-generate Phase 3 migration files (Finalization)
 * 
 * Phase 3 akan:
 * 1. Drop old FK constraints
 * 2. Drop old id column
 * 3. Drop old FK columns  
 * 4. Rename new_* columns to final names
 * 5. Set custom ID as Primary Key
 * 6. Recreate FK constraints
 */

// Load data
$analysis = json_decode(file_get_contents('database_structure_analysis.json'), true);
$plan = json_decode(file_get_contents('migration_plan.json'), true);

// Create directory
$dir = 'database/migrations/phase_3_correct';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

echo "==========================================\n";
echo "GENERATING PHASE 3 MIGRATIONS (FINALIZE)\n";
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
echo "Generating Phase 3 migrations...\n";
foreach ($levelGroups as $level => $tables) {
    $filename = sprintf(
        'database/migrations/phase_3_correct/2026_02_10_%03d_phase3_finalize_level_%d.php',
        200 + $level + 1,
        $level
    );
    
    $content = generatePhase3Migration($tables, $plan, $analysis, $level);
    file_put_contents($filename, $content);
    echo "  ✓ Created: " . basename($filename) . " (" . count($tables) . " tables)\n";
}

echo "\n✓ Phase 3 migrations generated!\n";

function generatePhase3Migration($tables, $plan, $analysis, $level) {
    $upCode = '';
    $downCode = '';
    
    foreach ($tables as $tableName) {
        $tableData = $plan[$tableName];
        $customIdColumn = $tableData['custom_id'];
        
        $upCode .= "        // {$tableName}: Finalize structure\n";
        $upCode .= "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        
        // Drop old FK constraints first
        $fksToDrop = [];
        if (isset($analysis[$tableName]['foreign_keys'])) {
            foreach ($analysis[$tableName]['foreign_keys'] as $fk) {
                $fksToDrop[] = $fk['constraint_name'];
            }
        }
        
        if (!empty($fksToDrop)) {
            foreach ($fksToDrop as $fkName) {
                $upCode .= "            // \$table->dropForeign('{$fkName}'); // Will be recreated\n";
            }
        }
        
        // Drop old id column
        $upCode .= "            \$table->dropColumn('id');\n";
        
        // Drop old FK columns and rename new_* columns
        foreach ($tableData['fk_columns'] as $fk) {
            $upCode .= "            \$table->dropColumn('{$fk['old']}');\n";
            $upCode .= "            \$table->renameColumn('{$fk['new']}', '{$fk['old']}');\n";
        }
        
        $upCode .= "        });\n\n";
        
        // Set custom ID as primary key
        $upCode .= "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        $upCode .= "            \$table->primary('{$customIdColumn}');\n";
        $upCode .= "        });\n\n";
        
        // Recreate FK constraints
        if (!empty($tableData['fk_columns'])) {
            $upCode .= "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            foreach ($tableData['fk_columns'] as $fk) {
                $upCode .= "            \$table->foreign('{$fk['old']}')->references('{$fk['ref_column']}')->on('{$fk['ref_table']}')->onDelete('cascade');\n";
            }
            $upCode .= "        });\n\n";
        }
        
        // DOWN: Reverse (simplified - just note that it's complex)
        $downCode .= "        // Reversing {$tableName} is complex - restore from backup if needed\n";
        $downCode .= "        // Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        $downCode .= "        //     // Add back old id column, etc.\n";
        $downCode .= "        // });\n\n";
    }
    
    $tableList = implode(', ', $tables);
    
    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        echo "\\n=== PHASE 3 - Level {$level}: Finalization ===\\n";
        echo "Tables: {$tableList}\\n\\n";
        echo "⚠️  WARNING: This will drop old ID columns and restructure tables!\\n";
        echo "Make sure Phase 2 completed successfully before proceeding.\\n\\n";
        
{$upCode}
        echo "\\n✓ Level {$level} finalization completed!\\n\\n";
    }

    public function down(): void
    {
        echo "\\n⚠️  WARNING: Phase 3 rollback is complex!\\n";
        echo "It's recommended to restore from backup instead.\\n\\n";
        
{$downCode}    }
};
PHP;
}

echo "\nDone!\n";
