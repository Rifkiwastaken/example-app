<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Planting;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\Harvest;
use App\Models\PlantingLoss;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Planting
 * 
 * Test ini menguji semua method dan relasi yang ada di model Planting
 */
class PlantingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat planting baru dengan field sesuai input
     * 
     * Menguji bahwa planting dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_planting_with_all_fields(): void
    {
        // Menyiapkan data plant dan planting location
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        // Menyiapkan data planting untuk diuji
        $plantingData = [
            'plant_id' => $plant->id,
            'planting_location_id' => $plantingLocation->id,
            'quantity_planted' => 100,
            'planted_at' => Carbon::now(),
            'estimated_harvest_date' => Carbon::now()->addMonths(4),
            'area_ha' => 1.5,
            'is_completed' => false,
        ];

        // Membuat planting baru
        $planting = Planting::create($plantingData);

        // Memverifikasi bahwa planting berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('plantings', [
            'id' => $planting->id,
            'plant_id' => $plant->id,
            'planting_location_id' => $plantingLocation->id,
            'quantity_planted' => 100,
        ]);
    }

    /**
     * Test: Relasi plant mengembalikan plant yang ditanam
     * 
     * Menguji bahwa relasi belongs-to antara planting dan plant berfungsi
     */
    public function test_plant_relationship(): void
    {
        // Membuat plant dan planting
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $planting = Planting::create([
            'plant_id' => $plant->id,
            'quantity_planted' => 100,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plant->id, $planting->plant->id);
    }

    /**
     * Test: Relasi location mengembalikan planting location tempat penanaman
     * 
     * Menguji bahwa relasi belongs-to antara planting dan planting location berfungsi
     */
    public function test_location_relationship(): void
    {
        // Membuat planting location dan planting
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $planting = Planting::create([
            'planting_location_id' => $plantingLocation->id,
            'quantity_planted' => 100,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $planting->location->id);
    }

    /**
     * Test: Relasi harvests mengembalikan semua panen dari planting ini
     * 
     * Menguji bahwa relasi one-to-many antara planting dan harvest berfungsi
     */
    public function test_harvests_relationship(): void
    {
        // Membuat planting dan beberapa harvests
        $planting = Planting::create([
            'quantity_planted' => 100,
        ]);

        $harvest1 = Harvest::create([
            'planting_id' => $planting->id,
            'quantity' => 50,
            'unit' => 'kg',
        ]);

        $harvest2 = Harvest::create([
            'planting_id' => $planting->id,
            'quantity' => 75,
            'unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($planting->harvests->contains($harvest1));
        $this->assertTrue($planting->harvests->contains($harvest2));
        $this->assertEquals(2, $planting->harvests->count());
    }

    /**
     * Test: Relasi losses mengembalikan semua kerugian dari planting ini
     * 
     * Menguji bahwa relasi one-to-many antara planting dan planting loss berfungsi
     */
    public function test_losses_relationship(): void
    {
        // Membuat planting dan beberapa losses
        $planting = Planting::create([
            'quantity_planted' => 100,
        ]);

        $loss1 = PlantingLoss::create([
            'planting_id' => $planting->id,
            'loss_amount' => 10,
            'loss_reason' => 'Hama',
        ]);

        $loss2 = PlantingLoss::create([
            'planting_id' => $planting->id,
            'loss_amount' => 15,
            'loss_reason' => 'Penyakit',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($planting->losses->contains($loss1));
        $this->assertTrue($planting->losses->contains($loss2));
        $this->assertEquals(2, $planting->losses->count());
    }
}








