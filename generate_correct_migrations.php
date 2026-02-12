<?php
/**
 * Script untuk generate migrasi yang benar berdasarkan analisis database
 */

require __DIR__.'/vendor/autoload.php';

// Load analysis
$analysis = json_decode(file_get_contents('database_structure_analysis.json'), true);

// Prefix mapping
$prefixMap = [
    'users' => 'USR',
    'plant_types' => 'PTY',
    'plants' => 'PLT',
    'planting_locations' => 'LOC',
    'plantings' => 'PLN',
    'harvests' => 'HRV',
    'certifications' => 'CRT',
    'certification_reports' => 'CRP',
    'warehouses' => 'WHS',
    'bins' => 'BIN',
    'inventory_types' => 'INV',
    'inventory_lots' => 'LOT',
    'inventory_transactions' => 'TRX',
    'inventory_type_warehouses' => 'ITW',
    'inventory_type_seeds' => 'ITS',
    'inventory_type_certification_reports' => 'ICR',
    'inventory_notes' => 'INN',
    'inventory_photos' => 'INP',
    'sales' => 'SAL',
    'sale_items' => 'SIT',
    'tasks' => 'TSK',
    'task_series' => 'TSR',
    'task_templates' => 'TTP',
    'expenses' => 'EXP',
    'attachments' => 'ATT',
    'seed_histories' => 'SDH',
    'planting_losses' => 'PLS',
    'plant_notes' => 'PLN',
    'plant_photos' => 'PHP',
    'planting_location_notes' => 'LCN',
    'planting_location_photos' => 'LCP',
    'user_planting_location_land_manager' => 'ULM',
    'user_planting_location_land_worker' => 'ULW',
    'nutrients' => 'NTR',
    'treatments' => 'TRT',
    'landing_page_settings' => 'LPS',
];

// Get singular form
function getSingular($tableName) {
    // Manual mapping for special cases
    $specialCases = [
        'warehouses' => 'warehouse',
        'task_series' => 'task_series', // already singular
        'expenses' => 'expense',
        'inventories' => 'inventory',
        'certifications' => 'certification',
        'certifications_reports' => 'certification_report',
    ];
    
    if (isset($specialCases[$tableName])) {
        return $specialCases[$tableName];
    }
    
    // Simple singularization
    if (substr($tableName, -3) === 'ies') {
        return substr($tableName, 0, -3) . 'y';
    }
    if (substr($tableName, -3) === 'ses') {
        return substr($tableName, 0, -2);
    }
    if (substr($tableName, -1) === 's') {
        return substr($tableName, 0, -1);
    }
    return $tableName;
}

echo "==========================================\n";
echo "GENERATING CORRECT MIGRATIONS\n";
echo "==========================================\n\n";

// Group tables by dependency level
$levels = [
    0 => ['users', 'plant_types', 'warehouses', 'task_templates', 'landing_page_settings'],
    1 => ['plants', 'planting_locations', 'bins', 'task_series'],
    2 => ['plantings', 'inventory_types', 'certifications'],
    3 => ['harvests', 'inventory_lots', 'certification_reports', 'inventory_type_seeds', 'tasks', 'sales'],
    4 => ['inventory_transactions', 'inventory_type_warehouses', 'inventory_type_certification_reports', 
          'sale_items', 'expenses', 'nutrients', 'treatments', 'attachments', 'seed_histories',
          'planting_losses', 'plant_notes', 'plant_photos', 'planting_location_notes', 
          'planting_location_photos', 'inventory_notes', 'inventory_photos',
          'user_planting_location_land_manager', 'user_planting_location_land_worker'],
];

$migrationContent = [];

foreach ($levels as $level => $tables) {
    echo "Processing Level {$level}...\n";
    
    foreach ($tables as $tableName) {
        if (!isset($analysis[$tableName])) continue;
        
        $data = $analysis[$tableName];
        $singular = getSingular($tableName);
        $customIdColumn = $singular . '_id';
        $prefix = $prefixMap[$tableName] ?? strtoupper(substr($tableName, 0, 3));
        
        echo "  - {$tableName} → {$customIdColumn} (Prefix: {$prefix})\n";
        
        // Check which FK columns need new_ versions
        $fkColumns = [];
        foreach ($data['foreign_keys'] as $fk) {
            $refTable = $fk['references_table'];
            $refSingular = getSingular($refTable);
            $refCustomId = $refSingular . '_id';
            
            $fkColumns[] = [
                'old' => $fk['column'],
                'new' => 'new_' . $fk['column'],
                'ref_table' => $refTable,
                'ref_column' => $refCustomId,
            ];
        }
        
        $migrationContent[$tableName] = [
            'custom_id' => $customIdColumn,
            'prefix' => $prefix,
            'fk_columns' => $fkColumns,
            'level' => $level,
        ];
    }
}

// Save migration plan
file_put_contents('migration_plan.json', json_encode($migrationContent, JSON_PRETTY_PRINT));

echo "\n✓ Migration plan saved to: migration_plan.json\n";
echo "\nTotal tables to migrate: " . count($migrationContent) . "\n";
echo "\nDone!\n";
