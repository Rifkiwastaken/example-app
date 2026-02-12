<?php

/**
 * Convert existing migrations to use custom IDs as primary keys
 */

$migrationsPath = __DIR__ . '/database/migrations';
$backupPath = __DIR__ . '/database/migrations_backup_' . date('YmdHis');

// Backup original migrations
if (!is_dir($backupPath)) {
    mkdir($backupPath, 0755, true);
}

// Table to custom ID mapping
$tableIdMap = [
    'users' => ['pk' => 'user_id', 'prefix' => 'USR'],
    'plant_types' => ['pk' => 'plant_type_id', 'prefix' => 'PTY'],
    'plants' => ['pk' => 'plant_id', 'prefix' => 'PLT'],
    'planting_locations' => ['pk' => 'planting_location_id', 'prefix' => 'LOC'],
    'plantings' => ['pk' => 'planting_id', 'prefix' => 'PLN'],
    'harvests' => ['pk' => 'harvest_id', 'prefix' => 'HRV'],
    'certifications' => ['pk' => 'certification_id', 'prefix' => 'CRT'],
    'certification_reports' => ['pk' => 'certification_report_id', 'prefix' => 'CRP'],
    'warehouses' => ['pk' => 'warehouse_id', 'prefix' => 'WHS'],
    'bins' => ['pk' => 'bin_id', 'prefix' => 'BIN'],
    'inventory_types' => ['pk' => 'inventory_type_id', 'prefix' => 'INV'],
    'inventory_lots' => ['pk' => 'inventory_lot_id', 'prefix' => 'LOT'],
    'inventory_transactions' => ['pk' => 'inventory_transaction_id', 'prefix' => 'TRX'],
    'inventory_type_warehouses' => ['pk' => 'inventory_type_warehouse_id', 'prefix' => 'ITW'],
    'inventory_type_seeds' => ['pk' => 'inventory_type_seed_id', 'prefix' => 'ITS'],
    'inventory_type_certification_reports' => ['pk' => 'inventory_type_certification_report_id', 'prefix' => 'ICR'],
    'inventory_notes' => ['pk' => 'inventory_note_id', 'prefix' => 'INN'],
    'inventory_photos' => ['pk' => 'inventory_photo_id', 'prefix' => 'INP'],
    'sales' => ['pk' => 'sale_id', 'prefix' => 'SAL'],
    'sale_items' => ['pk' => 'sale_item_id', 'prefix' => 'SIT'],
    'tasks' => ['pk' => 'task_id', 'prefix' => 'TSK'],
    'task_series' => ['pk' => 'task_series_id', 'prefix' => 'TSR'],
    'task_templates' => ['pk' => 'task_template_id', 'prefix' => 'TTP'],
    'locations' => ['pk' => 'location_id', 'prefix' => 'LCT'],
    'nutrients' => ['pk' => 'nutrient_id', 'prefix' => 'NTR'],
    'treatments' => ['pk' => 'treatment_id', 'prefix' => 'TRT'],
    'expenses' => ['pk' => 'expense_id', 'prefix' => 'EXP'],
    'attachments' => ['pk' => 'attachment_id', 'prefix' => 'ATT'],
    'seed_histories' => ['pk' => 'seed_history_id', 'prefix' => 'SDH'],
    'planting_losses' => ['pk' => 'planting_loss_id', 'prefix' => 'PLS'],
    'plant_notes' => ['pk' => 'plant_note_id', 'prefix' => 'PLN'],
    'plant_photos' => ['pk' => 'plant_photo_id', 'prefix' => 'PHP'],
    'planting_location_notes' => ['pk' => 'planting_location_note_id', 'prefix' => 'LCN'],
    'planting_location_photos' => ['pk' => 'planting_location_photo_id', 'prefix' => 'LCP'],
    'user_planting_location_land_manager' => ['pk' => 'user_planting_location_land_manager_id', 'prefix' => 'ULM'],
    'user_planting_location_land_worker' => ['pk' => 'user_planting_location_land_worker_id', 'prefix' => 'ULW'],
];

$files = glob($migrationsPath . '/*.php');
$stats = ['processed' => 0, 'modified' => 0, 'skipped' => 0];

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip phase migrations and special migrations
    if (strpos($filename, 'phase') !== false || 
        strpos($filename, 'password_reset') !== false ||
        strpos($filename, 'failed_jobs') !== false ||
        strpos($filename, 'personal_access') !== false) {
        $stats['skipped']++;
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Find table name
    $tableName = null;
    if (preg_match("/Schema::create\('(\w+)'/", $content, $matches)) {
        $tableName = $matches[1];
    }
    
    if (!$tableName || !isset($tableIdMap[$tableName])) {
        $stats['skipped']++;
        continue;
    }
    
    $pk = $tableIdMap[$tableName]['pk'];
    
    // Backup original
    copy($file, $backupPath . '/' . $filename);
    
    // Replace $table->id() with custom ID
    $content = preg_replace(
        '/\$table->id\(\);/',
        "\$table->string('$pk', 36)->primary();",
        $content
    );
    
    // Replace foreignId with string for FK columns
    $content = preg_replace(
        '/\$table->foreignId\(\'(\w+)\'\)/',
        "\$table->string('$1', 36)",
        $content
    );
    
    // Replace unsignedBigInteger for FK
    $content = preg_replace(
        '/\$table->unsignedBigInteger\(\'(\w+)\'\)/',
        "\$table->string('$1', 36)",
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Modified: $filename (table: $tableName, PK: $pk)\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "Migration Conversion Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "Skipped: {$stats['skipped']}\n";
echo "Backup location: $backupPath\n";
echo "========================================\n";
