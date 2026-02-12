<?php
/**
 * Find all references to old 'id' column in controllers and models
 */

$directories = [
    'app/Http/Controllers',
    'app/Models',
    'resources/views',
];

$patterns = [
    '/->id\b/',                          // ->id
    '/\[\'id\'\]/',                      // ['id']
    '/\["id"\]/',                        // ["id"]
    '/where\(\'id\'/',                   // where('id'
    '/where\("id"/',                     // where("id"
    '/whereIn\(\'id\'/',                 // whereIn('id'
    '/whereIn\("id"/',                   // whereIn("id"
    '/pluck\(\'id\'\)/',                 // pluck('id')
    '/pluck\("id"\)/',                   // pluck("id")
];

echo "==========================================\n";
echo "FINDING OLD 'id' REFERENCES\n";
echo "==========================================\n\n";

$findings = [];

foreach ($directories as $dir) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filepath = $file->getPathname();
            $content = file_get_contents($filepath);
            $lines = explode("\n", $content);
            
            foreach ($lines as $lineNum => $line) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        // Skip if it's a comment
                        if (preg_match('/^\s*\/\//', $line) || preg_match('/^\s*\*/', $line)) {
                            continue;
                        }
                        
                        // Skip if it's in a string that's not a column reference
                        if (preg_match('/[\'"].*id.*[\'"]/', $line) && 
                            !preg_match('/(where|pluck|whereIn|select)/', $line)) {
                            continue;
                        }
                        
                        $findings[] = [
                            'file' => str_replace('\\', '/', $filepath),
                            'line' => $lineNum + 1,
                            'code' => trim($line),
                        ];
                        break; // Only report once per line
                    }
                }
            }
        }
    }
}

if (empty($findings)) {
    echo "✅ No old 'id' references found!\n\n";
} else {
    echo "Found " . count($findings) . " potential issues:\n\n";
    
    $byFile = [];
    foreach ($findings as $finding) {
        $byFile[$finding['file']][] = $finding;
    }
    
    foreach ($byFile as $file => $issues) {
        echo "📄 " . $file . " (" . count($issues) . " issues)\n";
        foreach ($issues as $issue) {
            echo "   Line {$issue['line']}: {$issue['code']}\n";
        }
        echo "\n";
    }
    
    // Save to file
    file_put_contents('old_id_references.json', json_encode($findings, JSON_PRETTY_PRINT));
    echo "Results saved to: old_id_references.json\n\n";
}

echo "==========================================\n";
echo "RECOMMENDATIONS\n";
echo "==========================================\n\n";
echo "1. Review each finding manually\n";
echo "2. Replace with appropriate custom ID column:\n";
echo "   - \$model->id → \$model->model_id\n";
echo "   - where('id', ...) → where('model_id', ...)\n";
echo "   - whereIn('id', ...) → whereIn('model_id', ...)\n";
echo "   - pluck('id') → pluck('model_id')\n";
echo "3. Test thoroughly after changes\n\n";
