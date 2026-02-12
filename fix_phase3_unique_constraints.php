<?php
/**
 * Drop all unique constraints before Phase 3
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "DROP ALL UNIQUE CONSTRAINTS\n";
echo "==========================================\n\n";

// Get all unique constraints
$query = "
    SELECT 
        tc.TABLE_NAME,
        tc.CONSTRAINT_NAME,
        kcu.COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
        JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
            ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
            AND tc.TABLE_SCHEMA = kcu.TABLE_SCHEMA
    WHERE
        tc.TABLE_SCHEMA = 'sibit'
        AND tc.CONSTRAINT_TYPE = 'UNIQUE'
    ORDER BY
        tc.TABLE_NAME, tc.CONSTRAINT_NAME
";

$constraints = DB::select($query);

echo "Found " . count($constraints) . " unique constraints\n\n";

foreach ($constraints as $constraint) {
    echo "Dropping: {$constraint->TABLE_NAME}.{$constraint->CONSTRAINT_NAME}\n";
    try {
        DB::statement("ALTER TABLE `{$constraint->TABLE_NAME}` DROP INDEX `{$constraint->CONSTRAINT_NAME}`;");
        echo "  ✓ Dropped\n";
    } catch (\Exception $e) {
        echo "  ⚠️  Error: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ All unique constraints dropped!\n";
echo "You can now run Phase 3 migration again.\n\n";
