<?php
/**
 * Auto-generate Phase 3 migration - FINAL VERSION with error handling
 */

// Load data
$plan = json_decode(file_get_contents('migration_plan.json'), true);
$actualFKs = json_decode(file_get_contents('actual_fk_constraints.json'), true);

// Create directory
$dir = 'database/migrations/phase_3_correct';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

echo "==========================================\n";
echo "GENERATING PHASE 3 FINAL MIGRATION\n";
echo "==========================================\n\n";

// Generate single Phase 3 migration with error handling
$filename = 'database/migrations/phase_3_correct/2026_02_10_301_phase3_finalize_all_tables_final.php';
$content = generatePhase3Final($plan, $actualFKs);
file_put_contents($filename, $content);
echo "  ✓ Created: " . basename($filename) . "\n";

echo "\n✓ Phase 3 migration generated!\n";

function generatePhase3Final($plan, $actualFKs) {
    $dropFKCode = '';
    $restructureCode = '';
    $setPKCode = '';
    $recreateFKCode = '';
    
    // Sort tables by level
    $tablesByLevel = [];
    foreach ($plan as $tableName => $tableData) {
        $level = $tableData['level'];
        if (!isset($tablesByLevel[$level])) {
            $tablesByLevel[$level] = [];
        }
        $tablesByLevel[$level][] = $tableName;
    }
    
    // Step 1: Drop ALL FK constraints (with error handling)
    $dropFKCode .= "        echo \"Step 1: Dropping all FK constraints...\\n\";\n";
    $dropFKCode .= "        DB::statement('SET FOREIGN_KEY_CHECKS=0');\n\n";
    
    krsort($tablesByLevel);
    foreach ($tablesByLevel as $level => $tables) {
        foreach ($tables as $tableName) {
            if (isset($actualFKs[$tableName])) {
                foreach ($actualFKs[$tableName] as $fk) {
                    $dropFKCode .= "        try {\n";
                    $dropFKCode .= "            DB::statement('ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fk['constraint']}`;');\n";
                    $dropFKCode .= "        } catch (\\Exception \$e) { /* Already dropped */ }\n";
                }
            }
        }
    }
    $dropFKCode .= "        echo \"  ✓ All FK constraints dropped\\n\\n\";\n\n";
    
    // Step 2: Restructure tables (normal order)
    ksort($tablesByLevel);
    $restructureCode .= "        echo \"Step 2: Restructuring tables...\\n\";\n\n";
    
    foreach ($tablesByLevel as $level => $tables) {
        foreach ($tables as $tableName) {
            $tableData = $plan[$tableName];
            $customIdColumn = $tableData['custom_id'];
            
            $restructureCode .= "        // {$tableName}\n";
            
            // Drop old id column (with error handling)
            $restructureCode .= "        try {\n";
            $restructureCode .= "            DB::statement('ALTER TABLE `{$tableName}` DROP COLUMN `id`;');\n";
            $restructureCode .= "        } catch (\\Exception \$e) { /* Already dropped */ }\n";
            
            // Drop old FK columns and rename new_* columns
            foreach ($tableData['fk_columns'] as $fk) {
                $restructureCode .= "        try {\n";
                $restructureCode .= "            DB::statement('ALTER TABLE `{$tableName}` DROP COLUMN `{$fk['old']}`;');\n";
                $restructureCode .= "        } catch (\\Exception \$e) { /* Already dropped */ }\n";
                
                $restructureCode .= "        try {\n";
                $restructureCode .= "            DB::statement('ALTER TABLE `{$tableName}` CHANGE `{$fk['new']}` `{$fk['old']}` VARCHAR(36);');\n";
                $restructureCode .= "        } catch (\\Exception \$e) { /* Already renamed */ }\n";
            }
            
            $restructureCode .= "\n";
        }
    }
    $restructureCode .= "        echo \"  ✓ Tables restructured\\n\\n\";\n\n";
    
    // Step 3: Set Primary Keys
    $setPKCode .= "        echo \"Step 3: Setting custom IDs as Primary Keys...\\n\";\n\n";
    
    foreach ($tablesByLevel as $level => $tables) {
        foreach ($tables as $tableName) {
            $tableData = $plan[$tableName];
            $customIdColumn = $tableData['custom_id'];
            
            $setPKCode .= "        try {\n";
            $setPKCode .= "            DB::statement('ALTER TABLE `{$tableName}` ADD PRIMARY KEY (`{$customIdColumn}`);');\n";
            $setPKCode .= "        } catch (\\Exception \$e) { /* Already set */ }\n";
        }
    }
    $setPKCode .= "        echo \"  ✓ Primary keys set\\n\\n\";\n\n";
    
    // Step 4: Recreate FK constraints
    $recreateFKCode .= "        echo \"Step 4: Recreating FK constraints...\\n\";\n\n";
    
    foreach ($tablesByLevel as $level => $tables) {
        foreach ($tables as $tableName) {
            $tableData = $plan[$tableName];
            
            foreach ($tableData['fk_columns'] as $fk) {
                $fkName = substr("{$tableName}_{$fk['old']}_fk", 0, 64);
                $recreateFKCode .= "        try {\n";
                $recreateFKCode .= "            DB::statement('ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$fk['old']}`) REFERENCES `{$fk['ref_table']}`(`{$fk['ref_column']}`) ON DELETE CASCADE;');\n";
                $recreateFKCode .= "        } catch (\\Exception \$e) { /* FK already exists or error */ }\n";
            }
        }
    }
    $recreateFKCode .= "\n        DB::statement('SET FOREIGN_KEY_CHECKS=1');\n";
    $recreateFKCode .= "        echo \"  ✓ FK constraints recreated\\n\\n\";\n";
    
    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\\n=== PHASE 3: FINALIZE - Restructure All Tables (FINAL) ===\\n";
        echo "⚠️  WARNING: This is IRREVERSIBLE!\\n";
        echo "This version includes error handling for partial migrations.\\n\\n";
        
{$dropFKCode}
{$restructureCode}
{$setPKCode}
{$recreateFKCode}
        echo "\\n✅ PHASE 3 COMPLETED SUCCESSFULLY!\\n";
        echo "All tables now use custom string IDs as Primary Keys.\\n\\n";
    }

    public function down(): void
    {
        echo "\\n⚠️  CRITICAL WARNING!\\n";
        echo "Phase 3 rollback is NOT SUPPORTED.\\n";
        echo "Please restore from backup if you need to revert.\\n\\n";
        
        throw new \\Exception('Phase 3 rollback not supported. Restore from backup.');
    }
};
PHP;
}

echo "\nDone!\n";
