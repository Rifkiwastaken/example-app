<?php

/**
 * Find and fix ALL remaining ->references('id') in ALL migration files
 */

$migrationsPath = __DIR__ . '/database/migrations';

$files = glob($migrationsPath . '/*.php');
$stats = ['processed' => 0, 'modified' => 0, 'files' => []];

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip phase migrations
    if (strpos($filename, 'phase') !== false) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Check if file contains ->references('id')
    if (strpos($content, "->references('id')") !== false) {
        echo "Found ->references('id') in: $filename\n";
        $stats['files'][] = $filename;
    }
    
    $stats['processed']++;
}

echo "\n========================================\n";
echo "Scan Summary:\n";
echo "Processed: {$stats['processed']}\n";
echo "Files with ->references('id'): " . count($stats['files']) . "\n";
if (!empty($stats['files'])) {
    echo "\nFiles that need manual fixing:\n";
    foreach ($stats['files'] as $file) {
        echo "  - $file\n";
    }
}
echo "========================================\n";
