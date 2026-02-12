<?php
/**
 * Auto-fix common ID references in controllers
 * BACKUP YOUR FILES BEFORE RUNNING THIS!
 */

echo "==========================================\n";
echo "AUTO-FIX COMMON ID REFERENCES\n";
echo "==========================================\n\n";

echo "⚠️  WARNING: This will modify your files!\n";
echo "Make sure you have a backup before proceeding.\n\n";

// Common replacements for specific models
$replacements = [
    // User model
    "auth()->id()" => "auth()->user_id",
    "\$user->id" => "\$user->user_id",
    
    // Plant model
    "\$plant->id" => "\$plant->plant_id",
    "where('plant_id', \$plant->id)" => "where('plant_id', \$plant->plant_id)",
    
    // PlantType model
    "\$type->id" => "\$type->plant_type_id",
    
    // PlantingLocation model
    "\$plantingLocation->id" => "\$plantingLocation->planting_location_id",
    "\$location->id" => "\$location->planting_location_id",
    "\$loc->id" => "\$loc->planting_location_id",
    
    // Planting model
    "\$planting->id" => "\$planting->planting_id",
    "\$p->id" => "\$p->planting_id",
    
    // Harvest model
    "\$harvest->id" => "\$harvest->harvest_id",
    
    // Certification model
    "\$certification->id" => "\$certification->certification_id",
    
    // CertificationReport model
    "\$report->id" => "\$report->certification_report_id",
    "\$cert->id" => "\$cert->certification_report_id",
    "\$certReport->id" => "\$certReport->certification_report_id",
    "\$certificationReport->id" => "\$certificationReport->certification_report_id",
    
    // Warehouse model
    "\$warehouse->id" => "\$warehouse->warehouse_id",
    
    // Bin model
    "\$bin->id" => "\$bin->bin_id",
    
    // InventoryType model
    "\$inventoryType->id" => "\$inventoryType->inventory_type_id",
    "\$invType->id" => "\$invType->inventory_type_id",
    
    // InventoryLot model
    "\$lot->id" => "\$lot->inventory_lot_id",
    
    // InventoryTypeSeed model
    "\$seed->id" => "\$seed->inventory_type_seed_id",
    
    // Sale model
    "\$sale->id" => "\$sale->sale_id",
    
    // Task model
    "\$task->id" => "\$task->task_id",
    
    // Expense model
    "\$expense->id" => "\$expense->expense_id",
    
    // Nutrient model
    "\$nutrient->id" => "\$nutrient->nutrient_id",
    
    // Treatment model
    "\$treatment->id" => "\$treatment->treatment_id",
    
    // Note model
    "\$note->id" => "\$note->planting_location_note_id",
];

$files = [
    'app/Http/Controllers/LandingPageController.php',
    'app/Http/Controllers/CertificationController.php',
    'app/Http/Controllers/DashboardController.php',
    'app/Http/Controllers/HarvestController.php',
    'app/Http/Controllers/InventoryTypeController.php',
    'app/Http/Controllers/PlantController.php',
    'app/Http/Controllers/PlantingLocationController.php',
    'app/Http/Controllers/SaleController.php',
    'app/Http/Controllers/WarehouseController.php',
];

$totalReplacements = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⏭️  Skipped: {$file} (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileReplacements = 0;
    
    foreach ($replacements as $search => $replace) {
        $count = 0;
        $content = str_replace($search, $replace, $content, $count);
        $fileReplacements += $count;
    }
    
    if ($fileReplacements > 0) {
        file_put_contents($file, $content);
        echo "✅ {$file}: {$fileReplacements} replacements\n";
        $totalReplacements += $fileReplacements;
    } else {
        echo "⏭️  {$file}: No changes needed\n";
    }
}

echo "\n==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n";
echo "Total replacements: {$totalReplacements}\n\n";

echo "⚠️  IMPORTANT NEXT STEPS:\n";
echo "1. Review the changes in each file\n";
echo "2. Test the application thoroughly\n";
echo "3. Some references may need manual fixing\n";
echo "4. Check views (Blade files) separately\n\n";

echo "Note: This script only fixes controllers.\n";
echo "Views and models may need manual review.\n\n";
