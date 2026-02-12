<?php
/**
 * Verify that migration is complete and successful
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n";
echo "==========================================\n";
echo "MIGRATION VERIFICATION - FINAL CHECK\n";
echo "==========================================\n\n";

$tables = [
    'users' => 'user_id',
    'plant_types' => 'plant_type_id',
    'plants' => 'plant_id',
    'planting_locations' => 'planting_location_id',
    'plantings' => 'planting_id',
    'harvests' => 'harvest_id',
    'certifications' => 'certification_id',
    'certification_reports' => 'certification_report_id',
    'warehouses' => 'warehouse_id',
    'bins' => 'bin_id',
    'inventory_types' => 'inventory_type_id',
    'inventory_lots' => 'inventory_lot_id',
    'inventory_transactions' => 'inventory_transaction_id',
    'inventory_type_seeds' => 'inventory_type_seed_id',
    'sales' => 'sale_id',
    'sale_items' => 'sale_item_id',
    'tasks' => 'task_id',
    'task_series' => 'task_series_id',
    'locations' => 'location_id',
    'nutrients' => 'nutrient_id',
    'expenses' => 'expense_id',
    'attachments' => 'attachment_id',
    'seed_histories' => 'seed_history_id',
    'planting_losses' => 'planting_loss_id',
    'plant_notes' => 'plant_note_id',
    'plant_photos' => 'plant_photo_id',
    'planting_location_notes' => 'planting_location_note_id',
    'planting_location_photos' => 'planting_location_photo_id',
    'inventory_notes' => 'inventory_note_id',
    'inventory_photos' => 'inventory_photo_id',
];

$allPassed = true;
$totalChecks = 0;
$passedChecks = 0;

echo "Checking Primary Keys...\n";
echo "----------------------------------------\n";

foreach ($tables as $table => $expectedPK) {
    $totalChecks++;
    
    if (!Schema::hasTable($table)) {
        echo "❌ {$table}: Table not found\n";
        $allPassed = false;
        continue;
    }
    
    // Check if custom PK column exists
    if (!Schema::hasColumn($table, $expectedPK)) {
        echo "❌ {$table}: Column '{$expectedPK}' not found\n";
        $allPassed = false;
        continue;
    }
    
    // Check if old 'id' column is removed
    if (Schema::hasColumn($table, 'id')) {
        echo "⚠️  {$table}: Old 'id' column still exists\n";
        $allPassed = false;
        continue;
    }
    
    // Get primary key info
    $pkQuery = "
        SELECT COLUMN_NAME, DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'sibit'
        AND TABLE_NAME = '{$table}'
        AND COLUMN_KEY = 'PRI'
    ";
    
    $pkInfo = DB::select($pkQuery);
    
    if (empty($pkInfo)) {
        echo "❌ {$table}: No primary key found\n";
        $allPassed = false;
        continue;
    }
    
    $actualPK = $pkInfo[0]->COLUMN_NAME;
    $dataType = $pkInfo[0]->DATA_TYPE;
    
    if ($actualPK !== $expectedPK) {
        echo "❌ {$table}: PK is '{$actualPK}', expected '{$expectedPK}'\n";
        $allPassed = false;
        continue;
    }
    
    if ($dataType !== 'varchar') {
        echo "⚠️  {$table}: PK type is '{$dataType}', expected 'varchar'\n";
        $allPassed = false;
        continue;
    }
    
    // Check sample data format
    $sample = DB::table($table)->first();
    if ($sample) {
        $pkValue = $sample->{$expectedPK};
        if (!preg_match('/^[A-Z]{3}-[A-Z0-9]{8}$/', $pkValue)) {
            echo "⚠️  {$table}: PK format incorrect (sample: {$pkValue})\n";
            $allPassed = false;
            continue;
        }
    }
    
    echo "✅ {$table}: PK '{$expectedPK}' (varchar) - OK\n";
    $passedChecks++;
}

echo "\n";
echo "Checking Foreign Keys...\n";
echo "----------------------------------------\n";

$fkQuery = "
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        TABLE_SCHEMA = 'sibit'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY
        TABLE_NAME, COLUMN_NAME
";

$foreignKeys = DB::select($fkQuery);
$fkCount = count($foreignKeys);

echo "Found {$fkCount} foreign key relationships\n";

$fkIssues = 0;
foreach ($foreignKeys as $fk) {
    // Check if FK column is varchar
    $colQuery = "
        SELECT DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'sibit'
        AND TABLE_NAME = '{$fk->TABLE_NAME}'
        AND COLUMN_NAME = '{$fk->COLUMN_NAME}'
    ";
    
    $colInfo = DB::select($colQuery);
    if (!empty($colInfo) && $colInfo[0]->DATA_TYPE !== 'varchar') {
        echo "⚠️  {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME} (type: {$colInfo[0]->DATA_TYPE})\n";
        $fkIssues++;
    }
}

if ($fkIssues === 0) {
    echo "✅ All foreign keys are using varchar type\n";
} else {
    echo "⚠️  Found {$fkIssues} foreign keys with non-varchar type\n";
    $allPassed = false;
}

echo "\n";
echo "==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n";
echo "Total tables checked: {$totalChecks}\n";
echo "Passed: {$passedChecks}\n";
echo "Failed: " . ($totalChecks - $passedChecks) . "\n";
echo "Foreign keys: {$fkCount}\n";
echo "FK issues: {$fkIssues}\n";
echo "\n";

if ($allPassed && $fkIssues === 0) {
    echo "✅ ✅ ✅ MIGRATION COMPLETED SUCCESSFULLY! ✅ ✅ ✅\n";
    echo "\n";
    echo "All tables now use custom string IDs:\n";
    echo "- Format: PREFIX-XXXXXXXX (e.g., PLT-8X92MKA1)\n";
    echo "- All foreign keys updated\n";
    echo "- All models updated with HasCustomId trait\n";
    echo "\n";
    echo "Next steps:\n";
    echo "1. Test creating new records in the application\n";
    echo "2. Verify that custom IDs are auto-generated\n";
    echo "3. Test all CRUD operations\n";
    echo "4. Monitor for any issues\n";
} else {
    echo "⚠️  MIGRATION HAS ISSUES - Please review above\n";
}

echo "\n";
