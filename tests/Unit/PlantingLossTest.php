<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantingLoss;
use App\Models\Planting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model PlantingLoss
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantingLoss
 */
class PlantingLossTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat planting loss baru dengan field sesuai input
     * 
     * Menguji bahwa planting loss dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_planting_loss_with_all_fields(): void
    {
        // Menyiapkan data planting
        $planting = Planting::create([
            'quantity_planted' => 100,
        ]);

        // Menyiapkan data planting loss untuk diuji
        $lossData = [
            'planting_id' => $planting->id,
            'loss_date' => Carbon::now(),
            'loss_amount' => 10,
            'loss_reason' => 'Hama',
            'description' => 'Kerugian akibat serangan hama',
        ];

        // Membuat planting loss baru
        $loss = PlantingLoss::create($lossData);

        // Memverifikasi bahwa planting loss berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('planting_losses', [
            'id' => $loss->id,
            'planting_id' => $planting->id,
            'loss_amount' => 10,
            'loss_reason' => 'Hama',
        ]);
    }

    /**
     * Test: Relasi planting mengembalikan planting yang memiliki loss ini
     * 
     * Menguji bahwa relasi belongs-to antara planting loss dan planting berfungsi
     */
    public function test_planting_relationship(): void
    {
        // Membuat planting dan planting loss
        $planting = Planting::create([
            'quantity_planted' => 100,
        ]);

        $loss = PlantingLoss::create([
            'planting_id' => $planting->id,
            'loss_amount' => 10,
            'loss_reason' => 'Hama',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($planting->id, $loss->planting->id);
    }
}








