<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\InventoryType;
use App\Models\InventoryLot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model SaleItem
 * 
 * Test ini menguji semua method dan relasi yang ada di model SaleItem
 */
class SaleItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat sale item baru dengan field sesuai input
     * 
     * Menguji bahwa sale item dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_sale_item_with_all_fields(): void
    {
        // Menyiapkan data sale
        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
        ]);

        // Menyiapkan data sale item untuk diuji
        $saleItemData = [
            'sale_id' => $sale->id,
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 50000,
            'subtotal' => 500000,
        ];

        // Membuat sale item baru
        $saleItem = SaleItem::create($saleItemData);

        // Memverifikasi bahwa sale item berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('sale_items', [
            'id' => $saleItem->id,
            'sale_id' => $sale->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);
    }

    /**
     * Test: Relasi sale mengembalikan sale yang memiliki item ini
     * 
     * Menguji bahwa relasi belongs-to antara sale item dan sale berfungsi
     */
    public function test_sale_relationship(): void
    {
        // Membuat sale dan sale item
        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($sale->id, $saleItem->sale->id);
    }

    /**
     * Test: Relasi inventoryType mengembalikan inventory type yang dijual
     * 
     * Menguji bahwa relasi belongs-to antara sale item dan inventory type berfungsi
     */
    public function test_inventory_type_relationship(): void
    {
        // Membuat inventory type dan sale item
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'inventory_type_id' => $inventoryType->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryType->id, $saleItem->inventoryType->id);
    }

    /**
     * Test: Relasi inventoryLot mengembalikan inventory lot yang dijual
     * 
     * Menguji bahwa relasi belongs-to antara sale item dan inventory lot berfungsi
     */
    public function test_inventory_lot_relationship(): void
    {
        // Membuat inventory type, lot, dan sale item
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $inventoryLot = InventoryLot::create([
            'inventory_type_id' => $inventoryType->id,
            'current_stock' => 100,
            'stock_unit' => 'kg',
        ]);

        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'inventory_lot_id' => $inventoryLot->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryLot->id, $saleItem->inventoryLot->id);
    }
}








