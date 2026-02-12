<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InventoryLot;
use App\Models\InventoryType;
use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\Certification;
use App\Models\InventoryTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model InventoryLot
 * 
 * Test ini menguji semua method dan relasi yang ada di model InventoryLot
 */
class InventoryLotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat inventory lot baru dengan field sesuai input
     * 
     * Menguji bahwa inventory lot dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_inventory_lot_with_all_fields(): void
    {
        // Menyiapkan data inventory type
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Menyiapkan data inventory lot untuk diuji
        $inventoryLotData = [
            'inventory_type_id' => $inventoryType->id,
            'production_id' => 'PROD-001',
            'expiry_date' => Carbon::now()->addMonths(6),
            'status' => 'tersedia',
            'initial_stock' => 100,
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ];

        // Membuat inventory lot baru
        $inventoryLot = InventoryLot::create($inventoryLotData);

        // Memverifikasi bahwa inventory lot berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('inventory_lots', [
            'id' => $inventoryLot->id,
            'production_id' => 'PROD-001',
            'status' => 'tersedia',
            'initial_stock' => 100,
        ]);
    }

    /**
     * Test: Relasi inventoryType mengembalikan inventory type yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara inventory lot dan inventory type berfungsi
     */
    public function test_inventory_type_relationship(): void
    {
        // Membuat inventory type dan inventory lot
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $inventoryLot = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'initial_stock' => 100,
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryType->id, $inventoryLot->inventoryType->id);
    }

    /**
     * Test: Method updateStatus mengupdate status berdasarkan expiry date dan stock
     * 
     * Menguji bahwa method dapat mengupdate status dengan benar berdasarkan kondisi
     */
    public function test_update_status_updates_status_based_on_expiry_and_stock(): void
    {
        // Membuat inventory lot yang sudah kadaluarsa
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $expiredLot = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'expiry_date' => Carbon::now()->subDays(10),
            'current_stock' => 50,
            'stock_unit' => 'kg',
        ]);

        // Memanggil method updateStatus
        $expiredLot->updateStatus();

        // Memverifikasi bahwa status diupdate menjadi kadaluarsa
        $this->assertEquals('kadaluarsa', $expiredLot->fresh()->status);

        // Membuat inventory lot yang akan segera kadaluarsa (dalam 30 hari)
        $expiringSoonLot = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'expiry_date' => Carbon::now()->addDays(20),
            'current_stock' => 50,
            'stock_unit' => 'kg',
        ]);

        // Memanggil method updateStatus
        $expiringSoonLot->updateStatus();

        // Memverifikasi bahwa status diupdate menjadi segera_kadaluarsa
        $this->assertEquals('segera_kadaluarsa', $expiringSoonLot->fresh()->status);

        // Membuat inventory lot yang masih tersedia
        $availableLot = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'expiry_date' => Carbon::now()->addMonths(6),
            'current_stock' => 50,
            'stock_unit' => 'kg',
        ]);

        // Memanggil method updateStatus
        $availableLot->updateStatus();

        // Memverifikasi bahwa status diupdate menjadi tersedia
        $this->assertEquals('tersedia', $availableLot->fresh()->status);

        // Membuat inventory lot yang habis
        $emptyLot = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'expiry_date' => Carbon::now()->addMonths(6),
            'current_stock' => 0,
            'stock_unit' => 'kg',
        ]);

        // Memanggil method updateStatus
        $emptyLot->updateStatus();

        // Memverifikasi bahwa status diupdate menjadi habis
        $this->assertEquals('habis', $emptyLot->fresh()->status);
    }

    /**
     * Test: Method getStatusLabel mengembalikan label status dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap status memiliki label yang sesuai
     */
    public function test_get_status_label_returns_indonesian_label(): void
    {
        // Membuat inventory type
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat inventory lot dengan status tersedia
        $lot1 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'tersedia',
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('Tersedia', $lot1->status_label);

        // Membuat inventory lot dengan status segera_kadaluarsa
        $lot2 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'segera_kadaluarsa',
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('Segera Kadaluarsa', $lot2->status_label);

        // Membuat inventory lot dengan status kadaluarsa
        $lot3 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'kadaluarsa',
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('KADALUARSA', $lot3->status_label);

        // Membuat inventory lot dengan status habis
        $lot4 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'habis',
            'current_stock' => 0,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('Habis', $lot4->status_label);
    }

    /**
     * Test: Method getStatusColor mengembalikan warna badge untuk status
     * 
     * Menguji bahwa setiap status memiliki warna badge yang sesuai
     */
    public function test_get_status_color_returns_color_for_status(): void
    {
        // Membuat inventory type
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Membuat inventory lot dengan status tersedia
        $lot1 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'tersedia',
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('success', $lot1->status_color);

        // Membuat inventory lot dengan status segera_kadaluarsa
        $lot2 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'segera_kadaluarsa',
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('warning', $lot2->status_color);

        // Membuat inventory lot dengan status kadaluarsa
        $lot3 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'kadaluarsa',
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('danger', $lot3->status_color);

        // Membuat inventory lot dengan status habis
        $lot4 = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'status' => 'habis',
            'current_stock' => 0,
            'stock_unit' => 'kg',
        ]);
        $this->assertEquals('secondary', $lot4->status_color);
    }
}








