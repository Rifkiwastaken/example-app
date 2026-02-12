<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantingLocationNote;
use App\Models\PlantingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model PlantingLocationNote
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantingLocationNote
 */
class PlantingLocationNoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat planting location note baru dengan field sesuai input
     * 
     * Menguji bahwa planting location note dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_planting_location_note_with_all_fields(): void
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

        // Menyiapkan data note untuk diuji
        $noteData = [
            'planting_location_id' => $plantingLocation->id,
            'title' => 'Catatan Test',
            'description' => 'Ini adalah catatan test',
            'note_date' => Carbon::now(),
            'user_id' => $user->id,
        ];

        // Membuat note baru
        $note = PlantingLocationNote::create($noteData);

        // Memverifikasi bahwa note berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('planting_location_notes', [
            'id' => $note->id,
            'title' => 'Catatan Test',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test: Method markAsReadBy menandai note sebagai sudah dibaca oleh user
     * 
     * Menguji bahwa method dapat menandai note sebagai sudah dibaca
     */
    public function test_mark_as_read_by_marks_note_as_read(): void
    {
        // Membuat note dan user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $note = PlantingLocationNote::create([
            'title' => 'Catatan Test',
            'description' => 'Ini adalah catatan test',
            'note_date' => Carbon::now(),
        ]);

        // Memanggil method markAsReadBy
        $note->markAsReadBy($user->id);

        // Memverifikasi bahwa note ditandai sebagai sudah dibaca
        $this->assertTrue($note->fresh()->isReadBy($user->id));
    }

    /**
     * Test: Method isAssignedTo mengembalikan true jika note ditugaskan ke user
     * 
     * Menguji bahwa method dapat memeriksa apakah note ditugaskan ke user tertentu
     */
    public function test_is_assigned_to_returns_true_when_note_is_assigned(): void
    {
        // Membuat note dengan assigned_to
        $note = PlantingLocationNote::create([
            'title' => 'Catatan Test',
            'description' => 'Ini adalah catatan test',
            'note_date' => Carbon::now(),
            'assigned_to' => [1, 2, 3],
        ]);

        // Memverifikasi bahwa note ditugaskan ke user dengan id 1
        $this->assertTrue($note->isAssignedTo(1));
        $this->assertFalse($note->isAssignedTo(4));
    }
}








