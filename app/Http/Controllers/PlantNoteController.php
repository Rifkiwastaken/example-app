<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantNoteController extends Controller
{
    public function index(Plant $plant)
    {
        $notes = Attachment::with('creator')
            ->forPlant($plant->getKey())
            ->orderByDesc('attachment_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('planting.plants.notes.index', compact('plant', 'notes'));
    }

    public function create(Plant $plant)
    {
        return view('planting.plants.notes.create', compact('plant'));
    }

    public function store(Request $request, Plant $plant)
    {
        $attachment = $this->storeAttachment($request, [
            'module' => Attachment::MODULE_PLANT,
            'seed_varieties_id' => $plant->getKey(),
            'planting_location_id' => null,
            'stock_id' => null,
        ]);

        return redirect()->route('plants.notes.index', $plant)
            ->with('success', 'Lampiran berhasil ditambahkan');
    }

    public function show(Plant $plant, Attachment $note)
    {
        $this->ensureBelongsToPlant($plant, $note);
        $note->load('creator');

        return view('planting.plants.notes.show', compact('plant', 'note'));
    }

    public function edit(Plant $plant, Attachment $note)
    {
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit lampiran.');
        }

        $this->ensureBelongsToPlant($plant, $note);

        return view('planting.plants.notes.edit', compact('plant', 'note'));
    }

    public function update(Request $request, Plant $plant, Attachment $note)
    {
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit lampiran.');
        }

        $this->ensureBelongsToPlant($plant, $note);
        $this->updateAttachment($request, $note);

        return redirect()->route('plants.notes.index', $plant)
            ->with('success', 'Lampiran berhasil diperbarui');
    }

    public function destroy(Plant $plant, Attachment $note)
    {
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus lampiran.');
        }

        $this->ensureBelongsToPlant($plant, $note);
        if ($note->file_path && Storage::disk('public')->exists($note->file_path)) {
            Storage::disk('public')->delete($note->file_path);
        }
        $note->delete();

        return redirect()->route('plants.notes.index', $plant)
            ->with('success', 'Lampiran berhasil dihapus');
    }

    protected function storeAttachment(Request $request, array $context): Attachment
    {
        $data = $this->validated($request, true);
        $file = $request->file('file');

        return Attachment::create(array_merge($context, [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'attachment_date' => $data['attachment_date'],
            'created_by' => auth()->user()->user_id,
            'file_path' => $file ? $file->store('attachments', 'public') : null,
            'file_name' => $file?->getClientOriginalName(),
            'file_size' => $file?->getSize(),
            'mime_type' => $file?->getMimeType(),
        ]));
    }

    protected function updateAttachment(Request $request, Attachment $note): void
    {
        $data = $this->validated($request, false);
        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'attachment_date' => $data['attachment_date'],
        ];
        if ($request->hasFile('file')) {
            if ($note->file_path && Storage::disk('public')->exists($note->file_path)) {
                Storage::disk('public')->delete($note->file_path);
            }
            $file = $request->file('file');
            $payload['file_path'] = $file->store('attachments', 'public');
            $payload['file_name'] = $file->getClientOriginalName();
            $payload['file_size'] = $file->getSize();
            $payload['mime_type'] = $file->getMimeType();
        }
        $note->update($payload);
    }

    protected function validated(Request $request, bool $fileRequired): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'attachment_date' => 'required|date',
            'description' => 'nullable|string',
            'file' => ($fileRequired ? 'required' : 'nullable').'|file|max:10240',
        ]);
    }

    protected function ensureBelongsToPlant(Plant $plant, Attachment $note): void
    {
        if ($note->module !== Attachment::MODULE_PLANT || $note->seed_varieties_id !== $plant->getKey()) {
            abort(404);
        }
    }
}
