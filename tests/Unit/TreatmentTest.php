<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Treatment;
use App\Models\PlantingLocation;
use App\Models\Planting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Treatment
 * 
 * Test ini menguji semua method dan relasi yang ada di model Treatment
 */
class TreatmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat treatment baru dengan field sesuai input
     * 
     * Menguji bahwa treatment dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_treatment_with_all_fields(): void
    {
        // Menyiapkan data planting location
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        // Menyiapkan data treatment untuk diuji
        $treatmentData = [
            'planting_location_id' => $plantingLocation->id,
            'treatment_name' => 'Pestisida',
            'treatment_type' => 'Pestisida',
            'treatment_date' => Carbon::now(),
            'total_cost' => 500000,
        ];

        // Membuat treatment baru
        $treatment = Treatment::create($treatmentData);

        // Memverifikasi bahwa treatment berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('treatments', [
            'id' => $treatment->id,
            'treatment_name' => 'Pestisida',
            'treatment_type' => 'Pestisida',
        ]);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara treatment dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan treatment
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $treatment = Treatment::create([
            'planting_location_id' => $plantingLocation->id,
            'treatment_name' => 'Pestisida',
            'treatment_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $treatment->plantingLocation->id);
    }

    /**
     * Test: Relasi responsiblePerson mengembalikan user yang bertanggung jawab
     * 
     * Menguji bahwa relasi belongs-to antara treatment dan user berfungsi
     */
    public function test_responsible_person_relationship(): void
    {
        // Membuat user dan treatment
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $treatment = Treatment::create([
            'treatment_name' => 'Pestisida',
            'treatment_date' => Carbon::now(),
            'responsible_person_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $treatment->responsiblePerson->id);
    }
}








