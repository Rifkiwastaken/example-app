<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Contact::query();

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $contactType = $request->input('contact_type');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('organization', 'like', '%' . $search . '%')
                    ->orWhere('primary_phone', 'like', '%' . $search . '%')
                    ->orWhere('secondary_phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($status && array_key_exists($status, Contact::STATUSES)) {
            $query->where('status', $status);
        }

        if ($contactType && array_key_exists($contactType, Contact::CONTACT_TYPES)) {
            $query->where('contact_type', $contactType);
        }

        $contacts = $query
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('contacts.index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'contact_type' => $contactType,
            ],
            'statuses' => Contact::STATUSES,
            'contactTypes' => Contact::CONTACT_TYPES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->ensureAdmin();

        return view('contacts.create', [
            'statuses' => Contact::STATUSES,
            'contactTypes' => Contact::CONTACT_TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('contacts', 'public');
        }

        unset($data['photo']);

        $contact = Contact::create($data);

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Kontak berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): View
    {
        $relatedPlantingLocations = collect();

        if (class_exists(\App\Models\PlantingLocation::class) && Schema::hasColumn('planting_locations', 'responsible_contact_id')) {
            $relatedPlantingLocations = \App\Models\PlantingLocation::query()
                ->with('baseLocation')
                ->where('responsible_contact_id', $contact->id)
                ->orderByDesc('updated_at')
                ->get();
        }

        return view('contacts.show', [
            'contact' => $contact,
            'relatedPlantingLocations' => $relatedPlantingLocations,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        $this->ensureAdmin();

        return view('contacts.edit', [
            'contact' => $contact,
            'statuses' => Contact::STATUSES,
            'contactTypes' => Contact::CONTACT_TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($contact->photo_path && Storage::disk('public')->exists($contact->photo_path)) {
                Storage::disk('public')->delete($contact->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('contacts', 'public');
        }

        unset($data['photo']);

        $contact->update($data);

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Kontak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $this->ensureAdmin();

        if ($contact->photo_path && Storage::disk('public')->exists($contact->photo_path)) {
            Storage::disk('public')->delete($contact->photo_path);
        }

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Kontak berhasil dihapus.');
    }

    /**
     * Ensure the authenticated user has admin privileges.
     */
    protected function ensureAdmin(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }
}


