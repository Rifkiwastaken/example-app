<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantingLocation;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\Expense;
use App\Models\PlantingLocationNote;
use App\Models\PlantingLocationPhoto;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model PlantingLocation
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantingLocation
 */
class PlantingLocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat planting location baru dengan field sesuai input
     * 
     * Menguji bahwa planting location dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_planting_location_with_all_fields(): void
    {
        // Menyiapkan data planting location untuk diuji
        $plantingLocationData = [
            'name' => 'Lahan Utama',
            'location_type' => 'lapangan',
            'planting_format' => 'petak',
            'num_beds' => 10,
            'bed_length_m' => 20.5,
            'bed_width_m' => 5.0,
            'description' => 'Lahan utama untuk penanaman padi',
        ];

        // Membuat planting location baru
        $plantingLocation = PlantingLocation::create($plantingLocationData);

        // Memverifikasi bahwa planting location berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('planting_locations', [
            'id' => $plantingLocation->id,
            'name' => 'Lahan Utama',
            'location_type' => 'lapangan',
            'num_beds' => 10,
        ]);
    }

    /**
     * Test: Relasi plants mengembalikan semua plant di location ini
     * 
     * Menguji bahwa relasi one-to-many antara planting location dan plant berfungsi
     */
    public function test_plants_relationship(): void
    {
        // Membuat planting location dan beberapa plants
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $plant1 = Plant::create([
            'name' => 'Padi Varietas A',
            'planting_location_id' => $plantingLocation->id,
            'variety' => 'Varietas A',
        ]);

        $plant2 = Plant::create([
            'name' => 'Padi Varietas B',
            'planting_location_id' => $plantingLocation->id,
            'variety' => 'Varietas B',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plantingLocation->plants->contains($plant1));
        $this->assertTrue($plantingLocation->plants->contains($plant2));
        $this->assertEquals(2, $plantingLocation->plants->count());
    }

    /**
     * Test: Relasi plantings mengembalikan semua penanaman di location ini
     * 
     * Menguji bahwa relasi one-to-many antara planting location dan planting berfungsi
     */
    public function test_plantings_relationship(): void
    {
        // Membuat planting location dan beberapa plantings
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $planting1 = Planting::create([
            'planting_location_id' => $plantingLocation->id,
            'quantity_planted' => 100,
        ]);

        $planting2 = Planting::create([
            'planting_location_id' => $plantingLocation->id,
            'quantity_planted' => 200,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plantingLocation->plantings->contains($planting1));
        $this->assertTrue($plantingLocation->plantings->contains($planting2));
        $this->assertEquals(2, $plantingLocation->plantings->count());
    }

    /**
     * Test: Relasi landManagerUsers mengembalikan semua user yang mengelola location
     * 
     * Menguji bahwa relasi many-to-many antara planting location dan user (manager) berfungsi
     */
    public function test_land_manager_users_relationship(): void
    {
        // Membuat planting location dan user
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        // Menghubungkan user sebagai manager ke planting location
        $plantingLocation->landManagerUsers()->attach($user->id);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plantingLocation->landManagerUsers->contains($user));
        $this->assertEquals(1, $plantingLocation->landManagerUsers->count());
    }

    /**
     * Test: Relasi landWorkerUsers mengembalikan semua user yang bekerja di location
     * 
     * Menguji bahwa relasi many-to-many antara planting location dan user (worker) berfungsi
     */
    public function test_land_worker_users_relationship(): void
    {
        // Membuat planting location dan user
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'penangkar',
        ]);

        // Menghubungkan user sebagai worker ke planting location
        $plantingLocation->landWorkerUsers()->attach($user->id);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($plantingLocation->landWorkerUsers->contains($user));
        $this->assertEquals(1, $plantingLocation->landWorkerUsers->count());
    }
}








