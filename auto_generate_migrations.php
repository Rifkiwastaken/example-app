<?php
/**
 * Auto-generate migration files berdasarkan database structure analysis
 */

// Load data
$analysis = json_decode(file_get_contents('database_structure_analysis.json'), true);
$plan = json_decode(file_get_contents('migration_plan.json'), true);

// Create directories if not exist
$dirs = [
    'database/migrations/phase_1_correct',
    'database/migrations/phase_2_correct',
    'database/migrations/phase_3_correct',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

echo "==========================================\n";
echo "AUTO-GENERATING MIGRATION FILES\n";
echo "==========================================\n\n";

// Group tables by level for phase 1
$levelGroups = [];
foreach ($plan as $tableName => $tableData) {
    $level = $tableData['level'];
    if (!isset($levelGroups[$level])) {
        $levelGroups[$level] = [];
    }
    $levelGroups[$level][] = $tableName;
}

// Generate Phase 1 migrations (one file per level)
echo "Generating Phase 1 migrations...\n";
foreach ($levelGroups as $level => $tables) {
    $filename = sprintf(
        'database/migrations/phase_1_correct/2026_02_10_%03d_phase1_add_custom_id_level_%d.php',
        $level + 1,
        $level
    );
    
    $content = generatePhase1Migration($tables, $plan, $analysis, $level);
    file_put_contents($filename, $content);
    echo "  ✓ Created: " . basename($filename) . " (" . count($tables) . " tables)\n";
}

echo "\n✓ Phase 1 migrations generated!\n";
echo "\nNext: Review the generated files in database/migrations/phase_1_correct/\n";

// Helper function to generate Phase 1 migration
function generatePhase1Migration($tables, $plan, $analysis, $level) {
    $upCode = '';
    $downCode = '';
    
    foreach ($tables as $tableName) {
        $tableData = $plan[$tableName];
        $customIdColumn = $tableData['custom_id'];
        
        // UP: Add custom ID column
        $upCode .= "        // " . strtoupper($tableName) . " TABLE\n";
        $upCode .= "        if (Schema::hasTable('{$tableName}') && !Schema::hasColumn('{$tableName}', '{$customIdColumn}')) {\n";
        $upCode .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        
        // Generate short index name (max 64 chars for MySQL)
        $indexName = substr($tableName, 0, 20) . '_' . substr($customIdColumn, 0, 20) . '_unq';
        $upCode .= "                \$table->string('{$customIdColumn}', 36)->nullable()->unique('{$indexName}')->after('id');\n";
        
        // Add new_ columns for FK
        foreach ($tableData['fk_columns'] as $fk) {
            $upCode .= "                \$table->string('{$fk['new']}', 36)->nullable()->after('{$fk['old']}');\n";
        }
        
        $upCode .= "            });\n";
        $upCode .= "        }\n\n";
        
        // DOWN: Drop columns
        $columnsToDrop = [$customIdColumn];
        foreach ($tableData['fk_columns'] as $fk) {
            $columnsToDrop[] = $fk['new'];
        }
        
        $downCode .= "        if (Schema::hasTable('{$tableName}')) {\n";
        $downCode .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        $downCode .= "                \$table->dropColumn(['" . implode("', '", $columnsToDrop) . "']);\n";
        $downCode .= "            });\n";
        $downCode .= "        }\n\n";
    }
    
    $tableList = implode(', ', $tables);
    
    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1 - Level {$level}: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tables: {$tableList}
     */
    public function up(): void
    {
{$upCode}    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
{$downCode}    }
};
PHP;
}

echo "\nDone!\n";
