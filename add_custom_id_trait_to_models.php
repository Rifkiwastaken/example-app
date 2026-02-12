<?php

/**
 * Add HasCustomId trait to all models
 */

$modelsPath = __DIR__ . '/app/Models';

$models = [
    'User', 'PlantType', 'Plant', 'PlantingLocation', 'Planting', 'Harvest',
    'Certification', 'CertificationReport', 'Warehouse', 'Bin', 'InventoryType',
    'InventoryLot', 'InventoryTransaction', 'InventoryTypeSeed',
    'InventoryNote', 'InventoryPhoto', 'Sale', 'SaleItem', 'Task', 'TaskSeries',
    'Location', 'Nutrient', 'Treatment', 'Expense', 'Attachment', 'SeedHistory',
    'PlantingLoss', 'PlantNote', 'PlantPhoto', 'PlantingLocationNote', 'PlantingLocationPhoto'
];

$stats = ['processed' => 0, 'modified' => 0, 'skipped' => 0];

foreach ($models as $modelName) {
    $filePath = $modelsPath . '/' . $modelName . '.php';
    
    if (!file_exists($filePath)) {
        echo "⚠ Skipped: $modelName (file not found)\n";
        $stats['skipped']++;
        continue;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Check if HasCustomId is already imported
    if (strpos($content, 'use App\Traits\HasCustomId;') === false) {
        // Add import after other use statements
        $content = preg_replace(
            '/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)/',
            "$1\nuse App\\Traits\\HasCustomId;",
            $content
        );
    }
    
    // Check if trait is already used
    if (strpos($content, 'use HasCustomId;') === false) {
        // Add trait usage after HasFactory
        $content = preg_replace(
            '/(use HasFactory;)/',
            "$1\n    use HasCustomId;",
            $content
        );
    }
    
    // Remove protected $primaryKey if exists (HasCustomId will handle it)
    $content = preg_replace(
        '/\s*protected \$primaryKey = [^;]+;/',
        '',
        $content
    );
    
    // Remove protected $keyType if exists
    $content = preg_replace(
        '/\s*protected \$keyType = [^;]+;/',
        '',
        $content
    );
    
    // Remove public $incrementing if exists
    $content = preg_replace(
        '/\s*public \$incrementing = [^;]+;/',
        '',
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $stats['modified']++;
        echo "✓ Modified: $modelName\n";
    } else {
        $stats['skipped']++;
        echo "- Skipped: $modelName (already has trait)\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "Model Update Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "Skipped: {$stats['skipped']}\n";
echo "========================================\n";
