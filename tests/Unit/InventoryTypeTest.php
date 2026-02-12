<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InventoryType;
use App\Models\User;
use App\Models\Plant;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use App\Models\InventoryTypeSeed;
use App\Models\InventoryNote;
use App\Models\InventoryPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model InventoryType
 * 
 * Test ini menguji semua method dan relasi yang ada di model InventoryType
 */
class InventoryTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat inventory type baru dengan field sesuai input
     * 
     * Menguji bahwa inventory type dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_inventory_type_with_all_fields(): void
    {
        // Menyiapkan data user dan plant
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        // Menyiapkan data inventory type untuk diuji
        $inventoryTypeData = [
            'category' => 'Benih',
            'name' => 'Benih Padi',
            'sku' => 'BP-001',
            'unit' => 'kg',
            'estimated_value_per_unit' => 50000,
            'track_individual_lots' => true,
            'responsible_person_id' => $user->id,
            'plant_id' => $plant->id,
        ];

        // Membuat inventory type baru
        $inventoryType = InventoryType::create($inventoryTypeData);

        // Memverifikasi bahwa inventory type berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('inventory_types', [
            'id' => $inventoryType->id,
            'name' => 'Benih Padi',
            'sku' => 'BP-001',
            'category' => 'Benih',
        ]);
    }

    /**
     * Test: Relasi lots mengembalikan semua lot untuk inventory type ini
     * 
     * Menguji bahwa relasi one-to-many antara inventory type dan inventory lot berfungsi
     */
    public function test_lots_relationship(): void
    {
        // Membuat inventory type dan beberapa lots
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $lot1 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'initial_stock' => 100,
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);

        $lot2 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'initial_stock' => 200,
            'current_stock' => 200,
            'stock_unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($inventoryType->lots->contains($lot1));
        $this->assertTrue($inventoryType->lots->contains($lot2));
        $this->assertEquals(2, $inventoryType->lots->count());
    }

    /**
     * Test: Relasi transactions mengembalikan semua transaksi untuk inventory type ini
     * 
     * Menguji bahwa relasi one-to-many antara inventory type dan inventory transaction berfungsi
     */
    public function test_transactions_relationship(): void
    {
        // Membuat inventory type dan beberapa transactions
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $transaction1 = InventoryTransaction::create([
            'inventory_type_id' => $inventoryType->id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
        ]);

        $transaction2 = InventoryTransaction::create([
            'inventory_type_id' => $inventoryType->id,
            'transaction_type' => 'stok_keluar',
            'quantity' => 50,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($inventoryType->transactions->contains($transaction1));
        $this->assertTrue($inventoryType->transactions->contains($transaction2));
        $this->assertEquals(2, $inventoryType->transactions->count());
    }

    /**
     * Test: Relasi seeds mengembalikan semua seed record untuk inventory type ini
     * 
     * Menguji bahwa relasi one-to-many antara inventory type dan inventory type seed berfungsi
     */
    public function test_seeds_relationship(): void
    {
        // Membuat inventory type dan beberapa seeds
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $seed1 = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 100,
        ]);

        $seed2 = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 200,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($inventoryType->seeds->contains($seed1));
        $this->assertTrue($inventoryType->seeds->contains($seed2));
        $this->assertEquals(2, $inventoryType->seeds->count());
    }

    /**
     * Test: Relasi responsiblePerson mengembalikan user yang bertanggung jawab
     * 
     * Menguji bahwa relasi belongs-to antara inventory type dan user berfungsi
     */
    public function test_responsible_person_relationship(): void
    {
        // Membuat user dan inventory type
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
            'responsible_person_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $inventoryType->responsiblePerson->id);
    }

    /**
     * Test: Relasi plant mengembalikan plant yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara inventory type dan plant berfungsi
     */
    public function test_plant_relationship(): void
    {
        // Membuat plant dan inventory type
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
            'plant_id' => $plant->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plant->id, $inventoryType->plant->id);
    }

    /**
     * Test: Method getTotalStock mengembalikan total stok dari lots dan seeds
     * 
     * Menguji bahwa method dapat menghitung total stok dengan benar
     */
    public function test_get_total_stock_returns_sum_of_lots_and_seeds(): void
    {
        // Membuat inventory type dengan lots dan seeds
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat lots dengan total stok 300
        InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);

        InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'current_stock' => 200,
            'stock_unit' => 'kg',
        ]);

        // Membuat seeds dengan total stok 150
        InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 50,
        ]);

        InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 100,
        ]);

        // Memverifikasi bahwa total stock adalah 450 (300 + 150)
        $this->assertEquals(450, $inventoryType->total_stock);
    }

    /**
     * Test: Method getTotalStockFromSeeds mengembalikan total stok dari seeds saja
     * 
     * Menguji bahwa method dapat menghitung total stok dari seeds dengan benar
     */
    public function test_get_total_stock_from_seeds_returns_sum_of_seeds_only(): void
    {
        // Membuat inventory type dengan seeds
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat seeds dengan total stok 250
        InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 100,
        ]);

        InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 150,
        ]);

        // Memverifikasi bahwa total stock from seeds adalah 250
        $this->assertEquals(250, $inventoryType->total_stock_from_seeds);
    }

    /**
     * Test: Method getTotalValue mengembalikan total nilai inventory
     * 
     * Menguji bahwa method dapat menghitung total nilai dengan benar
     */
    public function test_get_total_value_returns_correct_value(): void
    {
        // Membuat inventory type dengan estimated value
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
            'estimated_value_per_unit' => 50000,
        ]);

        // Membuat lots dengan total stok 300
        InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'current_stock' => 300,
            'stock_unit' => 'kg',
        ]);

        // Memverifikasi bahwa total value adalah 15.000.000 (300 * 50000)
        $this->assertEquals(15000000, $inventoryType->total_value);
    }

    /**
     * Test: Method getTotalValue mengembalikan 0 jika estimated_value_per_unit tidak ada
     * 
     * Menguji bahwa method mengembalikan 0 ketika estimated value tidak diisi
     */
    public function test_get_total_value_returns_zero_when_no_estimated_value(): void
    {
        // Membuat inventory type tanpa estimated value
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat lots dengan total stok 300
        InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'current_stock' => 300,
            'stock_unit' => 'kg',
        ]);

        // Memverifikasi bahwa total value adalah 0
        $this->assertEquals(0, $inventoryType->total_value);
    }
}








