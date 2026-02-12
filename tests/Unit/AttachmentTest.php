<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attachment;
use App\Models\PlantingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Attachment
 * 
 * Test ini menguji semua method dan relasi yang ada di model Attachment
 */
class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat attachment baru dengan field sesuai input
     * 
     * Menguji bahwa attachment dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_attachment_with_all_fields(): void
    {
        // Menyiapkan data planting location dan user
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        // Menyiapkan data attachment untuk diuji
        $attachmentData = [
            'planting_location_id' => $plantingLocation->id,
            'title' => 'Dokumen Test',
            'file_path' => 'attachments/test.pdf',
            'file_name' => 'test.pdf',
            'attachment_date' => Carbon::now(),
            'created_by' => $user->id,
        ];

        // Membuat attachment baru
        $attachment = Attachment::create($attachmentData);

        // Memverifikasi bahwa attachment berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'title' => 'Dokumen Test',
            'file_name' => 'test.pdf',
        ]);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara attachment dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan attachment
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Utama',
        ]);

        $attachment = Attachment::create([
            'planting_location_id' => $plantingLocation->id,
            'title' => 'Dokumen Test',
            'file_path' => 'attachments/test.pdf',
            'attachment_date' => Carbon::now(),
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $attachment->plantingLocation->id);
    }

    /**
     * Test: Relasi creator mengembalikan user yang membuat attachment
     * 
     * Menguji bahwa relasi belongs-to antara attachment dan user (creator) berfungsi
     */
    public function test_creator_relationship(): void
    {
        // Membuat user dan attachment
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $attachment = Attachment::create([
            'title' => 'Dokumen Test',
            'file_path' => 'attachments/test.pdf',
            'attachment_date' => Carbon::now(),
            'created_by' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $attachment->creator->id);
    }
}








