<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['users', 'plant_types', 'plants', 'planting_locations', 'plantings', 'harvests'];

foreach ($tables as $table) {
    echo "\n=== Table: {$table} ===\n";
    $columns = DB::select("DESCRIBE {$table}");
    foreach ($columns as $col) {
        echo "{$col->Field} ({$col->Type})\n";
    }
}
