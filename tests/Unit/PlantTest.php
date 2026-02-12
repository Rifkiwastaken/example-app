<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Plant;
use App\Models\PlantType;
use App\Models\PlantingLocation;
use App\Models\Planting;
use App\Models\Harvest;
use App\Models\PlantNote;
use App\Models\PlantPhoto;
use App\Models\Certification;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model Plant
 * 
 * Test ini menguji semua method dan relasi yang ada di model Plant
 */
class PlantTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat plant baru dengan field sesuai input
     * 
     * Menguji bahwa plant dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_plant_with_all_fields(): void
    {
        // Menyiapkan data plant type dan planting location
        $plantType = PlantType::create([
            'name' => 'Padi',
            'category' => 'Cereal',
        ]);

        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        // Menyiapkan data plant untuk diuji
        $plantData = [
            'name' => 'Padi Varietas A',
            'plant_type_id' => $plantType->plant_type_id,
            'planting_location_id' => $plantingLocation->planting_location_id,
            'variety' => 'Varietas A',
            'status' => 'active',
            'progress' => 50,
        ];

        // Membuat plant baru
        $plant = Plant::create($plantData);

        // Memverifikasi bahwa plant berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('plants', [
            'plant_id' => $plant->plant_id,
            'name' => 'Padi Varietas A',
            'plant_type_id' => $plantType->plant_type_id,
            'planting_location_id' => $plantingLocation->planting_location_id,
            'variety' => 'Varietas A',
        ]);
    }

    /**
     * Test: Relasi type mengembalikan plant type yang dimiliki plant
     * 
     * Menguji bahwa relasi belongs-to antara plant dan plant type berfungsi
     */
    public function test_type_relationship(): void
    {
        // Membuat plant type dan plant
        $plantType = PlantType::create([
            'name' => 'Padi',
            'category' => 'Cereal',
        ]);

        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'plant_type_id' => $plantType->plant_type_id,
            'variety' => 'Varietas A',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantType->plant_type_id, $plant->type->plant_type_id);
        $this->assertEquals('Padi', $plant->type->name);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location tempat plant ditanam
     * 
     * Menguji bahwa relasi belongs-to antara plant dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan plant
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'planting_location_id' => $plantingLocation->planting_location_id,
            'variety' => 'Varietas A',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->planting_location_id, $plant->plantingLocation->planting_location_id);
        $this->assertEquals('Lahan Test', $plant->plantingLocation->name);
    }

    /**
     * Test: Relasi plantings mengembalikan semua penanaman untuk plant ini
     * 
     * Menguji bahwa relasi one-to-many antara plant dan planting berfungsi
     */
    public function test_plantings_relationship(): void
    {
        // Membuat plant dan beberapa planting
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $planting1 = Planting::create([
            'plant_id' => $plant->plant_id,
            'quantity_planted' => 100,
        ]);

        $planting2 = Planting::create([
            'plant_id' => $plant->plant_id,
            'quantity_planted' => 200,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plant->plantings->contains($planting1));
        $this->assertTrue($plant->plantings->contains($planting2));
        $this->assertEquals(2, $plant->plantings->count());
    }

    /**
     * Test: Relasi harvests mengembalikan semua panen untuk plant ini
     * 
     * Menguji bahwa relasi one-to-many antara plant dan harvest berfungsi
     */
    public function test_harvests_relationship(): void
    {
        // Membuat plant dan beberapa harvest
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $harvest1 = Harvest::create([
            'plant_id' => $plant->plant_id,
            'quantity' => 50,
            'unit' => 'kg',
        ]);

        $harvest2 = Harvest::create([
            'plant_id' => $plant->plant_id,
            'quantity' => 75,
            'unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plant->harvests->contains($harvest1));
        $this->assertTrue($plant->harvests->contains($harvest2));
        $this->assertEquals(2, $plant->harvests->count());
    }

    /**
     * Test: Relasi notes mengembalikan semua catatan untuk plant ini
     * 
     * Menguji bahwa relasi one-to-many antara plant dan plant note berfungsi
     */
    public function test_notes_relationship(): void
    {
        // Membuat plant dan beberapa notes
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $note1 = PlantNote::create([
            'plant_id' => $plant->plant_id,
            'description' => 'Catatan pertama',
        ]);

        $note2 = PlantNote::create([
            'plant_id' => $plant->plant_id,
            'description' => 'Catatan kedua',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plant->notes->contains($note1));
        $this->assertTrue($plant->notes->contains($note2));
        $this->assertEquals(2, $plant->notes->count());
    }

    /**
     * Test: Relasi photos mengembalikan semua foto untuk plant ini
     * 
     * Menguji bahwa relasi one-to-many antara plant dan plant photo berfungsi
     */
    public function test_photos_relationship(): void
    {
        // Membuat plant dan beberapa photos
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $photo1 = PlantPhoto::create([
            'plant_id' => $plant->plant_id,
            'file_path' => 'photos/photo1.jpg',
            'file_name' => 'photo1.jpg',
        ]);

        $photo2 = PlantPhoto::create([
            'plant_id' => $plant->plant_id,
            'file_path' => 'photos/photo2.jpg',
            'file_name' => 'photo2.jpg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plant->photos->contains($photo1));
        $this->assertTrue($plant->photos->contains($photo2));
        $this->assertEquals(2, $plant->photos->count());
    }

    /**
     * Test: Relasi certifications mengembalikan semua sertifikasi untuk plant ini
     * 
     * Menguji bahwa relasi one-to-many antara plant dan certification berfungsi
     */
    public function test_certifications_relationship(): void
    {
        // Membuat plant dan beberapa certifications
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $certification1 = Certification::create([
            'plant_id' => $plant->plant_id,
            'certification_status' => 'dalam_proses',
        ]);

        $certification2 = Certification::create([
            'plant_id' => $plant->plant_id,
            'certification_status' => 'lulus',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plant->certifications->contains($certification1));
        $this->assertTrue($plant->certifications->contains($certification2));
        $this->assertEquals(2, $plant->certifications->count());
    }
}








