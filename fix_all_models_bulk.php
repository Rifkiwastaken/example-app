<?php
/**
 * Bulk fix all model relationships
 * This will update all models at once
 */

echo "==========================================\n";
echo "BULK FIX ALL MODEL RELATIONSHIPS\n";
echo "==========================================\n\n";

// Model to custom ID mapping
$models = [
    'Plant' => 'plant_id',
    'PlantType' => 'plant_type_id',
    'Planting' => 'planting_id',
    'PlantingLocation' => 'planting_location_id',
    'Harvest' => 'harvest_id',
    'Certification' => 'certification_id',
    'CertificationReport' => 'certification_report_id',
    'Warehouse' => 'warehouse_id',
    'Bin' => 'bin_id',
    'InventoryLot' => 'inventory_lot_id',
    'InventoryTransaction' => 'inventory_transaction_id',
    'Sale' => 'sale_id',
    'SaleItem' => 'sale_item_id',
    'Task' => 'task_id',
    'TaskSeries' => 'task_series_id',
    'Expense' => 'expense_id',
    'Nutrient' => 'nutrient_id',
    'Treatment' => 'treatment_id',
    'Attachment' => 'attachment_id',
    'SeedHistory' => 'seed_history_id',
    'PlantingLoss' => 'planting_loss_id',
    'PlantNote' => 'plant_note_id',
    'PlantPhoto' => 'plant_photo_id',
    'PlantingLocationNote' => 'planting_location_note_id',
    'PlantingLocationPhoto' => 'planting_location_photo_id',
    'InventoryNote' => 'inventory_note_id',
    'InventoryPhoto' => 'inventory_photo_id',
    'Location' => 'location_id',
    'User' => 'user_id',
];

$fixedCount = 0;
$errors = [];

foreach ($models as $modelName => $customId) {
    $filePath = "app/Models/{$modelName}.php";
    
    if (!file_exists($filePath)) {
        echo "⏭️  Skipped: {$modelName} (file not found)\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Pattern 1: Fix belongsTo without explicit keys
    // From: return $this->belongsTo(Model::class, 'foreign_key');
    // To: return $this->belongsTo(Model::class, 'foreign_key', 'owner_key');
    
    $patterns = [
        // belongsTo with only foreign key
        '/return \$this->belongsTo\((\w+)::class, \'(\w+)\'\);/' => function($matches) use ($models) {
            $relatedModel = $matches[1];
            $foreignKey = $matches[2];
            $ownerKey = $models[$relatedModel] ?? 'id';
            return "return \$this->belongsTo({$relatedModel}::class, '{$foreignKey}', '{$ownerKey}');";
        },
        
        // belongsTo without any keys
        '/return \$this->belongsTo\((\w+)::class\);/' => function($matches) use ($models, $modelName) {
            $relatedModel = $matches[1];
            $ownerKey = $models[$relatedModel] ?? 'id';
            // Guess foreign key from model name
            $foreignKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $relatedModel)) . '_id';
            return "return \$this->belongsTo({$relatedModel}::class, '{$foreignKey}', '{$ownerKey}');";
        },
        
        // hasMany with only foreign key
        '/return \$this->hasMany\((\w+)::class, \'(\w+)\'\);/' => function($matches) use ($customId) {
            $relatedModel = $matches[1];
            $foreignKey = $matches[2];
            return "return \$this->hasMany({$relatedModel}::class, '{$foreignKey}', '{$customId}');";
        },
        
        // hasMany without any keys
        '/return \$this->hasMany\((\w+)::class\);/' => function($matches) use ($customId, $modelName) {
            $relatedModel = $matches[1];
            // Guess foreign key from current model name
            $foreignKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName)) . '_id';
            return "return \$this->hasMany({$relatedModel}::class, '{$foreignKey}', '{$customId}');";
        },
    ];
    
    $changesMade = false;
    foreach ($patterns as $pattern => $replacement) {
        $newContent = preg_replace_callback($pattern, $replacement, $content);
        if ($newContent !== $content) {
            $content = $newContent;
            $changesMade = true;
        }
    }
    
    if ($changesMade) {
        file_put_contents($filePath, $content);
        echo "✅ Fixed: {$modelName}\n";
        $fixedCount++;
    } else {
        echo "⏭️  No changes: {$modelName}\n";
    }
}

echo "\n==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n";
echo "Models fixed: {$fixedCount}\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n⚠️  IMPORTANT:\n";
echo "1. Review the changes in each model file\n";
echo "2. Some complex relationships may need manual review\n";
echo "3. Clear cache: php artisan cache:clear\n";
echo "4. Test the application\n\n";
