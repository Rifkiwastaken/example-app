<?php
/**
 * Update all models to use HasCustomId trait
 */

$modelsDir = __DIR__ . '/app/Models';
$traitUse = "use App\Traits\HasCustomId;";
$traitInClass = "    use HasCustomId;";

// Models yang perlu di-update
$models = [
    'User.php',
    'PlantType.php',
    'Plant.php',
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
    'Sale.php',
    'SaleItem.php',
    'Task.php',
    'TaskSeries.php',
    'Location.php',
    'Nutrient.php',
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
];

echo "==========================================\n";
echo "UPDATE ALL MODELS WITH HasCustomId TRAIT\n";
echo "==========================================\n\n";

$updated = 0;
$skipped = 0;

foreach ($models as $modelFile) {
    $filePath = $modelsDir . '/' . $modelFile;
    
    if (!file_exists($filePath)) {
        echo "⚠️  Skipped: {$modelFile} (file not found)\n";
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Check if already has the trait
    if (strpos($content, 'use HasCustomId') !== false) {
        echo "⏭️  Skipped: {$modelFile} (already has trait)\n";
        $skipped++;
        continue;
    }
    
    // Add use statement at the top (after namespace)
    if (strpos($content, $traitUse) === false) {
        $content = preg_replace(
            '/(namespace\s+App\\\\Models;)/',
            "$1\n\n{$traitUse}",
            $content
        );
    }
    
    // Add trait usage in class (after "use HasFactory;" or at the beginning of class)
    if (strpos($content, 'use HasFactory;') !== false) {
        $content = preg_replace(
            '/(use HasFactory;)/',
            "$1\n{$traitInClass}",
            $content
        );
    } else {
        // Add after class declaration
        $content = preg_replace(
            '/(class\s+\w+\s+extends\s+Model\s*\{)/',
            "$1\n{$traitInClass}\n",
            $content
        );
    }
    
    // Write back
    file_put_contents($filePath, $content);
    echo "✅ Updated: {$modelFile}\n";
    $updated++;
}

echo "\n==========================================\n";
echo "Summary:\n";
echo "  ✅ Updated: {$updated} models\n";
echo "  ⏭️  Skipped: {$skipped} models\n";
echo "==========================================\n\n";

echo "Next steps:\n";
echo "1. Review the updated models\n";
echo "2. Test creating new records\n";
echo "3. Verify custom IDs are generated automatically\n\n";
