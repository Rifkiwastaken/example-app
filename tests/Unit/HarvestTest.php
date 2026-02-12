<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Harvest;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLocation;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Harvest
 * 
 * Test ini menguji semua method dan relasi yang ada di model Harvest
 */
class HarvestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat harvest baru dengan field sesuai input
     * 
     * Menguji bahwa harvest dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_harvest_with_all_fields(): void
    {
        // Menyiapkan data plant, planting, dan planting location
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $planting = Planting::create([
            'plant_id' => $plant->id,
            'planting_location_id' => $plantingLocation->id,
            'quantity_planted' => 100,
        ]);

        // Menyiapkan data harvest untuk diuji
        $harvestData = [
            'plant_id' => $plant->id,
            'planting_id' => $planting->id,
            'planting_location_id' => $plantingLocation->id,
            'harvested_at' => Carbon::now(),
            'quantity' => 100,
            'unit' => 'kg',
            'loss_quantity' => 5,
        ];

        // Membuat harvest baru
        $harvest = Harvest::create($harvestData);

        // Memverifikasi bahwa harvest berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('harvests', [
            'id' => $harvest->id,
            'plant_id' => $plant->id,
            'planting_id' => $planting->id,
            'quantity' => 100,
        ]);
    }

    /**
     * Test: Relasi plant mengembalikan plant yang dipanen
     * 
     * Menguji bahwa relasi belongs-to antara harvest dan plant berfungsi
     */
    public function test_plant_relationship(): void
    {
        // Membuat plant dan harvest
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $harvest = Harvest::create([
            'plant_id' => $plant->id,
            'quantity' => 100,
            'unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plant->id, $harvest->plant->id);
    }

    /**
     * Test: Relasi planting mengembalikan planting yang menghasilkan panen
     * 
     * Menguji bahwa relasi belongs-to antara harvest dan planting berfungsi
     */
    public function test_planting_relationship(): void
    {
        // Membuat planting dan harvest
        $planting = Planting::create([
            'quantity_planted' => 100,
        ]);

        $harvest = Harvest::create([
            'planting_id' => $planting->id,
            'quantity' => 100,
            'unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($planting->id, $harvest->planting->id);
    }

    /**
     * Test: Relasi location mengembalikan planting location tempat panen
     * 
     * Menguji bahwa relasi belongs-to antara harvest dan planting location berfungsi
     */
    public function test_location_relationship(): void
    {
        // Membuat planting location dan harvest
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $harvest = Harvest::create([
            'planting_location_id' => $plantingLocation->id,
            'quantity' => 100,
            'unit' => 'kg',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $harvest->location->id);
    }

    /**
     * Test: Relasi certification mengembalikan sertifikasi untuk panen ini
     * 
     * Menguji bahwa relasi one-to-one antara harvest dan certification berfungsi
     */
    public function test_certification_relationship(): void
    {
        // Membuat harvest dan certification
        $harvest = Harvest::create([
            'quantity' => 100,
            'unit' => 'kg',
        ]);

        $certification = Certification::create([
            'harvest_id' => $harvest->id,
            'certification_status' => 'dalam_proses',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($certification->id, $harvest->certification->id);
    }

    /**
     * Test: Relasi recorder mengembalikan user yang mencatat panen
     * 
     * Menguji bahwa relasi belongs-to antara harvest dan user (recorder) berfungsi
     */
    public function test_recorder_relationship(): void
    {
        // Membuat user dan harvest
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'penangkar',
        ]);

        $harvest = Harvest::create([
            'quantity' => 100,
            'unit' => 'kg',
            'recorded_by' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $harvest->recorder->id);
    }

    /**
     * Test: Relasi editor mengembalikan user yang mengedit panen
     * 
     * Menguji bahwa relasi belongs-to antara harvest dan user (editor) berfungsi
     */
    public function test_editor_relationship(): void
    {
        // Membuat user dan harvest
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $harvest = Harvest::create([
            'quantity' => 100,
            'unit' => 'kg',
            'edited_by' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $harvest->editor->id);
    }
}








