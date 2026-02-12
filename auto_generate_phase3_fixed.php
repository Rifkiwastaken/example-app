<?php
/**
 * Auto-generate Phase 3 migration files (Finalization) - FIXED VERSION
 * 
 * Phase 3 akan:
 * 1. Drop ALL FK constraints first (reverse order - child tables first)
 * 2. Drop old id columns
 * 3. Drop old FK columns and rename new_* columns
 * 4. Set custom IDs as Primary Keys
 * 5. Recreate FK constraints with new custom IDs
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
echo "GENERATING PHASE 3 MIGRATIONS (FIXED)\n";
echo "==========================================\n\n";

// Group by level (REVERSE order for dropping FKs)
$levelGroups = [];
foreach ($plan as $tableName => $tableData) {
    $level = $tableData['level'];
    if (!isset($levelGroups[$level])) {
        $levelGroups[$level] = [];
    }
    $levelGroups[$level][] = $tableName;
}

// Generate Phase 3a: Drop ALL FK constraints (reverse order)
echo "Generating Phase 3a: Drop FK constraints...\n";
$filename = 'database/migrations/phase_3_correct/2026_02_10_200_phase3a_drop_all_fk_constraints.php';
$content = generatePhase3aDropFKs($levelGroups, $plan);
file_put_contents($filename, $content);
echo "  ✓ Created: " . basename($filename) . "\n\n";

// Generate Phase 3b: Restructure tables (normal order)
echo "Generating Phase 3b: Restructure tables...\n";
foreach ($levelGroups as $level => $tables) {
    $filename = sprintf(
        'database/migrations/phase_3_correct/2026_02_10_%03d_phase3b_restructure_level_%d.php',
        201 + $level,
        $level
    );
    
    $content = generatePhase3bRestructure($tables, $plan, $level);
    file_put_contents($filename, $content);
    echo "  ✓ Created: " . basename($filename) . " (" . count($tables) . " tables)\n";
}

echo "\n✓ Phase 3 migrations generated!\n";

function generatePhase3aDropFKs($levelGroups, $plan) {
    $dropCode = '';
    
    // Process in REVERSE order (level 4 -> 0)
    krsort($levelGroups);
    
    foreach ($levelGroups as $level => $tables) {
        foreach ($tables as $tableName) {
            $tableData = $plan[$tableName];
            
            if (!empty($tableData['fk_columns'])) {
                $dropCode .= "        // {$tableName}: Drop FK constraints\n";
                $dropCode .= "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
                
                foreach ($tableData['fk_columns'] as $fk) {
                    $fkName = "{$tableName}_{$fk['old']}_foreign";
                    $dropCode .= "            \$table->dropForeign('{$fkName}');\n";
                }
                
                $dropCode .= "        });\n\n";
            }
        }
    }
    
    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\\n=== PHASE 3a: Drop ALL Foreign Key Constraints ===\\n";
        echo "This is necessary before we can drop old ID columns.\\n\\n";
        
        // Disable FK checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
{$dropCode}
        // Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        echo "\\n✓ All FK constraints dropped!\\n\\n";
    }

    public function down(): void
    {
        echo "\\n⚠️  Rollback: FK constraints will be recreated in Phase 3b rollback\\n\\n";
    }
};
PHP;
}

function generatePhase3bRestructure($tables, $plan, $level) {
    $upCode = '';
    $downCode = '';
    
    foreach ($tables as $tableName) {
        $tableData = $plan[$tableName];
        $customIdColumn = $tableData['custom_id'];
        
        $upCode .= "        // {$tableName}: Restructure\n";
        $upCode .= "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        
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
        echo "\\n=== PHASE 3b - Level {$level}: Restructure Tables ===\\n";
        echo "Tables: {$tableList}\\n\\n";
        
{$upCode}
        echo "\\n✓ Level {$level} restructure completed!\\n\\n";
    }

    public function down(): void
    {
        echo "\\n⚠️  WARNING: Phase 3b rollback is complex!\\n";
        echo "It's recommended to restore from backup instead.\\n\\n";
    }
};
PHP;
}

echo "\nDone!\n";
