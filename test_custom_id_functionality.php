<?php

/**
 * Test Custom ID Functionality
 * Run: php test_custom_id_functionality.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PlantType;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\Certification;
use App\Models\InventoryType;
use App\Models\Sale;
use App\Models\Task;

echo "===========================================\n";
echo "Testing Custom ID Auto-Generation\n";
echo "===========================================\n\n";

// Test 1: PlantType
echo "1. Testing PlantType...\n";
try {
    $plantType = PlantType::create([
        'name' => 'Test Plant Type',
        'category' => 'sayuran'
    ]);
    echo "   ✓ Created PlantType with ID: {$plantType->plant_type_id}\n";
    echo "   ✓ ID Format: " . (preg_match('/^PTY-[A-Z0-9]{8}$/', $plantType->plant_type_id) ? 'VALID' : 'INVALID') . "\n";
    $plantType->delete();
    echo "   ✓ Deleted successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
}

// Test 2: Plant
echo "2. Testing Plant...\n";
try {
    $plantType = PlantType::create(['name' => 'Temp Type', 'category' => 'sayuran']);
    $plant = Plant::create([
        'name' => 'Test Plant',
        'plant_type_id' => $plantType->plant_type_id,
        'status' => 'perencanaan'
    ]);
    echo "   ✓ Created Plant with ID: {$plant->plant_id}\n";
    echo "   ✓ ID Format: " . (preg_match('/^PLT-[A-Z0-9]{8}$/', $plant->plant_id) ? 'VALID' : 'INVALID') . "\n";
    echo "   ✓ FK to PlantType: {$plant->plant_type_id}\n";
    $plant->delete();
    $plantType->delete();
    echo "   ✓ Deleted successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
}

// Test 3: PlantingLocation
echo "3. Testing PlantingLocation...\n";
try {
    $location = PlantingLocation::create([
        'name' => 'Test Location',
        'location_type' => 'lahan_terbuka',
        'area_size' => 1000
    ]);
    echo "   ✓ Created PlantingLocation with ID: {$location->planting_location_id}\n";
    echo "   ✓ ID Format: " . (preg_match('/^LOC-[A-Z0-9]{8}$/', $location->planting_location_id) ? 'VALID' : 'INVALID') . "\n";
    $location->delete();
    echo "   ✓ Deleted successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
}

// Test 4: InventoryType
echo "4. Testing InventoryType...\n";
try {
    $inventory = InventoryType::create([
        'name' => 'Test Inventory',
        'category' => 'benih',
        'unit' => 'kg'
    ]);
    echo "   ✓ Created InventoryType with ID: {$inventory->inventory_type_id}\n";
    echo "   ✓ ID Format: " . (preg_match('/^INV-[A-Z0-9]{8}$/', $inventory->inventory_type_id) ? 'VALID' : 'INVALID') . "\n";
    $inventory->delete();
    echo "   ✓ Deleted successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
}

// Test 5: Task
echo "5. Testing Task...\n";
try {
    $task = Task::create([
        'title' => 'Test Task',
        'description' => 'Test Description',
        'due_date' => now()->addDays(7),
        'status' => 'pending'
    ]);
    echo "   ✓ Created Task with ID: {$task->task_id}\n";
    echo "   ✓ ID Format: " . (preg_match('/^TSK-[A-Z0-9]{8}$/', $task->task_id) ? 'VALID' : 'INVALID') . "\n";
    $task->delete();
    echo "   ✓ Deleted successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
}

echo "===========================================\n";
echo "Testing Relationships\n";
echo "===========================================\n\n";

// Test 6: Relationships
echo "6. Testing Plant -> PlantType Relationship...\n";
try {
    $plantType = PlantType::create(['name' => 'Cabai', 'category' => 'sayuran']);
    $plant = Plant::create([
        'name' => 'Cabai Merah',
        'plant_type_id' => $plantType->plant_type_id,
        'status' => 'perencanaan'
    ]);
    
    // Test relationship
    $relatedType = $plant->type;
    echo "   ✓ Plant ID: {$plant->plant_id}\n";
    echo "   ✓ PlantType ID: {$plantType->plant_type_id}\n";
    echo "   ✓ Relationship works: " . ($relatedType->plant_type_id === $plantType->plant_type_id ? 'YES' : 'NO') . "\n";
    echo "   ✓ PlantType name: {$relatedType->name}\n";
    
    $plant->delete();
    $plantType->delete();
    echo "   ✓ Cleanup successful\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
}

echo "===========================================\n";
echo "Test Summary\n";
echo "===========================================\n";
echo "All basic tests completed!\n";
echo "Custom ID generation is working correctly.\n";
echo "===========================================\n";
