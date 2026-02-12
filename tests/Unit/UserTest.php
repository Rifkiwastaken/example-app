<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\PlantingLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Unit Test untuk Model User
 * 
 * Test ini menguji semua method dan relasi yang ada di model User
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat user baru dengan field sesuai input
     * 
     * Menguji bahwa user dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_user_with_all_fields(): void
    {
        // Menyiapkan data user untuk diuji
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'full_name' => 'John Doe',
            'status' => 'active',
            'contact_type' => 'internal',
            'organization' => 'BPSB',
            'position' => 'Kepala Seksi',
            'nip' => '123456789',
            'primary_phone' => '081234567890',
            'address' => 'Jl. Test No. 123',
        ];

        // Membuat user baru
        $user = User::create($userData);

        // Memverifikasi bahwa user berhasil dibuat dengan field sesuai input (primary key: user_id)
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin',
            'full_name' => 'John Doe',
        ]);
    }

    /**
     * Test: Method getRoleLabel mengembalikan label role dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap role memiliki label yang sesuai
     */
    public function test_get_role_label_returns_indonesian_label(): void
    {
        // Membuat user dengan role admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $this->assertEquals('Admin/Kepala Seksi', $admin->role_label);

        // Membuat user dengan role kepala_satuan_tugas
        $kepalaSatuanTugas = User::create([
            'name' => 'Kepala Satuan Tugas',
            'email' => 'kepala@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $this->assertEquals('Kepala Satuan Tugas/Manajemen Penanaman', $kepalaSatuanTugas->role_label);

        // Membuat user dengan role petugas_sertifikasi
        $petugasSertifikasi = User::create([
            'name' => 'Petugas Sertifikasi',
            'email' => 'sertifikasi@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_sertifikasi',
        ]);
        $this->assertEquals('Petugas Sertifikasi', $petugasSertifikasi->role_label);

        // Membuat user dengan role petugas_gudang
        $petugasGudang = User::create([
            'name' => 'Petugas Gudang',
            'email' => 'gudang@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_gudang',
        ]);
        $this->assertEquals('Petugas Gudang', $petugasGudang->role_label);

        // Membuat user dengan role petugas_bbi
        $petugasBbi = User::create([
            'name' => 'Petugas BBI',
            'email' => 'bbi@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_bbi',
        ]);
        $this->assertEquals('Petugas BBI', $petugasBbi->role_label);

        // Membuat user dengan role penangkar
        $penangkar = User::create([
            'name' => 'Penangkar',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $this->assertEquals('Penangkar', $penangkar->role_label);
    }

    /**
     * Test: Method getRoles mengembalikan array semua role yang tersedia
     * 
     * Menguji bahwa method static getRoles mengembalikan semua role dengan labelnya
     */
    public function test_get_roles_returns_all_available_roles(): void
    {
        // Memanggil method static getRoles
        $roles = User::getRoles();

        // Memverifikasi bahwa method mengembalikan array
        $this->assertIsArray($roles);

        // Memverifikasi bahwa semua role yang diharapkan ada dalam array
        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('kepala_satuan_tugas', $roles);
        $this->assertArrayHasKey('petugas_sertifikasi', $roles);
        $this->assertArrayHasKey('petugas_gudang', $roles);
        $this->assertArrayHasKey('petugas_bbi', $roles);
        $this->assertArrayHasKey('penangkar', $roles);

        // Memverifikasi bahwa label sesuai dengan yang diharapkan
        $this->assertEquals('Admin/Kepala Seksi', $roles['admin']);
    }

    /**
     * Test: Method isAdmin mengembalikan true jika user adalah admin
     * 
     * Menguji bahwa method isAdmin dapat mengidentifikasi user admin dengan benar
     */
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        // Membuat user dengan role admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Memverifikasi bahwa isAdmin mengembalikan true
        $this->assertTrue($admin->isAdmin());

        // Membuat user dengan role bukan admin
        $nonAdmin = User::create([
            'name' => 'Penangkar User',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);

        // Memverifikasi bahwa isAdmin mengembalikan false
        $this->assertFalse($nonAdmin->isAdmin());
    }

    /**
     * Test: Method hasAccessTo mengembalikan true jika user memiliki akses ke modul
     * 
     * Menguji bahwa method hasAccessTo dapat memeriksa akses user ke modul tertentu
     */
    public function test_has_access_to_returns_true_when_user_has_access(): void
    {
        // Admin memiliki akses ke semua modul
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $this->assertTrue($admin->hasAccessTo('penanaman'));
        $this->assertTrue($admin->hasAccessTo('sertifikasi'));
        $this->assertTrue($admin->hasAccessTo('gudang'));
        $this->assertTrue($admin->hasAccessTo('penjualan'));

        // Kepala satuan tugas memiliki akses ke modul penanaman
        $kepalaSatuanTugas = User::create([
            'name' => 'Kepala Satuan Tugas',
            'email' => 'kepala@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $this->assertTrue($kepalaSatuanTugas->hasAccessTo('penanaman'));
        $this->assertFalse($kepalaSatuanTugas->hasAccessTo('sertifikasi'));

        // Petugas sertifikasi memiliki akses ke modul sertifikasi
        $petugasSertifikasi = User::create([
            'name' => 'Petugas Sertifikasi',
            'email' => 'sertifikasi@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_sertifikasi',
        ]);
        $this->assertTrue($petugasSertifikasi->hasAccessTo('sertifikasi'));
        $this->assertFalse($petugasSertifikasi->hasAccessTo('penanaman'));

        // Petugas gudang memiliki akses ke modul gudang
        $petugasGudang = User::create([
            'name' => 'Petugas Gudang',
            'email' => 'gudang@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_gudang',
        ]);
        $this->assertTrue($petugasGudang->hasAccessTo('gudang'));
        $this->assertFalse($petugasGudang->hasAccessTo('penjualan'));

        // Petugas BBI memiliki akses ke modul penjualan
        $petugasBbi = User::create([
            'name' => 'Petugas BBI',
            'email' => 'bbi@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_bbi',
        ]);
        $this->assertTrue($petugasBbi->hasAccessTo('penjualan'));
        $this->assertFalse($petugasBbi->hasAccessTo('gudang'));

        // Penangkar memiliki akses ke modul penanaman
        $penangkar = User::create([
            'name' => 'Penangkar',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $this->assertTrue($penangkar->hasAccessTo('penanaman'));
        $this->assertFalse($penangkar->hasAccessTo('sertifikasi'));
    }

    /**
     * Test: Relasi managedPlantingLocations mengembalikan planting locations yang dikelola user
     * 
     * Menguji bahwa relasi many-to-many antara user dan planting location sebagai manager berfungsi
     */
    public function test_managed_planting_locations_relationship(): void
    {
        // Membuat user dan planting location
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $plantingLocation = PlantingLocation::create([
            'name' => 'Test Location',
        ]);

        // Menghubungkan user sebagai manager ke planting location (gunakan primary key planting_location_id)
        $user->managedPlantingLocations()->attach($plantingLocation->planting_location_id);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($user->managedPlantingLocations->contains($plantingLocation));
        $this->assertEquals(1, $user->managedPlantingLocations->count());
    }

    /**
     * Test: Relasi workedPlantingLocations mengembalikan planting locations tempat user bekerja
     * 
     * Menguji bahwa relasi many-to-many antara user dan planting location sebagai worker berfungsi
     */
    public function test_worked_planting_locations_relationship(): void
    {
        // Membuat user dan planting location
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $plantingLocation = PlantingLocation::create([
            'name' => 'Test Location',
        ]);

        // Menghubungkan user sebagai worker ke planting location (gunakan primary key planting_location_id)
        $user->workedPlantingLocations()->attach($plantingLocation->planting_location_id);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($user->workedPlantingLocations->contains($plantingLocation));
        $this->assertEquals(1, $user->workedPlantingLocations->count());
    }

    /**
     * Test: Method assignedPlantingLocations mengembalikan semua planting locations yang ditugaskan ke user
     * 
     * Menguji bahwa method dapat menggabungkan managed dan worked planting locations
     */
    public function test_assigned_planting_locations_returns_all_assigned_locations(): void
    {
        // Membuat user dan beberapa planting locations
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $managedLocation = PlantingLocation::create(['name' => 'Managed Location']);
        $workedLocation = PlantingLocation::create(['name' => 'Worked Location']);

        // Menghubungkan user sebagai manager dan worker (gunakan primary key planting_location_id)
        $user->managedPlantingLocations()->attach($managedLocation->planting_location_id);
        $user->workedPlantingLocations()->attach($workedLocation->planting_location_id);

        // Memanggil method assignedPlantingLocations
        $assignedLocations = $user->assignedPlantingLocations();

        // Memverifikasi bahwa semua location yang ditugaskan dikembalikan
        $this->assertTrue($assignedLocations->contains($managedLocation));
        $this->assertTrue($assignedLocations->contains($workedLocation));
        $this->assertEquals(2, $assignedLocations->count());
    }

    /**
     * Test: Method isAssignedToPlantingLocation mengembalikan true jika user ditugaskan ke location
     * 
     * Menguji bahwa method dapat memeriksa apakah user ditugaskan ke planting location tertentu
     */
    public function test_is_assigned_to_planting_location_returns_true_when_assigned(): void
    {
        // Membuat admin user (selalu memiliki akses)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $location = PlantingLocation::create(['name' => 'Test Location']);
        $this->assertTrue($admin->isAssignedToPlantingLocation($location));

        // Membuat kepala satuan tugas dan menghubungkannya sebagai manager
        $kepalaSatuanTugas = User::create([
            'name' => 'Kepala Satuan Tugas',
            'email' => 'kepala@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $location2 = PlantingLocation::create(['name' => 'Test Location 2']);
        $kepalaSatuanTugas->managedPlantingLocations()->attach($location2->planting_location_id);
        $this->assertTrue($kepalaSatuanTugas->isAssignedToPlantingLocation($location2));

        // Membuat penangkar dan menghubungkannya sebagai worker
        $penangkar = User::create([
            'name' => 'Penangkar',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $location3 = PlantingLocation::create(['name' => 'Test Location 3']);
        $penangkar->workedPlantingLocations()->attach($location3->planting_location_id);
        $this->assertTrue($penangkar->isAssignedToPlantingLocation($location3));

        // User dengan role yang tidak memiliki akses
        $petugasSertifikasi = User::create([
            'name' => 'Petugas Sertifikasi',
            'email' => 'sertifikasi@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_sertifikasi',
        ]);
        $this->assertFalse($petugasSertifikasi->isAssignedToPlantingLocation($location));
    }

    /**
     * Test: Method canManagePlantingLocation mengembalikan true jika user dapat mengelola location
     * 
     * Menguji bahwa method dapat memeriksa apakah user dapat mengedit/hapus planting location
     */
    public function test_can_manage_planting_location_returns_true_when_user_can_manage(): void
    {
        // Admin selalu dapat mengelola
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $location = PlantingLocation::create(['name' => 'Test Location']);
        $this->assertTrue($admin->canManagePlantingLocation($location));

        // Kepala satuan tugas yang ditugaskan dapat mengelola
        $kepalaSatuanTugas = User::create([
            'name' => 'Kepala Satuan Tugas',
            'email' => 'kepala@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $location2 = PlantingLocation::create(['name' => 'Test Location 2']);
        $kepalaSatuanTugas->managedPlantingLocations()->attach($location2->planting_location_id);
        $this->assertTrue($kepalaSatuanTugas->canManagePlantingLocation($location2));

        // Penangkar tidak dapat mengelola meskipun ditugaskan
        $penangkar = User::create([
            'name' => 'Penangkar',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $location3 = PlantingLocation::create(['name' => 'Test Location 3']);
        $penangkar->workedPlantingLocations()->attach($location3->planting_location_id);
        $this->assertFalse($penangkar->canManagePlantingLocation($location3));
    }

    /**
     * Test: Method canAddDataInPelaporan mengembalikan true jika user dapat menambah data
     * 
     * Menguji bahwa method dapat memeriksa apakah user dapat menambah data di tab pelaporan
     */
    public function test_can_add_data_in_pelaporan_returns_true_when_user_can_add(): void
    {
        // Admin selalu dapat menambah data
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $location = PlantingLocation::create(['name' => 'Test Location']);
        $this->assertTrue($admin->canAddDataInPelaporan($location));

        // Kepala satuan tugas yang ditugaskan dapat menambah data
        $kepalaSatuanTugas = User::create([
            'name' => 'Kepala Satuan Tugas',
            'email' => 'kepala@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $location2 = PlantingLocation::create(['name' => 'Test Location 2']);
        $kepalaSatuanTugas->managedPlantingLocations()->attach($location2->planting_location_id);
        $this->assertTrue($kepalaSatuanTugas->canAddDataInPelaporan($location2));

        // Penangkar yang ditugaskan dapat menambah data
        $penangkar = User::create([
            'name' => 'Penangkar',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $location3 = PlantingLocation::create(['name' => 'Test Location 3']);
        $penangkar->workedPlantingLocations()->attach($location3->planting_location_id);
        $this->assertTrue($penangkar->canAddDataInPelaporan($location3));

        // User dengan role yang tidak memiliki akses
        $petugasSertifikasi = User::create([
            'name' => 'Petugas Sertifikasi',
            'email' => 'sertifikasi@test.com',
            'password' => Hash::make('password'),
            'role' => 'petugas_sertifikasi',
        ]);
        $this->assertFalse($petugasSertifikasi->canAddDataInPelaporan($location));
    }

    /**
     * Test: Method canManageDataInPelaporan mengembalikan true jika user dapat mengelola data
     * 
     * Menguji bahwa method dapat memeriksa apakah user dapat mengedit/hapus data di tab pelaporan
     */
    public function test_can_manage_data_in_pelaporan_returns_true_when_user_can_manage(): void
    {
        // Admin selalu dapat mengelola data
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $location = PlantingLocation::create(['name' => 'Test Location']);
        $this->assertTrue($admin->canManageDataInPelaporan($location));

        // Kepala satuan tugas yang ditugaskan dapat mengelola data
        $kepalaSatuanTugas = User::create([
            'name' => 'Kepala Satuan Tugas',
            'email' => 'kepala@test.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_satuan_tugas',
        ]);
        $location2 = PlantingLocation::create(['name' => 'Test Location 2']);
        $kepalaSatuanTugas->managedPlantingLocations()->attach($location2->planting_location_id);
        $this->assertTrue($kepalaSatuanTugas->canManageDataInPelaporan($location2));

        // Penangkar tidak dapat mengelola data meskipun ditugaskan
        $penangkar = User::create([
            'name' => 'Penangkar',
            'email' => 'penangkar@test.com',
            'password' => Hash::make('password'),
            'role' => 'penangkar',
        ]);
        $location3 = PlantingLocation::create(['name' => 'Test Location 3']);
        $penangkar->workedPlantingLocations()->attach($location3->planting_location_id);
        $this->assertFalse($penangkar->canManageDataInPelaporan($location3));
    }
}

