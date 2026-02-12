<?php

/**
 * Fix foreign key constraints in migrations to reference custom IDs
 */

$migrationsPath = __DIR__ . '/database/migrations';

// Mapping of FK column to referenced table and custom PK
$fkMapping = [
    'location_id' => ['table' => 'locations', 'pk' => 'location_id'],
    'plant_type_id' => ['table' => 'plant_types', 'pk' => 'plant_type_id'],
    'plant_id' => ['table' => 'plants', 'pk' => 'plant_id'],
    'planting_location_id' => ['table' => 'planting_locations', 'pk' => 'planting_location_id'],
    'planting_id' => ['table' => 'plantings', 'pk' => 'planting_id'],
    'user_id' => ['table' => 'users', 'pk' => 'user_id'],
    'warehouse_id' => ['table' => 'warehouses', 'pk' => 'warehouse_id'],
    'bin_id' => ['table' => 'bins', 'pk' => 'bin_id'],
    'inventory_type_id' => ['table' => 'inventory_types', 'pk' => 'inventory_type_id'],
    'inventory_lot_id' => ['table' => 'inventory_lots', 'pk' => 'inventory_lot_id'],
    'certification_id' => ['table' => 'certifications', 'pk' => 'certification_id'],
    'certification_report_id' => ['table' => 'certification_reports', 'pk' => 'certification_report_id'],
    'sale_id' => ['table' => 'sales', 'pk' => 'sale_id'],
    'task_series_id' => ['table' => 'task_series', 'pk' => 'task_series_id'],
    'assigned_to' => ['table' => 'users', 'pk' => 'user_id'],
];

$files = glob($migrationsPath . '/*.php');
$stats = ['processed' => 0, 'modified' => 0];

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip phase migrations
    if (strpos($filename, 'phase') !== false) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Replace foreignId()->constrained() patterns
    foreach ($fkMapping as $fkColumn => $config) {
        $table = $config['table'];
        $pk = $config['pk'];
        
        // Pattern 1: ->foreignId('column')->constrained('table')
        $pattern1 = "/->foreignId\('$fkColumn'\)->([^;]*?)constrained\('$table'\)/";
        $replacement1 = "->string('$fkColumn', 36)->$1foreign('$pk')->references('$pk')->on('$table')";
        $content = preg_replace($pattern1, $replacement1, $content);
        
        // Pattern 2: ->foreignId('column')->nullable()->constrained('table')
        $pattern2 = "/->foreignId\('$fkColumn'\)->nullable\(\)->constrained\('$table'\)/";
        $replacement2 = "->string('$fkColumn', 36)->nullable()->foreign('$pk')->references('$pk')->on('$table')";
        $content = preg_replace($pattern2, $replacement2, $content);
    }
    
    // Remove any remaining ->constrained() that wasn't caught
    $content = preg_replace('/->constrained\([^\)]*\)/', '', $content);
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Fixed FK constraints in: $filename\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "FK Constraint Fix Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "========================================\n";
