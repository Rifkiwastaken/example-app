<?php
/**
 * Get actual FK constraint names from database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "GET ACTUAL FK CONSTRAINT NAMES\n";
echo "==========================================\n\n";

$query = "
    SELECT 
        TABLE_NAME,
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        REFERENCED_TABLE_SCHEMA = 'sibit'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY
        TABLE_NAME, CONSTRAINT_NAME
";

$fks = DB::select($query);

$fkMap = [];
foreach ($fks as $fk) {
    $tableName = $fk->TABLE_NAME;
    if (!isset($fkMap[$tableName])) {
        $fkMap[$tableName] = [];
    }
    $fkMap[$tableName][] = [
        'constraint' => $fk->CONSTRAINT_NAME,
        'column' => $fk->COLUMN_NAME,
        'ref_table' => $fk->REFERENCED_TABLE_NAME,
        'ref_column' => $fk->REFERENCED_COLUMN_NAME,
    ];
}

// Save to JSON
file_put_contents('actual_fk_constraints.json', json_encode($fkMap, JSON_PRETTY_PRINT));

echo "Found " . count($fks) . " FK constraints\n";
echo "Saved to: actual_fk_constraints.json\n\n";

// Display summary
foreach ($fkMap as $table => $constraints) {
    echo "{$table}: " . count($constraints) . " FKs\n";
}

echo "\nDone!\n";
