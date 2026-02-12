<?php

/**
 * Script untuk memperbaiki semua referensi ke custom ID di view files
 * karena database belum dimigrate ke custom ID
 */

$viewsPath = __DIR__ . '/resources/views';

// Patterns yang perlu diganti
$patterns = [
    // User references
    '/\$user->user_id/' => '$user->id',
    '/auth\(\)->user_id/' => 'auth()->id()',
    
    // Plant references  
    '/\$plant->plant_id/' => '$plant->id',
    
    // PlantType references
    '/\$plantType->plant_type_id/' => '$plantType->id',
    '/\$type->plant_type_id/' => '$type->id',
    
    // PlantingLocation references
    '/\$plantingLocation->planting_location_id/' => '$plantingLocation->id',
    '/\$location->planting_location_id/' => '$location->id',
    
    // Planting references
    '/\$planting->planting_id/' => '$planting->id',
    
    // Harvest references
    '/\$harvest->harvest_id/' => '$harvest->id',
    
    // Certification references
    '/\$certification->certification_id/' => '$certification->id',
    
    // CertificationReport references
    '/\$report->certification_report_id/' => '$report->id',
    '/\$certificationReport->certification_report_id/' => '$certificationReport->id',
    
    // Warehouse references
    '/\$warehouse->warehouse_id/' => '$warehouse->id',
    
    // Bin references
    '/\$bin->bin_id/' => '$bin->id',
    
    // InventoryType references
    '/\$inventoryType->inventory_type_id/' => '$inventoryType->id',
    '/\$type->inventory_type_id/' => '$type->id',
    
    // InventoryLot references
    '/\$lot->inventory_lot_id/' => '$lot->id',
    '/\$inventoryLot->inventory_lot_id/' => '$inventoryLot->id',
    
    // Sale references
    '/\$sale->sale_id/' => '$sale->id',
    
    // Task references
    '/\$task->task_id/' => '$task->id',
    
    // Expense references
    '/\$expense->expense_id/' => '$expense->id',
];

function processDirectory($dir, $patterns, &$stats) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);
            $originalContent = $content;
            $fileChanged = false;
            
            foreach ($patterns as $pattern => $replacement) {
                $newContent = preg_replace($pattern, $replacement, $content);
                if ($newContent !== $content) {
                    $content = $newContent;
                    $fileChanged = true;
                    $stats['patterns_found']++;
                }
            }
            
            if ($fileChanged) {
                file_put_contents($filePath, $content);
                $stats['files_modified']++;
                $relativePath = str_replace(__DIR__ . '/', '', $filePath);
                echo "✓ Fixed: $relativePath\n";
            }
        }
    }
}

echo "Fixing all view references to use 'id' instead of custom IDs...\n\n";

$stats = [
    'files_modified' => 0,
    'patterns_found' => 0,
];

processDirectory($viewsPath, $patterns, $stats);

echo "\n";
echo "========================================\n";
echo "Summary:\n";
echo "Files modified: {$stats['files_modified']}\n";
echo "Patterns replaced: {$stats['patterns_found']}\n";
echo "========================================\n";
