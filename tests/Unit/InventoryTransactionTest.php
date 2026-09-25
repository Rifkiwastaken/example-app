<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\StockHistory;
use App\Models\InventoryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk log transaksi stok (stock_histories).
 * Tabel inventory_transactions telah digabung ke stock_histories.
 */
class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat stock history dengan transaction_type (log transaksi stok)
     */
    public function test_can_create_stock_history_transaction_with_all_fields(): void
    {
        $user = User::factory()->create();
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $data = [
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
            'unit' => 'kg',
            'user_id' => $user->user_id,
        ];

        $record = StockHistory::create($data);

        $this->assertDatabaseHas('stock_histories', [
            'stock_history_id' => $record->stock_history_id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
        ]);
    }

    /**
     * Test: Relasi inventoryType pada StockHistory
     */
    public function test_inventory_type_relationship(): void
    {
        $user = User::factory()->create();
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $record = StockHistory::create([
            'inventory_type_id' => $inventoryType->inventory_type_id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
            'unit' => 'kg',
            'user_id' => $user->user_id,
        ]);

        $this->assertEquals($inventoryType->inventory_type_id, $record->inventoryType->inventory_type_id);
    }

    /**
     * Test: getTransactionTypeLabelAttribute mengembalikan label tipe transaksi
     */
    public function test_get_transaction_type_label_returns_label(): void
    {
        $user = User::factory()->create();
        $type = InventoryType::create(['name' => 'Benih Test', 'category' => 'Benih', 'unit' => 'kg']);

        $r1 = StockHistory::create([
            'inventory_type_id' => $type->inventory_type_id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
            'user_id' => $user->user_id,
        ]);
        $this->assertEquals('Stok Masuk (Lot)', $r1->transaction_type_label);

        $r2 = StockHistory::create([
            'inventory_type_id' => $type->inventory_type_id,
            'transaction_type' => 'distribusi',
            'quantity' => 50,
            'user_id' => $user->user_id,
        ]);
        $this->assertEquals('Distribusi', $r2->transaction_type_label);
    }
}
