<?php

/**
 * Fix all SQL queries in migrations that reference table.id
 */

$migrationsPath = __DIR__ . '/database/migrations';

// Mapping of table to custom PK
$tablePkMap = [
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
    'sales' => 'sale_id',
    'tasks' => 'task_id',
    'task_series' => 'task_series_id',
    'task_templates' => 'task_template_id',
    'locations' => 'location_id',
    'nutrients' => 'nutrient_id',
    'treatments' => 'treatment_id',
    'expenses' => 'expense_id',
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
    
    // Fix SQL queries: table.id -> table.custom_id
    foreach ($tablePkMap as $table => $pk) {
        // Pattern: table.id in SQL queries
        $content = preg_replace(
            "/\b$table\.id\b/",
            "$table.$pk",
            $content
        );
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Fixed SQL queries in: $filename\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "SQL Query Fix Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "========================================\n";
