<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'plant_notes',
    'plant_photos', 
    'planting_location_notes',
    'planting_location_photos',
    'nutrients',
    'treatments'
];

foreach ($tables as $table) {
    if (DB::select("SHOW TABLES LIKE '{$table}'")) {
        echo "\n=== Table: {$table} ===\n";
        $columns = DB::select("DESCRIBE {$table}");
        foreach ($columns as $col) {
            echo "{$col->Field}\n";
        }
    } else {
        echo "\n=== Table: {$table} === NOT FOUND\n";
    }
}
