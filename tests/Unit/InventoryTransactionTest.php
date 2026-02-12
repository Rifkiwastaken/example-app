<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InventoryTransaction;
use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model InventoryTransaction
 * 
 * Test ini menguji semua method dan relasi yang ada di model InventoryTransaction
 */
class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat inventory transaction baru dengan field sesuai input
     * 
     * Menguji bahwa inventory transaction dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_inventory_transaction_with_all_fields(): void
    {
        // Menyiapkan data inventory type
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Menyiapkan data inventory transaction untuk diuji
        $transactionData = [
            'inventory_type_id' => $inventoryType->id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
            'unit' => 'kg',
        ];

        // Membuat inventory transaction baru
        $transaction = InventoryTransaction::create($transactionData);

        // Memverifikasi bahwa inventory transaction berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction->id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
        ]);
    }

    /**
     * Test: Relasi inventoryType mengembalikan inventory type yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara inventory transaction dan inventory type berfungsi
     */
    public function test_inventory_type_relationship(): void
    {
        // Membuat inventory type dan transaction
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $transaction = InventoryTransaction::create([
            'inventory_type_id' => $inventoryType->id,
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryType->id, $transaction->inventoryType->id);
    }

    /**
     * Test: Method getTransactionTypeLabel mengembalikan label tipe transaksi
     * 
     * Menguji bahwa setiap tipe transaksi memiliki label yang sesuai
     */
    public function test_get_transaction_type_label_returns_label(): void
    {
        // Membuat transaction dengan type stok_masuk
        $transaction1 = InventoryTransaction::create([
            'transaction_type' => 'stok_masuk',
            'quantity' => 100,
        ]);
        $this->assertEquals('Stok Masuk (Lot)', $transaction1->transaction_type_label);

        // Membuat transaction dengan type stok_keluar
        $transaction2 = InventoryTransaction::create([
            'transaction_type' => 'stok_keluar',
            'quantity' => 50,
        ]);
        $this->assertEquals('Stok Keluar', $transaction2->transaction_type_label);

        // Membuat transaction dengan type penyesuaian_tambah
        $transaction3 = InventoryTransaction::create([
            'transaction_type' => 'penyesuaian_tambah',
            'quantity' => 25,
        ]);
        $this->assertEquals('Penyesuaian (+)', $transaction3->transaction_type_label);

        // Membuat transaction dengan type penyesuaian_kurang
        $transaction4 = InventoryTransaction::create([
            'transaction_type' => 'penyesuaian_kurang',
            'quantity' => 10,
        ]);
        $this->assertEquals('Penyesuaian (-)', $transaction4->transaction_type_label);

        // Membuat transaction dengan type distribusi
        $transaction5 = InventoryTransaction::create([
            'transaction_type' => 'distribusi',
            'quantity' => 30,
        ]);
        $this->assertEquals('Distribusi', $transaction5->transaction_type_label);

        // Membuat transaction dengan type pindah_lokasi
        $transaction6 = InventoryTransaction::create([
            'transaction_type' => 'pindah_lokasi',
            'quantity' => 20,
        ]);
        $this->assertEquals('Pindahkan Stok', $transaction6->transaction_type_label);
    }
}








