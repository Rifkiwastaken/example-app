<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\PlantingLocation;
use App\Models\Treatment;
use App\Models\Nutrient;
use App\Models\Planting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Expense
 * 
 * Test ini menguji semua method dan relasi yang ada di model Expense
 */
class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat expense baru dengan field sesuai input
     * 
     * Menguji bahwa expense dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_expense_with_all_fields(): void
    {
        // Menyiapkan data planting location
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        // Menyiapkan data expense untuk diuji
        $expenseData = [
            'planting_location_id' => $plantingLocation->id,
            'expense_name' => 'Biaya Tenaga Kerja',
            'amount' => 1000000,
            'expense_type' => 'tenaga_kerja',
            'expense_date' => Carbon::now(),
        ];

        // Membuat expense baru
        $expense = Expense::create($expenseData);

        // Memverifikasi bahwa expense berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_name' => 'Biaya Tenaga Kerja',
            'amount' => 1000000,
        ]);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara expense dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan expense
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $expense = Expense::create([
            'planting_location_id' => $plantingLocation->id,
            'expense_name' => 'Biaya Tenaga Kerja',
            'amount' => 1000000,
            'expense_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $expense->plantingLocation->id);
    }

    /**
     * Test: Method getPlant mengembalikan plant dari treatment atau nutrient
     * 
     * Menguji bahwa method dapat mengambil plant dari treatment atau nutrient yang terkait
     */
    public function test_get_plant_returns_plant_from_treatment_or_nutrient(): void
    {
        // Membuat plant, planting, treatment, dan expense
        $plant = \App\Models\Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $planting = Planting::create([
            'plant_id' => $plant->id,
            'quantity_planted' => 100,
        ]);

        $treatment = Treatment::create([
            'planting_id' => $planting->id,
            'treatment_name' => 'Pestisida',
            'treatment_date' => Carbon::now(),
        ]);

        $expense = Expense::create([
            'treatment_id' => $treatment->id,
            'expense_name' => 'Biaya Pestisida',
            'amount' => 500000,
            'expense_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa method getPlant mengembalikan plant yang benar
        $this->assertEquals($plant->id, $expense->plant->id);
    }
}








