<?php

/**
 * Update all models to use HasCustomId trait and set correct primary key
 */

$modelsPath = __DIR__ . '/app/Models';

// Model to PK mapping
$modelPkMap = [
    'User.php' => 'user_id',
    'PlantType.php' => 'plant_type_id',
    'Plant.php' => 'plant_id',
    'PlantingLocation.php' => 'planting_location_id',
    'Planting.php' => 'planting_id',
    'Harvest.php' => 'harvest_id',
    'Certification.php' => 'certification_id',
    'CertificationReport.php' => 'certification_report_id',
    'Warehouse.php' => 'warehouse_id',
    'Bin.php' => 'bin_id',
    'InventoryType.php' => 'inventory_type_id',
    'InventoryLot.php' => 'inventory_lot_id',
    'InventoryTransaction.php' => 'inventory_transaction_id',
    'InventoryTypeWarehouse.php' => 'inventory_type_warehouse_id',
    'InventoryTypeSeed.php' => 'inventory_type_seed_id',
    'InventoryNote.php' => 'inventory_note_id',
    'InventoryPhoto.php' => 'inventory_photo_id',
    'Sale.php' => 'sale_id',
    'SaleItem.php' => 'sale_item_id',
    'Task.php' => 'task_id',
    'TaskSeries.php' => 'task_series_id',
    'Location.php' => 'location_id',
    'Nutrient.php' => 'nutrient_id',
    'Treatment.php' => 'treatment_id',
    'Expense.php' => 'expense_id',
    'Attachment.php' => 'attachment_id',
    'SeedHistory.php' => 'seed_history_id',
    'PlantingLoss.php' => 'planting_loss_id',
    'PlantNote.php' => 'plant_note_id',
    'PlantPhoto.php' => 'plant_photo_id',
    'PlantingLocationNote.php' => 'planting_location_note_id',
    'PlantingLocationPhoto.php' => 'planting_location_photo_id',
];

$stats = ['processed' => 0, 'modified' => 0];

foreach ($modelPkMap as $filename => $pk) {
    $file = $modelsPath . '/' . $filename;
    
    if (!file_exists($file)) {
        echo "⚠ File not found: $filename\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Check if already has HasCustomId trait
    if (strpos($content, 'use HasCustomId;') === false) {
        // Add use statement at top
        if (strpos($content, 'use App\Traits\HasCustomId;') === false) {
            $content = preg_replace(
                '/(namespace App\\\\Models;)/',
                "$1\n\nuse App\\Traits\\HasCustomId;",
                $content
            );
        }
        
        // Add trait in class
        $content = preg_replace(
            '/(class \w+ extends Model\s*\{)/',
            "$1\n    use HasCustomId;",
            $content
        );
    }
    
    // Add or update protected $primaryKey
    if (strpos($content, 'protected $primaryKey') === false) {
        // Add after 'use HasCustomId;'
        $content = preg_replace(
            '/(use HasCustomId;)/',
            "$1\n\n    protected \$primaryKey = '$pk';",
            $content
        );
    } else {
        // Update existing
        $content = preg_replace(
            '/protected \$primaryKey = [\'"].*?[\'"];/',
            "protected \$primaryKey = '$pk';",
            $content
        );
    }
    
    // Remove $incrementing and $keyType if exists (trait handles this)
    $content = preg_replace('/\s*public \$incrementing = false;\s*/', "\n", $content);
    $content = preg_replace('/\s*protected \$keyType = [\'"]string[\'"];\s*/', "\n", $content);
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Updated: $filename\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "Model Update Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "========================================\n";
