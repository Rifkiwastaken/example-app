<?php
/**
 * Script untuk membersihkan kolom custom ID yang sudah ada
 * Agar bisa mulai migrasi dari awal dengan bersih
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "==========================================\n";
echo "CLEANUP CUSTOM ID COLUMNS\n";
echo "==========================================\n\n";

echo "⚠️  WARNING: This will drop all custom ID columns!\n";
echo "Make sure you have a backup before proceeding.\n\n";

$columnsToClean = [
    'users' => ['user_id'],
    'plant_types' => ['plant_type_id'],
    'plants' => ['plant_id', 'new_plant_type_id', 'new_planting_location_id'],
];

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    foreach ($columnsToClean as $table => $columns) {
        echo "Cleaning table: {$table}\n";
        
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                echo "  - Dropping column: {$column}...";
                Schema::table($table, function ($table) use ($column) {
                    $table->dropColumn($column);
                });
                echo " ✓\n";
            } else {
                echo "  - Column {$column} not found, skipping\n";
            }
        }
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "\n==========================================\n";
    echo "✓ CLEANUP COMPLETED!\n";
    echo "==========================================\n";
    echo "Database is now clean and ready for fresh migration.\n\n";
    
} catch (Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "\n==========================================\n";
    echo "✗ ERROR!\n";
    echo "==========================================\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
