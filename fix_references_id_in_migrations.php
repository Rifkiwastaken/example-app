<?php

/**
 * Fix ->references('id') to reference custom IDs in migrations
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
    'inventory_type_warehouses' => 'inventory_type_warehouse_id',
    'inventory_type_seeds' => 'inventory_type_seed_id',
    'inventory_type_certification_reports' => 'inventory_type_certification_report_id',
    'inventory_notes' => 'inventory_note_id',
    'inventory_photos' => 'inventory_photo_id',
    'sales' => 'sale_id',
    'sale_items' => 'sale_item_id',
    'tasks' => 'task_id',
    'task_series' => 'task_series_id',
    'task_templates' => 'task_template_id',
    'locations' => 'location_id',
    'nutrients' => 'nutrient_id',
    'treatments' => 'treatment_id',
    'expenses' => 'expense_id',
    'attachments' => 'attachment_id',
    'seed_histories' => 'seed_history_id',
    'planting_losses' => 'planting_loss_id',
    'plant_notes' => 'plant_note_id',
    'plant_photos' => 'plant_photo_id',
    'planting_location_notes' => 'planting_location_note_id',
    'planting_location_photos' => 'planting_location_photo_id',
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
    
    // Fix ->references('id')->on('table_name')
    foreach ($tablePkMap as $table => $pk) {
        $content = preg_replace(
            "/->references\('id'\)->on\('$table'\)/",
            "->references('$pk')->on('$table')",
            $content
        );
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Fixed references('id') in: $filename\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "References Fix Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "========================================\n";
