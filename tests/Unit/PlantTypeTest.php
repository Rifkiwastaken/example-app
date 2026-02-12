<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantType;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model PlantType
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantType
 */
class PlantTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat plant type baru dengan field sesuai input
     * 
     * Menguji bahwa plant type dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_plant_type_with_all_fields(): void
    {
        // Menyiapkan data plant type untuk diuji
        $plantTypeData = [
            'name' => 'Padi',
            'category' => 'Cereal',
        ];

        // Membuat plant type baru
        $plantType = PlantType::create($plantTypeData);

        // Memverifikasi bahwa plant type berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('plant_types', [
            'id' => $plantType->id,
            'name' => 'Padi',
            'category' => 'Cereal',
        ]);
    }

    /**
     * Test: Relasi plants mengembalikan semua plant yang memiliki plant type ini
     * 
     * Menguji bahwa relasi one-to-many antara plant type dan plant berfungsi
     */
    public function test_plants_relationship(): void
    {
        // Membuat plant type dan beberapa plant
        $plantType = PlantType::create([
            'name' => 'Padi',
            'category' => 'Cereal',
        ]);

        $plant1 = Plant::create([
            'name' => 'Padi Varietas 1',
            'plant_type_id' => $plantType->id,
            'variety' => 'Varietas A',
        ]);

        $plant2 = Plant::create([
            'name' => 'Padi Varietas 2',
            'plant_type_id' => $plantType->id,
            'variety' => 'Varietas B',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plantType->plants->contains($plant1));
        $this->assertTrue($plantType->plants->contains($plant2));
        $this->assertEquals(2, $plantType->plants->count());
    }
}








