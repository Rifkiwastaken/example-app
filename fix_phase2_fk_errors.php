<?php
/**
 * Fix Phase 2 FK Update Errors
 * Manually update the failed FK references
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "FIXING PHASE 2 FK UPDATE ERRORS\n";
echo "==========================================\n\n";

// Fix 1: certifications.new_harvest_id
echo "Fixing certifications.new_harvest_id...\n";
$fixed1 = DB::statement("
    UPDATE certifications child
    INNER JOIN harvests parent ON child.harvest_id = parent.id
    SET child.new_harvest_id = parent.harvest_id
    WHERE child.harvest_id IS NOT NULL
");
$count1 = DB::table('certifications')->whereNotNull('new_harvest_id')->count();
echo "  ✓ Updated {$count1} rows\n\n";

// Fix 2: expenses.new_nutrient_id
echo "Fixing expenses.new_nutrient_id...\n";
$fixed2 = DB::statement("
    UPDATE expenses child
    INNER JOIN nutrients parent ON child.nutrient_id = parent.id
    SET child.new_nutrient_id = parent.nutrient_id
    WHERE child.nutrient_id IS NOT NULL
");
$count2 = DB::table('expenses')->whereNotNull('new_nutrient_id')->count();
echo "  ✓ Updated {$count2} rows\n\n";

echo "==========================================\n";
echo "✅ FK ERRORS FIXED!\n";
echo "==========================================\n";
echo "Run verify_phase2_data.php again to confirm.\n\n";
