<?php
/**
 * Verify Phase 2 Data Migration
 * Check if all custom IDs are generated and FK references are updated correctly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "VERIFYING PHASE 2 DATA MIGRATION\n";
echo "==========================================\n\n";

$plan = json_decode(file_get_contents('migration_plan.json'), true);

$totalTables = 0;
$totalRows = 0;
$totalCustomIds = 0;
$totalFkUpdates = 0;
$errors = [];

foreach ($plan as $tableName => $tableData) {
    $totalTables++;
    $customIdColumn = $tableData['custom_id'];
    
    // Check if custom IDs are generated
    $rowCount = DB::table($tableName)->count();
    $customIdCount = DB::table($tableName)->whereNotNull($customIdColumn)->count();
    
    $totalRows += $rowCount;
    $totalCustomIds += $customIdCount;
    
    echo "Table: {$tableName}\n";
    echo "  Total rows: {$rowCount}\n";
    echo "  Custom IDs generated: {$customIdCount}\n";
    
    if ($rowCount != $customIdCount) {
        $errors[] = "{$tableName}: Missing custom IDs! ({$customIdCount}/{$rowCount})";
        echo "  ❌ ERROR: Not all rows have custom IDs!\n";
    } else {
        echo "  ✓ All rows have custom IDs\n";
    }
    
    // Check FK updates
    foreach ($tableData['fk_columns'] as $fk) {
        $oldFk = $fk['old'];
        $newFk = $fk['new'];
        
        // Count rows with old FK
        $oldFkCount = DB::table($tableName)->whereNotNull($oldFk)->count();
        // Count rows with new FK
        $newFkCount = DB::table($tableName)->whereNotNull($newFk)->count();
        
        $totalFkUpdates += $newFkCount;
        
        echo "  FK: {$oldFk} -> {$newFk}\n";
        echo "    Old FK rows: {$oldFkCount}\n";
        echo "    New FK rows: {$newFkCount}\n";
        
        if ($oldFkCount != $newFkCount) {
            $errors[] = "{$tableName}.{$newFk}: FK mismatch! ({$newFkCount}/{$oldFkCount})";
            echo "    ❌ ERROR: FK update incomplete!\n";
        } else {
            echo "    ✓ FK updated correctly\n";
        }
    }
    
    echo "\n";
}

echo "==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n";
echo "Total tables: {$totalTables}\n";
echo "Total rows: {$totalRows}\n";
echo "Total custom IDs generated: {$totalCustomIds}\n";
echo "Total FK updates: {$totalFkUpdates}\n";
echo "\n";

if (empty($errors)) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "Phase 2 data migration is successful.\n";
    echo "You can proceed to Phase 3.\n";
} else {
    echo "❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
    echo "\n";
    echo "⚠️  DO NOT proceed to Phase 3 until these errors are fixed!\n";
}

echo "\n";
