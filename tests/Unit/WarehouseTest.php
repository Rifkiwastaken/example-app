<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Bin;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model Warehouse
 * 
 * Test ini menguji semua method dan relasi yang ada di model Warehouse
 */
class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat warehouse baru dengan field sesuai input
     * 
     * Menguji bahwa warehouse dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_warehouse_with_all_fields(): void
    {
        // Menyiapkan data user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        // Menyiapkan data warehouse untuk diuji
        $warehouseData = [
            'name' => 'Gudang Utama',
            'internal_id' => 'WH-001',
            'tracking_type' => 'bin_separated',
            'description' => 'Gudang utama untuk penyimpanan benih',
            'responsible_person_id' => $user->id,
        ];

        // Membuat warehouse baru
        $warehouse = Warehouse::create($warehouseData);

        // Memverifikasi bahwa warehouse berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Gudang Utama',
            'internal_id' => 'WH-001',
            'tracking_type' => 'bin_separated',
        ]);
    }

    /**
     * Test: Relasi bins mengembalikan semua bin dalam warehouse
     * 
     * Menguji bahwa relasi one-to-many antara warehouse dan bin berfungsi
     */
    public function test_bins_relationship(): void
    {
        // Membuat warehouse dan beberapa bins
        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);

        $bin1 = Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin A',
            'max_capacity' => 1000,
            'capacity_unit' => 'kg',
        ]);

        $bin2 = Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin B',
            'max_capacity' => 2000,
            'capacity_unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($warehouse->bins->contains($bin1));
        $this->assertTrue($warehouse->bins->contains($bin2));
        $this->assertEquals(2, $warehouse->bins->count());
    }

    /**
     * Test: Relasi responsiblePerson mengembalikan user yang bertanggung jawab
     * 
     * Menguji bahwa relasi belongs-to antara warehouse dan user berfungsi
     */
    public function test_responsible_person_relationship(): void
    {
        // Membuat user dan warehouse
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
            'responsible_person_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $warehouse->responsiblePerson->id);
    }

    /**
     * Test: Method getTrackingTypeLabel mengembalikan label tipe tracking dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap tipe tracking memiliki label yang sesuai
     */
    public function test_get_tracking_type_label_returns_indonesian_label(): void
    {
        // Membuat warehouse dengan tracking type bin_separated
        $warehouse1 = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);
        $this->assertEquals('Di dalam bin terpisah', $warehouse1->tracking_type_label);

        // Membuat warehouse dengan tracking type warehouse_only
        $warehouse2 = Warehouse::create([
            'name' => 'Gudang Kedua',
            'tracking_type' => 'warehouse_only',
        ]);
        $this->assertEquals('Hanya di lokasi ini', $warehouse2->tracking_type_label);
    }

    /**
     * Test: Method getBinsCount mengembalikan jumlah bin dalam warehouse
     * 
     * Menguji bahwa method dapat menghitung jumlah bin dengan benar
     */
    public function test_get_bins_count_returns_number_of_bins(): void
    {
        // Membuat warehouse dan beberapa bins
        $warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'tracking_type' => 'bin_separated',
        ]);

        Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin A',
            'max_capacity' => 1000,
            'capacity_unit' => 'kg',
        ]);

        Bin::create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Bin B',
            'max_capacity' => 2000,
            'capacity_unit' => 'kg',
        ]);

        // Memverifikasi bahwa bins count adalah 2
        $this->assertEquals(2, $warehouse->bins_count);
    }
}








