<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contactType = $this->faker->randomElement(array_keys(Contact::CONTACT_TYPES));
        $status = $this->faker->randomElement(array_keys(Contact::STATUSES));

        return [
            'full_name' => $this->faker->name(),
            'photo_path' => null,
            'status' => $status,
            'contact_type' => $contactType,
            'organization' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'nip' => $this->faker->optional()->numerify('1978##########'),
            'primary_phone' => $this->faker->numerify('08##########'),
            'primary_phone_is_whatsapp' => $this->faker->boolean(70),
            'secondary_phone' => $this->faker->optional()->numerify('08##########'),
            'email' => $this->faker->optional()->safeEmail(),
            'address' => $this->faker->address(),
            'province' => $this->faker->state(),
            'city' => $this->faker->city(),
            'district' => $this->faker->citySuffix(),
            'village' => $this->faker->streetName(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}







