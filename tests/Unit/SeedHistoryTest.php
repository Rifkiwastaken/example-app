<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SeedHistory;
use App\Models\InventoryTypeSeed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model SeedHistory
 * 
 * Test ini menguji semua method dan relasi yang ada di model SeedHistory
 */
class SeedHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat seed history baru dengan field sesuai input
     * 
     * Menguji bahwa seed history dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_seed_history_with_all_fields(): void
    {
        // Menyiapkan data inventory type seed dan user
        $inventoryType = \App\Models\InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $seed = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 1000,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        // Menyiapkan data history untuk diuji
        $historyData = [
            'inventory_type_seed_id' => $seed->id,
            'action' => 'update',
            'description' => 'Update quantity dari 1000 menjadi 1500',
            'old_data' => ['total_seed_quantity' => 1000],
            'new_data' => ['total_seed_quantity' => 1500],
            'user_id' => $user->id,
        ];

        // Membuat seed history baru
        $history = SeedHistory::create($historyData);

        // Memverifikasi bahwa seed history berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('seed_histories', [
            'id' => $history->id,
            'inventory_type_seed_id' => $seed->id,
            'action' => 'update',
        ]);
    }

    /**
     * Test: Relasi seed mengembalikan inventory type seed yang memiliki history ini
     * 
     * Menguji bahwa relasi belongs-to antara seed history dan inventory type seed berfungsi
     */
    public function test_seed_relationship(): void
    {
        // Membuat seed dan history
        $inventoryType = \App\Models\InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $seed = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 1000,
        ]);

        $history = SeedHistory::create([
            'inventory_type_seed_id' => $seed->id,
            'action' => 'update',
            'description' => 'Update quantity',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($seed->id, $history->seed->id);
    }

    /**
     * Test: Relasi user mengembalikan user yang membuat history ini
     * 
     * Menguji bahwa relasi belongs-to antara seed history dan user berfungsi
     */
    public function test_user_relationship(): void
    {
        // Membuat user dan history
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        $inventoryType = \App\Models\InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $seed = InventoryTypeSeed::create([
            'inventory_type_id' => $inventoryType->id,
            'total_seed_quantity' => 1000,
        ]);

        $history = SeedHistory::create([
            'inventory_type_seed_id' => $seed->id,
            'action' => 'update',
            'description' => 'Update quantity',
            'user_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $history->user->id);
    }
}








