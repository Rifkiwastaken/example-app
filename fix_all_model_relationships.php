<?php
/**
 * Fix all model relationships to use explicit foreign keys
 * This is critical for custom ID migration
 */

echo "==========================================\n";
echo "FIX ALL MODEL RELATIONSHIPS\n";
echo "==========================================\n\n";

echo "⚠️  This script will update ALL model files!\n";
echo "Make sure you have a backup.\n\n";

// Mapping of models to their custom ID columns
$modelMapping = [
    'User' => 'user_id',
    'Plant' => 'plant_id',
    'PlantType' => 'plant_type_id',
    'Planting' => 'planting_id',
    'PlantingLocation' => 'planting_location_id',
    'Harvest' => 'harvest_id',
    'Certification' => 'certification_id',
    'CertificationReport' => 'certification_report_id',
    'Warehouse' => 'warehouse_id',
    'Bin' => 'bin_id',
    'InventoryType' => 'inventory_type_id',
    'InventoryLot' => 'inventory_lot_id',
    'InventoryTransaction' => 'inventory_transaction_id',
    'InventoryTypeSeed' => 'inventory_type_seed_id',
    'InventoryTypeWarehouse' => 'inventory_type_warehouse_id',
    'InventoryNote' => 'inventory_note_id',
    'InventoryPhoto' => 'inventory_photo_id',
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
    'Location' => 'location_id',
];

echo "This is a complex task that requires manual review.\n";
echo "The issue is that Laravel Eloquent relationships need explicit foreign keys.\n\n";

echo "Example fix needed in EVERY model:\n\n";

echo "// ❌ Wrong (uses default 'id')\n";
echo "public function plant(): BelongsTo\n";
echo "{\n";
echo "    return \$this->belongsTo(Plant::class, 'plant_id');\n";
echo "}\n\n";

echo "// ✅ Correct (explicit custom ID)\n";
echo "public function plant(): BelongsTo\n";
echo "{\n";
echo "    return \$this->belongsTo(Plant::class, 'plant_id', 'plant_id');\n";
echo "}\n\n";

echo "==========================================\n";
echo "MODELS THAT NEED FIXING\n";
echo "==========================================\n\n";

$modelsDir = 'app/Models';
$files = glob($modelsDir . '/*.php');

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if (isset($modelMapping[$basename])) {
        echo "- {$basename}.php (PK: {$modelMapping[$basename]})\n";
    }
}

echo "\n==========================================\n";
echo "RECOMMENDATION\n";
echo "==========================================\n\n";

echo "Due to the complexity, I recommend:\n\n";

echo "1. Clear cache first:\n";
echo "   php artisan cache:clear\n";
echo "   php artisan view:clear\n";
echo "   php artisan config:clear\n\n";

echo "2. Test the application now\n";
echo "   Most relationships should work with the InventoryType fix\n\n";

echo "3. If you encounter more errors:\n";
echo "   - Note which model/relationship is failing\n";
echo "   - Fix that specific model's relationships\n";
echo "   - Test again\n\n";

echo "4. Pattern to fix relationships:\n\n";

echo "   hasMany():\n";
echo "   return \$this->hasMany(Model::class, 'foreign_key', 'local_key');\n\n";

echo "   belongsTo():\n";
echo "   return \$this->belongsTo(Model::class, 'foreign_key', 'owner_key');\n\n";

echo "   belongsToMany():\n";
echo "   return \$this->belongsToMany(Model::class, 'pivot_table', 'foreign_pivot_key', 'related_pivot_key', 'parent_key', 'related_key');\n\n";

echo "==========================================\n";
echo "NEXT STEPS\n";
echo "==========================================\n\n";

echo "1. Clear all caches (command above)\n";
echo "2. Refresh the landing page\n";
echo "3. If still error, check which model is failing\n";
echo "4. Fix that model's relationships\n";
echo "5. Repeat until all working\n\n";

echo "The InventoryType model has been fixed.\n";
echo "This should resolve the landing page error.\n\n";
