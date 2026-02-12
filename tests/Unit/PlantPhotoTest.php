<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantPhoto;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model PlantPhoto
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantPhoto
 */
class PlantPhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat plant photo baru dengan field sesuai input
     * 
     * Menguji bahwa plant photo dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_plant_photo_with_all_fields(): void
    {
        // Menyiapkan data plant
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        // Menyiapkan data photo untuk diuji
        $photoData = [
            'plant_id' => $plant->id,
            'file_path' => 'photos/plant1.jpg',
            'file_name' => 'plant1.jpg',
            'file_size' => 1024000,
            'mime_type' => 'image/jpeg',
            'taken_at' => Carbon::now(),
        ];

        // Membuat plant photo baru
        $photo = PlantPhoto::create($photoData);

        // Memverifikasi bahwa plant photo berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('plant_photos', [
            'id' => $photo->id,
            'plant_id' => $plant->id,
            'file_name' => 'plant1.jpg',
        ]);
    }

    /**
     * Test: Relasi plant mengembalikan plant yang memiliki photo ini
     * 
     * Menguji bahwa relasi belongs-to antara plant photo dan plant berfungsi
     */
    public function test_plant_relationship(): void
    {
        // Membuat plant dan plant photo
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $photo = PlantPhoto::create([
            'plant_id' => $plant->id,
            'file_path' => 'photos/plant1.jpg',
            'file_name' => 'plant1.jpg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plant->id, $photo->plant->id);
    }
}








