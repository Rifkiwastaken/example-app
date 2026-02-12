<?php
/**
 * Script untuk menganalisis struktur database lengkap
 * Akan mengidentifikasi semua tabel, kolom, dan foreign key relationships
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "DATABASE STRUCTURE ANALYSIS\n";
echo "==========================================\n\n";

$dbName = env('DB_DATABASE');
echo "Database: {$dbName}\n\n";

// Get all tables
$tables = DB::select("SHOW TABLES");
$tableKey = "Tables_in_{$dbName}";

$analysis = [];

foreach ($tables as $table) {
    $tableName = $table->$tableKey;
    
    // Skip system tables
    if (in_array($tableName, ['migrations', 'password_reset_tokens', 'failed_jobs', 'personal_access_tokens'])) {
        continue;
    }
    
    echo "Analyzing table: {$tableName}...\n";
    
    $analysis[$tableName] = [
        'columns' => [],
        'foreign_keys' => [],
        'has_id' => false,
        'has_timestamps' => false,
    ];
    
    // Get columns
    $columns = DB::select("DESCRIBE {$tableName}");
    foreach ($columns as $col) {
        $analysis[$tableName]['columns'][] = [
            'name' => $col->Field,
            'type' => $col->Type,
            'null' => $col->Null,
            'key' => $col->Key,
            'default' => $col->Default,
            'extra' => $col->Extra,
        ];
        
        if ($col->Field === 'id' && $col->Key === 'PRI') {
            $analysis[$tableName]['has_id'] = true;
        }
        
        if (in_array($col->Field, ['created_at', 'updated_at'])) {
            $analysis[$tableName]['has_timestamps'] = true;
        }
    }
    
    // Get foreign keys
    $fks = DB::select("
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            CONSTRAINT_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ", [$dbName, $tableName]);
    
    foreach ($fks as $fk) {
        $analysis[$tableName]['foreign_keys'][] = [
            'column' => $fk->COLUMN_NAME,
            'references_table' => $fk->REFERENCED_TABLE_NAME,
            'references_column' => $fk->REFERENCED_COLUMN_NAME,
            'constraint' => $fk->CONSTRAINT_NAME,
        ];
    }
}

echo "\n==========================================\n";
echo "ANALYSIS COMPLETE\n";
echo "==========================================\n\n";

// Save to JSON file
$jsonFile = 'database_structure_analysis.json';
file_put_contents($jsonFile, json_encode($analysis, JSON_PRETTY_PRINT));
echo "✓ Analysis saved to: {$jsonFile}\n\n";

// Generate summary report
echo "==========================================\n";
echo "SUMMARY REPORT\n";
echo "==========================================\n\n";

echo "Total tables analyzed: " . count($analysis) . "\n\n";

echo "Tables with BigInt ID (need migration):\n";
foreach ($analysis as $tableName => $data) {
    if ($data['has_id']) {
        echo "  - {$tableName}\n";
    }
}

echo "\n\nForeign Key Relationships:\n";
foreach ($analysis as $tableName => $data) {
    if (!empty($data['foreign_keys'])) {
        echo "\n{$tableName}:\n";
        foreach ($data['foreign_keys'] as $fk) {
            echo "  - {$fk['column']} → {$fk['references_table']}.{$fk['references_column']}\n";
        }
    }
}

echo "\n\n==========================================\n";
echo "DETAILED COLUMN ANALYSIS\n";
echo "==========================================\n\n";

// Identify columns that reference users
echo "Columns that reference users:\n";
foreach ($analysis as $tableName => $data) {
    $userColumns = [];
    foreach ($data['columns'] as $col) {
        if (preg_match('/(user_id|recorded_by|edited_by|assigned_to|responsible_person_id|created_by|updated_by)/i', $col['name'])) {
            $userColumns[] = $col['name'];
        }
    }
    if (!empty($userColumns)) {
        echo "  {$tableName}: " . implode(', ', $userColumns) . "\n";
    }
}

echo "\n\nDone!\n";
