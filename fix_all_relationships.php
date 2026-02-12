<?php

/**
 * Fix all model relationships to use correct foreign keys and owner keys
 */

$modelsPath = __DIR__ . '/app/Models';

$fixes = [
    'Plant.php' => [
        "return \$this->belongsTo(PlantType::class);" => "return \$this->belongsTo(PlantType::class, 'plant_type_id', 'plant_type_id');",
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
    ],
    'Planting.php' => [
        "return \$this->belongsTo(Plant::class);" => "return \$this->belongsTo(Plant::class, 'plant_id', 'plant_id');",
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
    ],
    'Harvest.php' => [
        "return \$this->belongsTo(Plant::class);" => "return \$this->belongsTo(Plant::class, 'plant_id', 'plant_id');",
        "return \$this->belongsTo(Planting::class);" => "return \$this->belongsTo(Planting::class, 'planting_id', 'planting_id');",
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'Certification.php' => [
        "return \$this->belongsTo(Plant::class);" => "return \$this->belongsTo(Plant::class, 'plant_id', 'plant_id');",
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'CertificationReport.php' => [
        "return \$this->belongsTo(Certification::class);" => "return \$this->belongsTo(Certification::class, 'certification_id', 'certification_id');",
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'Bin.php' => [
        "return \$this->belongsTo(Warehouse::class);" => "return \$this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');",
    ],
    'InventoryLot.php' => [
        "return \$this->belongsTo(InventoryType::class);" => "return \$this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');",
        "return \$this->belongsTo(Bin::class);" => "return \$this->belongsTo(Bin::class, 'bin_id', 'bin_id');",
    ],
    'InventoryTransaction.php' => [
        "return \$this->belongsTo(InventoryLot::class);" => "return \$this->belongsTo(InventoryLot::class, 'inventory_lot_id', 'inventory_lot_id');",
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'InventoryTypeSeed.php' => [
        "return \$this->belongsTo(InventoryType::class);" => "return \$this->belongsTo(InventoryType::class, 'inventory_type_id', 'inventory_type_id');",
        "return \$this->belongsTo(PlantType::class);" => "return \$this->belongsTo(PlantType::class, 'plant_type_id', 'plant_type_id');",
        "return \$this->belongsTo(CertificationReport::class);" => "return \$this->belongsTo(CertificationReport::class, 'certification_report_id', 'certification_report_id');",
    ],
    'Sale.php' => [
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
    ],
    'SaleItem.php' => [
        "return \$this->belongsTo(Sale::class);" => "return \$this->belongsTo(Sale::class, 'sale_id', 'sale_id');",
        "return \$this->belongsTo(InventoryLot::class);" => "return \$this->belongsTo(InventoryLot::class, 'inventory_lot_id', 'inventory_lot_id');",
    ],
    'Task.php' => [
        "return \$this->belongsTo(User::class, 'user_id');" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
        "return \$this->belongsTo(User::class, 'assigned_to');" => "return \$this->belongsTo(User::class, 'assigned_to', 'user_id');",
        "return \$this->belongsTo(TaskSeries::class);" => "return \$this->belongsTo(TaskSeries::class, 'task_series_id', 'task_series_id');",
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
    ],
    'TaskSeries.php' => [
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'Nutrient.php' => [
        "return \$this->belongsTo(Planting::class);" => "return \$this->belongsTo(Planting::class, 'planting_id', 'planting_id');",
        "return \$this->belongsTo(User::class, 'responsible_person');" => "return \$this->belongsTo(User::class, 'responsible_person', 'user_id');",
    ],
    'Treatment.php' => [
        "return \$this->belongsTo(Planting::class);" => "return \$this->belongsTo(Planting::class, 'planting_id', 'planting_id');",
    ],
    'Expense.php' => [
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
        "return \$this->belongsTo(Planting::class);" => "return \$this->belongsTo(Planting::class, 'planting_id', 'planting_id');",
    ],
    'SeedHistory.php' => [
        "return \$this->belongsTo(InventoryTypeSeed::class);" => "return \$this->belongsTo(InventoryTypeSeed::class, 'inventory_type_seed_id', 'inventory_type_seed_id');",
    ],
    'PlantingLoss.php' => [
        "return \$this->belongsTo(Planting::class);" => "return \$this->belongsTo(Planting::class, 'planting_id', 'planting_id');",
    ],
    'PlantNote.php' => [
        "return \$this->belongsTo(Plant::class);" => "return \$this->belongsTo(Plant::class, 'plant_id', 'plant_id');",
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'PlantPhoto.php' => [
        "return \$this->belongsTo(Plant::class);" => "return \$this->belongsTo(Plant::class, 'plant_id', 'plant_id');",
    ],
    'PlantingLocationNote.php' => [
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
        "return \$this->belongsTo(User::class, 'user_id');" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
        "return \$this->belongsTo(User::class, 'assigned_to');" => "return \$this->belongsTo(User::class, 'assigned_to', 'user_id');",
    ],
    'PlantingLocationPhoto.php' => [
        "return \$this->belongsTo(PlantingLocation::class);" => "return \$this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');",
    ],
    'InventoryNote.php' => [
        "return \$this->belongsTo(InventoryLot::class);" => "return \$this->belongsTo(InventoryLot::class, 'inventory_lot_id', 'inventory_lot_id');",
        "return \$this->belongsTo(User::class);" => "return \$this->belongsTo(User::class, 'user_id', 'user_id');",
    ],
    'InventoryPhoto.php' => [
        "return \$this->belongsTo(InventoryLot::class);" => "return \$this->belongsTo(InventoryLot::class, 'inventory_lot_id', 'inventory_lot_id');",
    ],
];

$stats = ['processed' => 0, 'modified' => 0];

foreach ($fixes as $filename => $replacements) {
    $file = $modelsPath . '/' . $filename;
    
    if (!file_exists($file)) {
        echo "⚠ File not found: $filename\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['modified']++;
        echo "✓ Fixed relationships in: $filename\n";
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "Relationship Fix Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Modified: {$stats['modified']}\n";
echo "========================================\n";
