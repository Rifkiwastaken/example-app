<?php

/**
 * Script untuk menghapus HasCustomId trait dari semua models
 * karena database belum dimigrate ke custom ID
 */

$modelsPath = __DIR__ . '/app/Models';

$modelsToFix = [
    'Plant.php',
    'PlantType.php',
    'PlantingLocation.php',
    'Planting.php',
    'Harvest.php',
    'Certification.php',
    'CertificationReport.php',
    'Warehouse.php',
    'Bin.php',
    'InventoryType.php',
    'InventoryLot.php',
    'InventoryTransaction.php',
    'InventoryTypeSeed.php',
    'InventoryTypeWarehouse.php',
    'Sale.php',
    'SaleItem.php',
    'Task.php',
    'TaskSeries.php',
    'Expense.php',
    'Attachment.php',
    'SeedHistory.php',
    'PlantingLoss.php',
    'PlantNote.php',
    'PlantPhoto.php',
    'PlantingLocationNote.php',
    'PlantingLocationPhoto.php',
    'InventoryNote.php',
    'InventoryPhoto.php',
    'Location.php',
    'Nutrient.php',
    'Treatment.php',
];

$stats = [
    'files_modified' => 0,
    'traits_removed' => 0,
];

foreach ($modelsToFix as $modelFile) {
    $filePath = $modelsPath . '/' . $modelFile;
    
    if (!file_exists($filePath)) {
        echo "⚠ Skipped (not found): $modelFile\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Remove use statement
    $content = preg_replace('/use App\\\\Traits\\\\HasCustomId;\s*\n/', '', $content);
    
    // Remove trait from class
    $content = preg_replace('/(use\s+[^;]+),\s*HasCustomId/', '$1', $content);
    $content = preg_replace('/(use\s+)HasCustomId,\s*/', '$1', $content);
    $content = preg_replace('/(use\s+)HasCustomId;/', '$1;', $content);
    
    // Fix relationships - remove explicit foreign key parameters if they use custom IDs
    // Example: belongsTo(Model::class, 'model_id', 'model_id') -> belongsTo(Model::class)
    $content = preg_replace(
        '/->belongsTo\(([^,]+),\s*\'(\w+)_id\',\s*\'(\w+)_id\'\)/',
        '->belongsTo($1)',
        $content
    );
    
    $content = preg_replace(
        '/->belongsToMany\(([^,]+),\s*\'([^\']+)\',\s*\'(\w+)_id\',\s*\'(\w+)_id\',\s*\'(\w+)_id\',\s*\'(\w+)_id\'\)/',
        '->belongsToMany($1, \'$2\')',
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $stats['files_modified']++;
        $stats['traits_removed']++;
        echo "✓ Fixed: $modelFile\n";
    } else {
        echo "- No changes: $modelFile\n";
    }
}

echo "\n";
echo "========================================\n";
echo "Summary:\n";
echo "Files modified: {$stats['files_modified']}\n";
echo "Traits removed: {$stats['traits_removed']}\n";
echo "========================================\n";
