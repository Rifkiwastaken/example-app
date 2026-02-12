<?php
/**
 * Auto-fix ID references in Blade views
 * BACKUP YOUR FILES BEFORE RUNNING THIS!
 */

echo "==========================================\n";
echo "AUTO-FIX VIEW ID REFERENCES\n";
echo "==========================================\n\n";

// Replacements for views (more conservative)
$replacements = [
    // Common patterns in views
    "{{ \$plant->id }}" => "{{ \$plant->plant_id }}",
    "{{ \$type->id }}" => "{{ \$type->plant_type_id }}",
    "{{ \$location->id }}" => "{{ \$location->planting_location_id }}",
    "{{ \$loc->id }}" => "{{ \$loc->planting_location_id }}",
    "{{ \$planting->id }}" => "{{ \$planting->planting_id }}",
    "{{ \$p->id }}" => "{{ \$p->planting_id }}",
    "{{ \$warehouse->id }}" => "{{ \$warehouse->warehouse_id }}",
    "{{ \$user->id }}" => "{{ \$user->user_id }}",
    "{{ \$invType->id }}" => "{{ \$invType->inventory_type_id }}",
    "{{ \$lot->id }}" => "{{ \$lot->inventory_lot_id }}",
    "{{ \$report->id }}" => "{{ \$report->certification_report_id }}",
    
    // In comparisons
    "== \$plant->id" => "== \$plant->plant_id",
    "== \$type->id" => "== \$type->plant_type_id",
    "== \$location->id" => "== \$location->planting_location_id",
    "== \$loc->id" => "== \$loc->planting_location_id",
    "== \$planting->id" => "== \$planting->planting_id",
    "== \$warehouse->id" => "== \$warehouse->warehouse_id",
    "== \$user->id" => "== \$user->user_id",
    "== \$invType->id" => "== \$invType->inventory_type_id",
    
    // auth()->id() - special case for user_id
    "auth()->id()" => "auth()->user_id",
    "{{ auth()->id() }}" => "{{ auth()->user_id }}",
    
    // data-user-id attributes
    'data-user-id="{{ $user->id }}"' => 'data-user-id="{{ $user->user_id }}"',
];

$viewDirs = [
    'resources/views/certifications',
    'resources/views/dashboard',
    'resources/views/planting',
    'resources/views/warehouse',
    'resources/views/sales',
    'resources/views/reports',
    'resources/views/users',
    'resources/views/landing',
];

$totalReplacements = 0;
$filesModified = 0;

foreach ($viewDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filepath = $file->getPathname();
            $content = file_get_contents($filepath);
            $originalContent = $content;
            $fileReplacements = 0;
            
            foreach ($replacements as $search => $replace) {
                $count = 0;
                $content = str_replace($search, $replace, $content, $count);
                $fileReplacements += $count;
            }
            
            if ($fileReplacements > 0) {
                file_put_contents($filepath, $content);
                $relPath = str_replace('\\', '/', $filepath);
                echo "✅ {$relPath}: {$fileReplacements} replacements\n";
                $totalReplacements += $fileReplacements;
                $filesModified++;
            }
        }
    }
}

echo "\n==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n";
echo "Files modified: {$filesModified}\n";
echo "Total replacements: {$totalReplacements}\n\n";

echo "⚠️  IMPORTANT:\n";
echo "1. Some view references may need manual review\n";
echo "2. Test the application thoroughly\n";
echo "3. Clear view cache: php artisan view:clear\n\n";
