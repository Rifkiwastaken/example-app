<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InventoryTypeSeed;
use App\Models\InventoryType;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\User;
use App\Models\SeedHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model InventoryTypeSeed
 * 
 * Test ini menguji semua method dan relasi yang ada di model InventoryTypeSeed
 */
class InventoryTypeSeedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat inventory type seed baru dengan field sesuai input
     * 
     * Menguji bahwa inventory type seed dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_inventory_type_seed_with_all_fields(): void
    {
        // Menyiapkan data inventory type, plant, dan planting location
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        // Menyiapkan data seed untuk diuji
        $seedData = [
            'inventory_type_id' => $inventoryType->id,
            'plant_id' => $plant->id,
            'planting_location_id' => $plantingLocation->id,
            'total_seed_quantity' => 1000,
            'estimated_sale_price_per_kg' => 50000,
        ];

        // Membuat inventory type seed baru
        $seed = InventoryTypeSeed::create($seedData);

        // Memverifikasi bahwa inventory type seed berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('inventory_type_seeds', [
            'id' => $seed->id,
            'inventory_type_id' => $inventoryType->id,
            'plant_id' => $plant->id,
            'total_seed_quantity' => 1000,
        ]);
    }

    /**
     * Test: Relasi inventoryType mengembalikan inventory type yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara inventory type seed dan inventory type berfungsi
     */
    public function test_inventory_type_relationship(): void
    {
        // Membuat inventory type dan seed
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $seed = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 1000,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryType->id, $seed->inventoryType->id);
    }

    /**
     * Test: Relasi histories mengembalikan semua history untuk seed ini
     * 
     * Menguji bahwa relasi one-to-many antara inventory type seed dan seed history berfungsi
     */
    public function test_histories_relationship(): void
    {
        // Membuat seed dan beberapa histories
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $seed = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 1000,
        ]);

        $history1 = SeedHistory::create([
            'inventory_type_seed_id' => $seed->id,
            'action' => 'update',
            'description' => 'Update quantity',
        ]);

        $history2 = SeedHistory::create([
            'inventory_type_seed_id' => $seed->id,
            'action' => 'delete',
            'description' => 'Delete seed',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($seed->histories->contains($history1));
        $this->assertTrue($seed->histories->contains($history2));
        $this->assertEquals(2, $seed->histories->count());
    }
}








