<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PlantNote;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model PlantNote
 * 
 * Test ini menguji semua method dan relasi yang ada di model PlantNote
 */
class PlantNoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat plant note baru dengan field sesuai input
     * 
     * Menguji bahwa plant note dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_plant_note_with_all_fields(): void
    {
        // Menyiapkan data plant
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        // Menyiapkan data note untuk diuji
        $noteData = [
            'plant_id' => $plant->id,
            'description' => 'Catatan tentang pertumbuhan tanaman',
            'note_date' => Carbon::now(),
            'keywords' => 'pertumbuhan, sehat',
        ];

        // Membuat plant note baru
        $note = PlantNote::create($noteData);

        // Memverifikasi bahwa plant note berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('plant_notes', [
            'id' => $note->id,
            'plant_id' => $plant->id,
            'description' => 'Catatan tentang pertumbuhan tanaman',
        ]);
    }

    /**
     * Test: Relasi plant mengembalikan plant yang memiliki note ini
     * 
     * Menguji bahwa relasi belongs-to antara plant note dan plant berfungsi
     */
    public function test_plant_relationship(): void
    {
        // Membuat plant dan plant note
        $plant = Plant::create([
            'name' => 'Padi Varietas A',
            'variety' => 'Varietas A',
        ]);

        $note = PlantNote::create([
            'plant_id' => $plant->id,
            'description' => 'Catatan tentang pertumbuhan tanaman',
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plant->id, $note->plant->id);
    }
}








