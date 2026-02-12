<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CertificationReport;
use App\Models\Certification;
use App\Models\InventoryType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model CertificationReport
 * 
 * Test ini menguji semua method dan relasi yang ada di model CertificationReport
 */
class CertificationReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat certification report baru dengan field sesuai input
     * 
     * Menguji bahwa certification report dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_certification_report_with_all_fields(): void
    {
        // Menyiapkan data certification
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        // Menyiapkan data certification report untuk diuji
        $reportData = [
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_number_bpsb' => 'BPSB-001',
            'report_date' => Carbon::now(),
            'inspection_phase' => 'Fase 1',
            'conclusion' => 'LULUS',
            'certified_seed_quantity' => 1000,
        ];

        // Membuat certification report baru
        $report = CertificationReport::create($reportData);

        // Memverifikasi bahwa certification report berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('certification_reports', [
            'id' => $report->id,
            'report_type' => 'Lapangan',
            'report_number_bpsb' => 'BPSB-001',
            'conclusion' => 'LULUS',
        ]);
    }

    /**
     * Test: Relasi certification mengembalikan certification yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara certification report dan certification berfungsi
     */
    public function test_certification_relationship(): void
    {
        // Membuat certification dan certification report
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        $report = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($certification->id, $report->certification->id);
    }

    /**
     * Test: Relasi inventoryTypes mengembalikan semua inventory type yang terkait
     * 
     * Menguji bahwa relasi many-to-many antara certification report dan inventory type berfungsi
     */
    public function test_inventory_types_relationship(): void
    {
        // Membuat certification report dan inventory types
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        $report = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now(),
        ]);

        $inventoryType1 = InventoryType::create([
            'name' => 'Benih Padi',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        $inventoryType2 = InventoryType::create([
            'name' => 'Benih Jagung',
            'category' => 'Benih',
            'unit' => 'kg',
        ]);

        // Menghubungkan inventory types ke report
        $report->inventoryTypes()->attach($inventoryType1->id, ['quantity' => 500]);
        $report->inventoryTypes()->attach($inventoryType2->id, ['quantity' => 300]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($report->inventoryTypes->contains($inventoryType1));
        $this->assertTrue($report->inventoryTypes->contains($inventoryType2));
        $this->assertEquals(2, $report->inventoryTypes->count());
    }

    /**
     * Test: Method getPhaseLabel mengembalikan label fase inspeksi
     * 
     * Menguji bahwa method mengembalikan label fase dengan benar
     */
    public function test_get_phase_label_returns_inspection_phase(): void
    {
        // Membuat certification report dengan inspection phase
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        $report = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now(),
            'inspection_phase' => 'Fase 1 - Lapangan',
        ]);

        // Memverifikasi bahwa phase label sesuai dengan inspection phase
        $this->assertEquals('Fase 1 - Lapangan', $report->phase_label);
    }

    /**
     * Test: Method getConclusionBadgeClass mengembalikan class badge untuk kesimpulan
     * 
     * Menguji bahwa setiap kesimpulan memiliki class badge yang sesuai
     */
    public function test_get_conclusion_badge_class_returns_badge_class(): void
    {
        // Membuat certification
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        // Membuat certification report dengan conclusion LULUS
        $report1 = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now(),
            'conclusion' => 'LULUS',
        ]);
        $this->assertEquals('badge bg-success', $report1->conclusion_badge_class);

        // Membuat certification report dengan conclusion TIDAK LULUS
        $report2 = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now(),
            'conclusion' => 'TIDAK LULUS',
        ]);
        $this->assertEquals('badge bg-danger', $report2->conclusion_badge_class);
    }
}








