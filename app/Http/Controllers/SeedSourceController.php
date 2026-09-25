<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\SeedSource;
use Illuminate\Http\Request;

class SeedSourceController extends Controller
{
    public function index(Plant $plant)
    {
        $plant->load('satuanStok');
        $seedSources = $plant->seedSources()->with(['plantings.field.plantingLocation'])->latest()->paginate(15);

        return view('planting.plants.seed-sources.index', compact('plant', 'seedSources'));
    }

    public function indexJson(Plant $plant)
    {
        $plant->load('satuanStok');
        $unit = $plant->satuanStok?->code ?: 'kg';

        return response()->json(
            $plant->seedSources()
                ->where('quantity_kg', '>', 0)
                ->orderBy('origin_lot_number')
                ->get()
                ->map(fn ($source) => [
                    'seed_source_id' => $source->seed_source_id,
                    'quantity' => (float) $source->quantity_kg,
                    'unit' => $unit,
                    'label' => $source->seedClassLabel().' · '.$source->origin_lot_number.' · sisa '.number_format((float) $source->quantity_kg, 2).' '.$unit,
                ])
        );
    }

    public function create(Plant $plant)
    {
        $plant->load('satuanStok');

        return view('planting.plants.seed-sources.create', compact('plant'));
    }

    public function store(Request $request, Plant $plant)
    {
        $data = $this->validatedData($request);
        $data['seed_varieties_id'] = $plant->getKey();
        unset($data['file']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('seed-sources', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        }

        $data['quantity_awal'] = $data['quantity_kg'];
        SeedSource::create($data);

        return redirect()->route('plants.seed-sources.index', $plant)
            ->with('success', 'Benih sumber berhasil ditambahkan');
    }

    public function show(Plant $plant, SeedSource $seed_source)
    {
        $this->ensureBelongsToPlant($plant, $seed_source);
        $plant->load(['satuanStok', 'satuanTanam']);
        $seed_source->load(['plantings.field.plantingLocation', 'plantings.postHarvests']);

        return view('planting.plants.seed-sources.show', compact('plant', 'seed_source'));
    }

    public function edit(Plant $plant, SeedSource $seed_source)
    {
        $this->ensureBelongsToPlant($plant, $seed_source);
        $plant->load('satuanStok');

        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit benih sumber.');
        }

        return view('planting.plants.seed-sources.edit', compact('plant', 'seed_source'));
    }

    public function update(Request $request, Plant $plant, SeedSource $seed_source)
    {
        $this->ensureBelongsToPlant($plant, $seed_source);

        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit benih sumber.');
        }

        $data = $this->validatedData($request);
        unset($data['file']);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('seed-sources', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        }
        $seed_source->update($data);

        return redirect()->route('plants.seed-sources.index', $plant)
            ->with('success', 'Benih sumber berhasil diperbarui');
    }

    public function destroy(Plant $plant, SeedSource $seed_source)
    {
        $this->ensureBelongsToPlant($plant, $seed_source);

        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus benih sumber.');
        }

        $seed_source->delete();

        return redirect()->route('plants.seed-sources.index', $plant)
            ->with('success', 'Benih sumber berhasil dihapus');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'seed_class' => 'required|in:BS,FS,SS,ES',
            'origin_lot_number' => 'required|string|max:50',
            'origin_producer' => 'required|string|max:150',
            'quantity_kg' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ], [
            'seed_class.required' => 'Kelas benih wajib dipilih.',
            'origin_lot_number.required' => 'Nomor lot asal wajib diisi.',
            'origin_producer.required' => 'Produsen asal wajib diisi.',
            'quantity_kg.required' => 'Jumlah benih wajib diisi.',
        ]);
    }

    protected function ensureBelongsToPlant(Plant $plant, SeedSource $seedSource): void
    {
        if ($seedSource->seed_varieties_id !== $plant->getKey()) {
            abort(404);
        }
    }
}
