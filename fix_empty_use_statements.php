<?php

/**
 * Script untuk menghapus empty use statements dari semua models
 */

$modelsPath = __DIR__ . '/app/Models';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modelsPath),
    RecursiveIteratorIterator::SELF_FIRST
);

$stats = [
    'files_checked' => 0,
    'files_fixed' => 0,
];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Remove empty use statements
        $content = preg_replace('/^\s*use\s*;\s*$/m', '', $content);
        
        // Remove multiple consecutive empty lines
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        $stats['files_checked']++;
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $stats['files_fixed']++;
            $relativePath = str_replace(__DIR__ . '/', '', $filePath);
            echo "✓ Fixed: $relativePath\n";
        }
    }
}

echo "\n";
echo "========================================\n";
echo "Summary:\n";
echo "Files checked: {$stats['files_checked']}\n";
echo "Files fixed: {$stats['files_fixed']}\n";
echo "========================================\n";
