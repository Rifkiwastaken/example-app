<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model Location
 *
 * Menguji semua method dan relasi model Location:
 * - Primary key: location_id (HasCustomId)
 * - Relasi: users() hasMany User
 * - Accessor: type_label (getTypeLabelAttribute)
 * - Static: getTypes()
 */
class LocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Location dapat dibuat dengan field fillable
     *
     * Memastikan create dengan name, city, district, type, dll tersimpan dengan benar
     */
    public function test_can_create_location_with_fillable_fields(): void
    {
        $location = Location::create([
            'name' => 'Lahan A',
            'city' => 'Padang',
            'district' => 'Koto Tangah',
            'type' => 'lokasi_lahan',
            'description' => 'Deskripsi lahan',
        ]);

        $this->assertDatabaseHas('locations', [
            'location_id' => $location->location_id,
            'name' => 'Lahan A',
            'city' => 'Padang',
            'type' => 'lokasi_lahan',
        ]);
    }

    /**
     * Test: Relasi users() mengembalikan daftar user yang punya location_id ini
     *
     * Memastikan hasMany(User::class, 'location_id', 'location_id') berfungsi
     */
    public function test_users_relationship_returns_users_for_this_location(): void
    {
        $location = Location::create([
            'name' => 'Kantor',
            'city' => 'Padang',
            'district' => 'Padang Barat',
            'type' => 'lokasi_kantor_utama',
        ]);

        $user1 = User::create([
            'name' => 'User Satu',
            'email' => 'user1@test.com',
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'location_id' => $location->location_id,
        ]);
        $user2 = User::create([
            'name' => 'User Dua',
            'email' => 'user2@test.com',
            'password' => bcrypt('secret'),
            'role' => 'penangkar',
            'location_id' => $location->location_id,
        ]);

        $users = $location->users;

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains($user1));
        $this->assertTrue($users->contains($user2));
    }

    /**
     * Test: type_label accessor mengembalikan label Indonesia untuk setiap type
     *
     * Menguji getTypeLabelAttribute() untuk lokasi_lahan, lokasi_sertifikasi, lokasi_gudang, lokasi_kantor_utama
     */
    public function test_type_label_returns_indonesian_label_for_each_type(): void
    {
        $lahan = Location::create([
            'name' => 'L',
            'city' => 'P',
            'district' => 'D',
            'type' => 'lokasi_lahan',
        ]);
        $this->assertSame('Lokasi Lahan', $lahan->type_label);

        $sertifikasi = Location::create([
            'name' => 'S',
            'city' => 'P',
            'district' => 'D',
            'type' => 'lokasi_sertifikasi',
        ]);
        $this->assertSame('Lokasi Sertifikasi', $sertifikasi->type_label);

        $gudang = Location::create([
            'name' => 'G',
            'city' => 'P',
            'district' => 'D',
            'type' => 'lokasi_gudang',
        ]);
        $this->assertSame('Lokasi Gudang', $gudang->type_label);

        $kantor = Location::create([
            'name' => 'K',
            'city' => 'P',
            'district' => 'D',
            'type' => 'lokasi_kantor_utama',
        ]);
        $this->assertSame('Lokasi Kantor Utama', $kantor->type_label);
    }

    /**
     * Test: type_label mengembalikan type as-is untuk value yang tidak dikenal
     *
     * Default case di match() mengembalikan $this->type
     */
    public function test_type_label_returns_raw_type_for_unknown(): void
    {
        $location = Location::create([
            'name' => 'X',
            'city' => 'P',
            'district' => 'D',
            'type' => 'unknown_type',
        ]);
        $this->assertSame('unknown_type', $location->type_label);
    }

    /**
     * Test: getTypes() mengembalikan array semua tipe dengan label
     *
     * Memastikan method static getTypes() berisi 4 pasangan key => label
     */
    public function test_get_types_returns_all_available_types(): void
    {
        $types = Location::getTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey('lokasi_lahan', $types);
        $this->assertArrayHasKey('lokasi_sertifikasi', $types);
        $this->assertArrayHasKey('lokasi_gudang', $types);
        $this->assertArrayHasKey('lokasi_kantor_utama', $types);
        $this->assertSame('Lokasi Lahan', $types['lokasi_lahan']);
    }
}
