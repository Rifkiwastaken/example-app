<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantingLocationPhoto;
use App\Models\PlantingLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model PlantingLocationPhoto
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantingLocationPhoto
 */
class PlantingLocationPhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat planting location photo baru dengan field sesuai input
     * 
     * Menguji bahwa planting location photo dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_planting_location_photo_with_all_fields(): void
    {
        // Menyiapkan data planting location
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        // Menyiapkan data photo untuk diuji
        $photoData = [
            'planting_location_id' => $plantingLocation->id,
            'file_path' => 'photos/location1.jpg',
            'file_name' => 'location1.jpg',
            'file_size' => 1024000,
            'mime_type' => 'image/jpeg',
            'taken_at' => Carbon::now(),
        ];

        // Membuat planting location photo baru
        $photo = PlantingLocationPhoto::create($photoData);

        // Memverifikasi bahwa planting location photo berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('planting_location_photos', [
            'id' => $photo->id,
            'planting_location_id' => $plantingLocation->id,
            'file_name' => 'location1.jpg',
        ]);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang memiliki photo ini
     * 
     * Menguji bahwa relasi belongs-to antara planting location photo dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan photo
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $photo = PlantingLocationPhoto::create([
            'planting_location_id' => $plantingLocation->id,
            'file_path' => 'photos/location1.jpg',
            'file_name' => 'location1.jpg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $photo->plantingLocation->id);
    }
}








