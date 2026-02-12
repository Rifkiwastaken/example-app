<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bin;
use App\Models\Warehouse;
use App\Models\InventoryLot;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model Bin
 * 
 * Test ini menguji semua method dan relasi yang ada di model Bin
 */
class BinTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat bin baru dengan field sesuai input
     * 
     * Menguji bahwa bin dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_bin_with_all_fields(): void
    {
        // Menyiapkan data warehouse
        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);

        // Menyiapkan data bin untuk diuji
        $binData = [
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin A',
            'internal_id' => 'BIN-001',
            'max_capacity' => 1000,
            'capacity_unit' => 'kg',
            'description' => 'Bin untuk penyimpanan benih padi',
        ];

        // Membuat bin baru
        $bin = Bin::create($binData);

        // Memverifikasi bahwa bin berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('bins', [
            'id' => $bin->id,
            'name' => 'Bin A',
            'internal_id' => 'BIN-001',
            'max_capacity' => 1000,
        ]);
    }

    /**
     * Test: Relasi warehouse mengembalikan warehouse yang memiliki bin ini
     * 
     * Menguji bahwa relasi belongs-to antara bin dan warehouse berfungsi
     */
    public function test_warehouse_relationship(): void
    {
        // Membuat warehouse dan bin
        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);

        $bin = Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin A',
            'max_capacity' => 1000,
            'capacity_unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($warehouse->id, $bin->warehouse->id);
    }

    /**
     * Test: Relasi inventoryLots mengembalikan semua inventory lot dalam bin ini
     * 
     * Menguji bahwa relasi one-to-many antara bin dan inventory lot berfungsi
     */
    public function test_inventory_lots_relationship(): void
    {
        // Membuat warehouse, bin, dan inventory type
        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);

        $bin = Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin A',
            'max_capacity' => 1000,
            'capacity_unit' => 'kg',
        ]);

        $inventoryType = \App\Models\InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat beberapa inventory lots
        $lot1 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'bin_id' => $bin->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);

        $lot2 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'bin_id' => $bin->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => 200,
            'stock_unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($bin->inventoryLots->contains($lot1));
        $this->assertTrue($bin->inventoryLots->contains($lot2));
        $this->assertEquals(2, $bin->inventoryLots->count());
    }

    /**
     * Test: Method getCurrentStock mengembalikan total stok dalam bin
     * 
     * Menguji bahwa method dapat menghitung total stok dengan benar
     */
    public function test_get_current_stock_returns_sum_of_lots(): void
    {
        // Membuat warehouse, bin, dan inventory type
        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);

        $bin = Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin A',
            'max_capacity' => 1000,
            'capacity_unit' => 'kg',
        ]);

        $inventoryType = \App\Models\InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat beberapa inventory lots dengan total stok 450
        InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'bin_id' => $bin->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => 150,
            'stock_unit' => 'kg',
        ]);

        InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'bin_id' => $bin->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => 300,
            'stock_unit' => 'kg',
        ]);

        // Memverifikasi bahwa current stock adalah 450
        $this->assertEquals(450, $bin->current_stock);
    }
}








