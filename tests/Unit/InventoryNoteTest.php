<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InventoryNote;
use App\Models\InventoryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model InventoryNote
 * 
 * Test ini menguji semua method dan relasi yang ada di model InventoryNote
 */
class InventoryNoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat inventory note baru dengan field sesuai input
     * 
     * Menguji bahwa inventory note dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_inventory_note_with_all_fields(): void
    {
        // Menyiapkan data inventory type dan user
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        // Menyiapkan data note untuk diuji
        $noteData = [
            'inventory_type_id' => $inventoryType->id,
            'content' => 'Catatan tentang stok benih',
            'user_id' => $user->id,
        ];

        // Membuat inventory note baru
        $note = InventoryNote::create($noteData);

        // Memverifikasi bahwa inventory note berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('inventory_notes', [
            'id' => $note->id,
            'inventory_type_id' => $inventoryType->id,
            'content' => 'Catatan tentang stok benih',
        ]);
    }

    /**
     * Test: Relasi inventoryType mengembalikan inventory type yang memiliki note ini
     * 
     * Menguji bahwa relasi belongs-to antara inventory note dan inventory type berfungsi
     */
    public function test_inventory_type_relationship(): void
    {
        // Membuat inventory type dan note
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $note = InventoryNote::create([
            'inventory_type_id' => $inventoryType->id,
            'content' => 'Catatan tentang stok benih',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryType->id, $note->inventoryType->id);
    }

    /**
     * Test: Relasi user mengembalikan user yang membuat note ini
     * 
     * Menguji bahwa relasi belongs-to antara inventory note dan user berfungsi
     */
    public function test_user_relationship(): void
    {
        // Membuat user dan note
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        $note = InventoryNote::create([
            'content' => 'Catatan tentang stok benih',
            'user_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $note->user->id);
    }
}








