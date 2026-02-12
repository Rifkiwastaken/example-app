<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InventoryPhoto;
use App\Models\InventoryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model InventoryPhoto
 * 
 * Test ini menguji semua method dan relasi yang ada di model InventoryPhoto
 */
class InventoryPhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat inventory photo baru dengan field sesuai input
     * 
     * Menguji bahwa inventory photo dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_inventory_photo_with_all_fields(): void
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

        // Menyiapkan data photo untuk diuji
        $photoData = [
            'inventory_type_id' => $inventoryType->id,
            'photo_path' => 'photos/inventory1.jpg',
            'caption' => 'Foto stok benih',
            'user_id' => $user->id,
        ];

        // Membuat inventory photo baru
        $photo = InventoryPhoto::create($photoData);

        // Memverifikasi bahwa inventory photo berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('inventory_photos', [
            'id' => $photo->id,
            'inventory_type_id' => $inventoryType->id,
            'caption' => 'Foto stok benih',
        ]);
    }

    /**
     * Test: Relasi inventoryType mengembalikan inventory type yang memiliki photo ini
     * 
     * Menguji bahwa relasi belongs-to antara inventory photo dan inventory type berfungsi
     */
    public function test_inventory_type_relationship(): void
    {
        // Membuat inventory type dan photo
        $inventoryType = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $photo = InventoryPhoto::create([
            'inventory_type_id' => $inventoryType->id,
            'photo_path' => 'photos/inventory1.jpg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($inventoryType->id, $photo->inventoryType->id);
    }

    /**
     * Test: Relasi user mengembalikan user yang mengupload photo ini
     * 
     * Menguji bahwa relasi belongs-to antara inventory photo dan user berfungsi
     */
    public function test_user_relationship(): void
    {
        // Membuat user dan photo
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_gudang',
        ]);

        $photo = InventoryPhoto::create([
            'photo_path' => 'photos/inventory1.jpg',
            'user_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $photo->user->id);
    }
}








