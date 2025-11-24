<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_create_form(): void
    {
        $user = User::factory()->create([
            'role' => 'petugas_gudang',
        ]);

        $response = $this->actingAs($user)->get(route('contacts.create'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_new_contact(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('contacts.store'), [
            'full_name' => 'John Doe',
            'status' => Contact::STATUS_ACTIVE,
            'contact_type' => 'petani',
            'organization' => 'Kelompok Tani Maju',
            'position' => 'Ketua',
            'nip' => '19781212 201001 1 001',
            'primary_phone' => '08123456789',
            'primary_phone_is_whatsapp' => true,
            'secondary_phone' => '08987654321',
            'email' => 'john@example.com',
            'address' => 'Jl. Contoh No. 123',
            'province' => 'Sumatera Barat',
            'city' => 'Padang',
            'district' => 'Padang Barat',
            'village' => 'Kampung Dalam',
            'notes' => 'Kontak dapat dihubungi di luar jam kerja.',
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'full_name' => 'John Doe',
            'contact_type' => 'petani',
        ]);

        $contact = Contact::where('full_name', 'John Doe')->first();

        $this->assertNotNull($contact);
        $this->assertNotNull($contact->photo_path);
        Storage::disk('public')->assertExists($contact->photo_path);
    }
}







