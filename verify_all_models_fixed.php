<?php
/**
 * Verify all models have been properly fixed
 * This script checks if all relationships use explicit foreign keys
 */

echo "==========================================\n";
echo "VERIFY ALL MODELS FIXED\n";
echo "==========================================\n\n";

$modelsToCheck = [
    'User', 'Plant', 'PlantType', 'Planting', 'PlantingLocation',
    'Harvest', 'Certification', 'CertificationReport', 'Warehouse',
    'Bin', 'InventoryType', 'InventoryTypeSeed', 'InventoryLot',
    'InventoryTransaction', 'Sale', 'SaleItem', 'Task', 'TaskSeries',
    'Expense', 'Nutrient', 'Treatment', 'Attachment', 'SeedHistory',
    'PlantingLoss', 'PlantNote', 'PlantPhoto', 'PlantingLocationNote',
    'PlantingLocationPhoto', 'InventoryNote', 'InventoryPhoto', 'Location'
];

$results = [
    'has_trait' => [],
    'missing_trait' => [],
    'has_relationships' => [],
    'needs_review' => []
];

foreach ($modelsToCheck as $modelName) {
    $filePath = "app/Models/{$modelName}.php";
    
    if (!file_exists($filePath)) {
        echo "⚠️  {$modelName}: File not found\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Check for HasCustomId trait
    $hasTrait = strpos($content, 'use HasCustomId') !== false;
    
    // Check for relationship methods
    $hasRelationships = preg_match('/public function \w+\(\).*?:(HasMany|BelongsTo|BelongsToMany)/s', $content);
    
    // Check if relationships have explicit keys (3 parameters for belongsTo, hasMany)
    $hasExplicitKeys = preg_match('/return \$this->(belongsTo|hasMany|belongsToMany)\([^)]+,\s*\'[^\']+\',\s*\'[^\']+\'/s', $content);
    
    if ($hasTrait) {
        $results['has_trait'][] = $modelName;
    } else {
        $results['missing_trait'][] = $modelName;
    }
    
    if ($hasRelationships) {
        $results['has_relationships'][] = $modelName;
        
        if (!$hasExplicitKeys) {
            $results['needs_review'][] = $modelName;
        }
    }
    
    // Display status
    $traitStatus = $hasTrait ? '✅' : '❌';
    $relStatus = $hasRelationships ? ($hasExplicitKeys ? '✅' : '⚠️') : '➖';
    
    echo "{$traitStatus} {$relStatus} {$modelName}\n";
}

echo "\n==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n\n";

echo "✅ Models with HasCustomId trait: " . count($results['has_trait']) . "\n";
echo "❌ Models missing HasCustomId trait: " . count($results['missing_trait']) . "\n";
echo "📊 Models with relationships: " . count($results['has_relationships']) . "\n";
echo "⚠️  Models needing review: " . count($results['needs_review']) . "\n";

if (!empty($results['missing_trait'])) {
    echo "\n⚠️  Missing HasCustomId trait:\n";
    foreach ($results['missing_trait'] as $model) {
        echo "   - {$model}\n";
    }
}

if (!empty($results['needs_review'])) {
    echo "\n⚠️  Needs manual review (relationships may not have explicit keys):\n";
    foreach ($results['needs_review'] as $model) {
        echo "   - {$model}\n";
    }
}

echo "\n==========================================\n";
echo "LEGEND\n";
echo "==========================================\n";
echo "✅ = Has trait / Relationships fixed\n";
echo "❌ = Missing trait\n";
echo "⚠️  = Has relationships but may need review\n";
echo "➖ = No relationships found\n";

echo "\n==========================================\n";
if (count($results['missing_trait']) === 0 && count($results['needs_review']) === 0) {
    echo "✅ ALL MODELS PROPERLY CONFIGURED!\n";
} else {
    echo "⚠️  SOME MODELS NEED ATTENTION\n";
}
echo "==========================================\n\n";
