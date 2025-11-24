<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'primary_phone_is_whatsapp' => $this->boolean('primary_phone_is_whatsapp'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $statusOptions = implode(',', array_keys(Contact::STATUSES));
        $typeOptions = implode(',', array_keys(Contact::CONTACT_TYPES));

        return [
            'full_name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:' . $statusOptions,
            'contact_type' => 'required|in:' . $typeOptions,
            'organization' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:50',
            'primary_phone' => 'required|string|max:30',
            'primary_phone_is_whatsapp' => 'boolean',
            'secondary_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}







