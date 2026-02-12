<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Certification;
use App\Models\Harvest;
use App\Models\Plant;
use App\Models\PlantingLocation;
use App\Models\CertificationReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Certification
 * 
 * Test ini menguji semua method dan relasi yang ada di model Certification
 */
class CertificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat certification baru dengan field sesuai input
     * 
     * Menguji bahwa certification dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_certification_with_all_fields(): void
    {
        // Menyiapkan data harvest, plant, dan planting location
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        $harvest = Harvest::create([
            'plant_id' => $plant->id,
            'quantity' => 100,
            'unit' => 'kg',
        ]);

        // Menyiapkan data certification untuk diuji
        $certificationData = [
            'harvest_id' => $harvest->id,
            'plant_id' => $plant->id,
            'planting_location_id' => $plantingLocation->id,
            'certification_status' => 'dalam_proses',
            'seed_class_requested' => 'FS',
        ];

        // Membuat certification baru
        $certification = Certification::create($certificationData);

        // Memverifikasi bahwa certification berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('certifications', [
            'id' => $certification->id,
            'harvest_id' => $harvest->id,
            'plant_id' => $plant->id,
            'certification_status' => 'dalam_proses',
        ]);
    }

    /**
     * Test: Relasi harvest mengembalikan harvest yang terkait dengan certification
     * 
     * Menguji bahwa relasi belongs-to antara certification dan harvest berfungsi
     */
    public function test_harvest_relationship(): void
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
        $this->assertEquals($harvest->id, $certification->harvest->id);
    }

    /**
     * Test: Relasi reports mengembalikan semua laporan sertifikasi untuk certification ini
     * 
     * Menguji bahwa relasi one-to-many antara certification dan certification report berfungsi
     */
    public function test_reports_relationship(): void
    {
        // Membuat certification dan beberapa reports
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        $report1 = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now(),
        ]);

        $report2 = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Laboratorium',
            'report_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($certification->reports->contains($report1));
        $this->assertTrue($certification->reports->contains($report2));
        $this->assertEquals(2, $certification->reports->count());
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara certification dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan certification
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        $certification = Certification::create([
            'planting_location_id' => $plantingLocation->id,
            'certification_status' => 'dalam_proses',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $certification->plantingLocation->id);
    }

    /**
     * Test: Relasi plant mengembalikan plant yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara certification dan plant berfungsi
     */
    public function test_plant_relationship(): void
    {
        // Membuat plant dan certification
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $certification = Certification::create([
            'plant_id' => $plant->id,
            'certification_status' => 'dalam_proses',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plant->id, $certification->plant->id);
    }

    /**
     * Test: Method getStatusLabel mengembalikan label status dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap status memiliki label yang sesuai
     */
    public function test_get_status_label_returns_indonesian_label(): void
    {
        // Membuat certification dengan status dalam_proses
        $certification1 = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);
        $this->assertEquals('Dalam Proses', $certification1->status_label);

        // Membuat certification dengan status lulus
        $certification2 = Certification::create([
            'certification_status' => 'lulus',
        ]);
        $this->assertEquals('Lulus', $certification2->status_label);

        // Membuat certification dengan status tidak_lulus
        $certification3 = Certification::create([
            'certification_status' => 'tidak_lulus',
        ]);
        $this->assertEquals('Tidak Lulus', $certification3->status_label);

        // Membuat certification dengan status selesai
        $certification4 = Certification::create([
            'certification_status' => 'selesai',
        ]);
        $this->assertEquals('Selesai', $certification4->status_label);
    }

    /**
     * Test: Method getLatestReportDate mengembalikan tanggal laporan terbaru
     * 
     * Menguji bahwa method dapat mengambil tanggal laporan terbaru dengan format yang benar
     */
    public function test_get_latest_report_date_returns_latest_report_date(): void
    {
        // Membuat certification dan beberapa reports dengan tanggal berbeda
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        $oldReport = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Lapangan',
            'report_date' => Carbon::now()->subDays(10),
        ]);

        $latestReport = CertificationReport::create([
            'certification_id' => $certification->id,
            'report_type' => 'Laboratorium',
            'report_date' => Carbon::now()->subDays(5),
        ]);

        // Memverifikasi bahwa method mengembalikan tanggal laporan terbaru
        $expectedDate = $latestReport->report_date->format('d M Y');
        $this->assertEquals($expectedDate, $certification->latest_report_date);
    }

    /**
     * Test: Method getLatestReportDate mengembalikan null jika tidak ada laporan
     * 
     * Menguji bahwa method mengembalikan null ketika certification belum memiliki laporan
     */
    public function test_get_latest_report_date_returns_null_when_no_reports(): void
    {
        // Membuat certification tanpa reports
        $certification = Certification::create([
            'certification_status' => 'dalam_proses',
        ]);

        // Memverifikasi bahwa method mengembalikan null
        $this->assertNull($certification->latest_report_date);
    }
}








