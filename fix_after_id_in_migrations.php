<?php

/**
 * Fix ->after('id') to ->after('custom_id') in migrations
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
    'plantings' => 'planting_id',
    'planting_losses' => 'planting_loss_id',
    'plant_notes' => 'plant_note_id',
    'plant_photos' => 'plant_photo_id',
    'planting_location_notes' => 'planting_location_note_id',
    'planting_location_photos' => 'planting_location_photo_id',
    'inventory_notes' => 'inventory_note_id',
    'inventory_photos' => 'inventory_photo_id',
    'sale_items' => 'sale_item_id',
    'inventory_type_warehouses' => 'inventory_type_warehouse_id',
    'inventory_type_seeds' => 'inventory_type_seed_id',
    'seed_histories' => 'seed_history_id',
    'attachments' => 'attachment_id',
];

$files = glob($migrationsPath . '/*.php');
$stats = ['processed' => 0, 'modified' => 0];

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip phase migrations and create migrations
    if (strpos($filename, 'phase') !== false || strpos($filename, 'create_') !== false) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Try to detect which table this migration is modifying
    // Look for Schema::table('table_name'
    if (preg_match("/Schema::table\('(\w+)'/", $content, $matches)) {
        $tableName = $matches[1];
        
        if (isset($tablePkMap[$tableName])) {
            $customPk = $tablePkMap[$tableName];
            
            // Replace ->after('id') with ->after('custom_pk')
            $content = preg_replace(
                "/->after\('id'\)/",
                "->after('$customPk')",
                $content
            );
        }
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Fixed ->after('id') in: $filename\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "After ID Fix Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "========================================\n";
