<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Nutrient;
use App\Models\PlantingLocation;
use App\Models\Planting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Nutrient
 * 
 * Test ini menguji semua method dan relasi yang ada di model Nutrient
 */
class NutrientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat nutrient baru dengan field sesuai input
     * 
     * Menguji bahwa nutrient dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_nutrient_with_all_fields(): void
    {
        // Menyiapkan data planting location
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        // Menyiapkan data nutrient untuk diuji
        $nutrientData = [
            'planting_location_id' => $plantingLocation->id,
            'nutrient_name' => 'Pupuk NPK',
            'product_applied' => 'Pupuk NPK 16-16-16',
            'amount_applied' => 50,
            'unit' => 'kg',
            'application_date' => Carbon::now(),
            'total_cost' => 300000,
        ];

        // Membuat nutrient baru
        $nutrient = Nutrient::create($nutrientData);

        // Memverifikasi bahwa nutrient berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('nutrients', [
            'id' => $nutrient->id,
            'nutrient_name' => 'Pupuk NPK',
            'product_applied' => 'Pupuk NPK 16-16-16',
        ]);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara nutrient dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan nutrient
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $nutrient = Nutrient::create([
            'planting_location_id' => $plantingLocation->id,
            'nutrient_name' => 'Pupuk NPK',
            'application_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $nutrient->plantingLocation->id);
    }

    /**
     * Test: Relasi responsiblePerson mengembalikan user yang bertanggung jawab
     * 
     * Menguji bahwa relasi belongs-to antara nutrient dan user berfungsi
     */
    public function test_responsible_person_relationship(): void
    {
        // Membuat user dan nutrient
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $nutrient = Nutrient::create([
            'nutrient_name' => 'Pupuk NPK',
            'application_date' => Carbon::now(),
            'responsible_person_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $nutrient->responsiblePerson->id);
    }
}








