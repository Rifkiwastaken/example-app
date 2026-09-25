<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantType;
use App\Models\PlantingLocation;
use App\Models\Planting;
use App\Models\SeedSource;
use App\Models\SeedUnit;
use App\Models\PlantingPostHarvest;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockPackaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlantController extends Controller
{
    /**
     * Map form start_method values to database ENUM values
     */
    private function mapStartMethod($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'Tanam Langsung' => 'tanam_langsung',
            'Mulai di baki semai' => 'baki_semai',
            'Pindahkan ke tanah' => 'pindahkan_ke_tanah',
            'Pindah tanaman (transplant)' => 'transplant',
            'Dalam pot (container)' => 'container',
            'Ditanam di baki semai' => 'ditanam_di_baki_semai',
            'Batang bawah/ tanaman induk' => 'batang_bawah',
            'Umbi' => 'umbi',
            'Sambung/okulasi' => 'sambung_okulasi',
            'Lainnya' => 'lainnya',
        ];
        
        // If value is already in ENUM format, return as is
        $validEnumValues = ['tanam_langsung', 'baki_semai', 'pindahkan_ke_tanah', 'transplant', 'container', 'ditanam_di_baki_semai', 'batang_bawah', 'umbi', 'sambung_okulasi', 'lainnya'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form germination_stage values to database ENUM values
     */
    private function mapGerminationStage($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'benih ditanam' => 'benih_ditanam',
            'perkecambahan' => 'perkecambahan',
            'bibit/ tunas muda' => 'bibit',
            'sudah ditanam' => 'sudah_ditanam',
            'fase vegetatif' => 'vegetatif',
            'berbunga' => 'berbunga',
            'pematangan buah' => 'pematangan_buah',
            'selesai' => 'selesai',
        ];
        
        $validEnumValues = ['benih_ditanam', 'perkecambahan', 'bibit', 'sudah_ditanam', 'vegetatif', 'berbunga', 'pematangan_buah', 'selesai'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form light_profile values to database ENUM values
     */
    private function mapLightProfile($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'sinar matahari penuh' => 'matahari_penuh',
            'sinar matahari penuh sebagian' => 'matahari_penuh_sebagian',
            'sinar matahari sebagian' => 'matahari_sebagian',
            'matahari hingga setengah teduh' => 'matahari_setengah_teduh',
            'setengah teduh' => 'setengah_teduh',
            'teduh sepenuhnya' => 'teduh_sepenuhnya',
        ];
        
        $validEnumValues = ['matahari_penuh', 'matahari_penuh_sebagian', 'matahari_sebagian', 'matahari_setengah_teduh', 'setengah_teduh', 'teduh_sepenuhnya'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form soil_condition values to database ENUM values
     */
    private function mapSoilCondition($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'tanah berkapur' => 'berkapur',
            'tanah liat' => 'liat',
            'tanah lempung' => 'lempung',
            'tanah gambut' => 'gambut',
            'tanah berpasir' => 'berpasir',
            'tanah lanau' => 'lanau',
        ];
        
        $validEnumValues = ['berkapur', 'liat', 'lempung', 'gambut', 'berpasir', 'lanau'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? null;
    }

    /**
     * Map form harvest_unit values to database ENUM values
     */
    private function mapHarvestUnit($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $mapping = [
            'jumlah' => 'satuan',
        ];
        
        $validEnumValues = ['ikat', 'barel', 'tandan', 'gantang', 'lusin', 'gram', 'batang', 'kilogram', 'kiloliter', 'liter', 'mililiter', 'satuan', 'ton'];
        if (in_array($value, $validEnumValues)) {
            return $value;
        }
        
        return $mapping[$value] ?? $value;
    }

    public function index(Request $request)
    {
        $query = Plant::with(['type', 'plantings.location']);

        // Search by nama tanaman, varietas, atau deskripsi
        if ($request->filled('search')) {
            $search = $request->input('search');
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('variety', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('type', function ($typeQuery) use ($like) {
                        $typeQuery->where('name', 'like', $like)
                            ->orWhere('category', 'like', $like);
                    });
            });
        }

        if ($request->filled('planting_location_id')) {
            $query->whereHas('plantings', fn ($q) => $q->where('planting_location_id', $request->planting_location_id));
        }
        if ($request->filled('category')) {
            $query->whereHas('type', fn ($q) => $q->whereRaw('LOWER(category) = ?', [mb_strtolower($request->category)]));
        }
        if ($request->filled('plant_name')) {
            $query->whereHas('type', fn ($q) => $q->where('name', $request->plant_name));
        }
        if ($request->filled('variety_id')) {
            $query->where('seed_varieties_id', $request->variety_id);
        }
        if ($request->filled('plant_type_id')) {
            $query->where('seed_commodity_id', $request->plant_type_id);
        }

        $plants = $query
            ->join('plant_commodities', 'plant_varieties.seed_commodity_id', '=', 'plant_commodities.seed_commodity_id')
            ->select('plant_varieties.*')
            ->orderBy('plant_commodities.category')
            ->orderBy('plant_commodities.name')
            ->orderBy('plant_varieties.variety')
            ->paginate(15)
            ->withQueryString();
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $categories = $types->pluck('category')->filter()->unique()->sort()->values();
        $plantNameOptions = $types
            ->when($request->filled('category'), fn ($rows) => $rows->filter(fn ($row) => strcasecmp((string) $row->category, $request->category) === 0))
            ->unique('name')
            ->sortBy('name')
            ->values();
        $varietyOptions = Plant::query()
            ->when($request->filled('category'), fn ($q) => $q->whereHas('type', fn ($t) => $t->whereRaw('LOWER(category) = ?', [mb_strtolower($request->category)])))
            ->when($request->filled('plant_name'), fn ($q) => $q->whereHas('type', fn ($t) => $t->where('name', $request->plant_name)))
            ->orderBy('variety')
            ->get();
        return view('planting/plants/index', compact('plants', 'types', 'locations', 'categories', 'plantNameOptions', 'varietyOptions'));
    }

    public function create()
    {
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $seedUnits = SeedUnit::orderBy('name')->get();
        return view('planting/plants/create', compact('types', 'locations', 'seedUnits'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'plant_type_id' => 'required|exists:plant_commodities,seed_commodity_id',
                'variety' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('plant_varieties', 'variety'),
                ],
                'description' => 'nullable|string',
                'days_to_emerge' => 'nullable|integer|min:0',
                'spacing_between_plants' => 'nullable|numeric|min:0',
                'spacing_between_rows' => 'nullable|numeric|min:0',
                'sowing_depth' => 'nullable|numeric|min:0',
                'avg_height' => 'nullable|numeric|min:0',
                'start_method' => 'nullable|string|max:255',
                'germination_stage' => 'nullable|string|max:255',
                'seeds_per_hole' => 'nullable|integer|min:1',
                'light_profile' => 'nullable|string|max:255',
                'soil_condition' => 'nullable|string|max:255',
                'planting_detail' => 'nullable|string',
                'pruning_detail' => 'nullable|string',
                'days_to_flower' => 'nullable|integer|min:0',
                'days_to_harvest' => 'nullable|integer|min:0',
                'harvest_window_days' => 'nullable|integer|min:0',
                'expected_loss_rate' => 'nullable|numeric|min:0|max:100',
                'harvest_unit' => 'nullable|string',
                'satuan_stok_id' => 'required|exists:seed_units,seed_unit_id',
                'satuan_tanam_id' => 'required|exists:seed_units,seed_unit_id',
                'satuan_panen_id' => 'required|exists:seed_units,seed_unit_id',
                'public_description' => 'nullable|string',
                'public_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
                'harga_jual' => 'required|numeric|min:0',
                'minimal_stok' => 'required|numeric|min:0',
            ], [
                'plant_type_id.required' => 'Nama tanaman wajib dipilih.',
                'variety.required' => 'Varietas wajib diisi.',
                'variety.unique' => 'Varietas ini sudah digunakan. Gunakan varietas lain.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
        
        try {
            $name = $this->composePlantDisplayName($request->input('variety'));
            
            $plantData = [
                'name' => trim($name),
                'plant_type_id' => $data['plant_type_id'] ?? null,
                'variety' => $data['variety'],
                'description' => $data['description'] ?? null,
                'days_to_emerge' => $data['days_to_emerge'] ?? null,
                'spacing_between_plants' => $data['spacing_between_plants'] ?? null,
                'spacing_between_rows' => $data['spacing_between_rows'] ?? null,
                'sowing_depth' => $data['sowing_depth'] ?? null,
                'avg_height' => $data['avg_height'] ?? null,
                'start_method' => $this->mapStartMethod($data['start_method'] ?? null),
                'germination_stage' => $this->mapGerminationStage($data['germination_stage'] ?? null),
                'seeds_per_hole' => $data['seeds_per_hole'] ?? null,
                'light_profile' => $this->mapLightProfile($data['light_profile'] ?? null),
                'soil_condition' => $this->mapSoilCondition($data['soil_condition'] ?? null),
                'planting_detail' => $data['planting_detail'] ?? null,
                'pruning_detail' => $data['pruning_detail'] ?? null,
                'days_to_flower' => $data['days_to_flower'] ?? null,
                'days_to_harvest' => $data['days_to_harvest'] ?? null,
                'harvest_window_days' => $data['harvest_window_days'] ?? null,
                'expected_loss_rate' => $data['expected_loss_rate'] ?? null,
                'harvest_unit' => $data['harvest_unit'] ?? null,
                'satuan_stok_id' => $data['satuan_stok_id'] ?? null,
                'satuan_tanam_id' => $data['satuan_tanam_id'] ?? null,
                'satuan_panen_id' => $data['satuan_panen_id'] ?? null,
                'harga_jual' => $data['harga_jual'] ?? null,
                'minimal_stok' => $data['minimal_stok'] ?? null,
                'public_description' => $data['public_description'] ?? null,
            ];
            
            if ($request->hasFile('public_photo')) {
                $plantData['public_photo_path'] = $request->file('public_photo')->store('plants/public', 'public');
            }
            
            $plant = Plant::create($plantData);
            
            return redirect()->route('plants.show', $plant)->with('success', 'Varietas tanaman berhasil ditambahkan');
        } catch (\Exception $e) {
            \Log::error('Error creating plant: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data tanaman: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Plant $plant)
    {
        $plant->load(['type', 'satuanStok', 'plantings.location', 'plantings.field', 'plantings.seedSource']);
        $seedUnits = \App\Models\SeedUnit::orderBy('name')->get();
        $stocks = Stock::where('seed_varieties_id', $plant->getKey())->get();
        $packagingIds = StockPackaging::whereIn('stok_benih_id', $stocks->pluck('id'))->pluck('id');
        $currentStock = (float) $stocks->where('status_stok', Stock::STATUS_SIAP)->sum('stok_saat_ini');
        $soldQty = (float) SaleItem::whereIn('stock_packaging_id', $packagingIds)->sum('quantity');
        $revenue = (float) SaleItem::whereIn('stock_packaging_id', $packagingIds)->sum('subtotal');

        return view('planting/plants/show', compact('plant', 'seedUnits', 'currentStock', 'soldQty', 'revenue'));
    }

    public function edit(Plant $plant)
    {
        // Prevent penangkar from editing plants
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data tanaman.');
        }
        
        $plant->load(['plantings']);
        $types = PlantType::orderBy('category')->orderBy('name')->get();
        $locations = PlantingLocation::orderBy('name')->get();
        $seedUnits = SeedUnit::orderBy('name')->get();
        return view('planting/plants/edit', compact('plant', 'types', 'locations', 'seedUnits'));
    }

    public function update(Request $request, Plant $plant)
    {
        // Prevent penangkar from updating plants
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data tanaman.');
        }
        
        $data = $request->validate([
            'plant_type_id' => 'nullable|exists:plant_commodities,seed_commodity_id',
            'variety' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plant_varieties', 'variety')->ignore($plant->getKey(), 'seed_varieties_id'),
            ],
            'description' => 'nullable|string',
            'days_to_emerge' => 'nullable|integer|min:0',
            'spacing_between_plants' => 'nullable|numeric|min:0',
            'spacing_between_rows' => 'nullable|numeric|min:0',
            'sowing_depth' => 'nullable|numeric|min:0',
            'avg_height' => 'nullable|numeric|min:0',
            'start_method' => 'nullable|string|max:255',
            'germination_stage' => 'nullable|string|max:255',
            'seeds_per_hole' => 'nullable|integer|min:1',
            'light_profile' => 'nullable|string|max:255',
            'soil_condition' => 'nullable|string|max:255',
            'planting_detail' => 'nullable|string',
            'pruning_detail' => 'nullable|string',
            'days_to_flower' => 'nullable|integer|min:0',
            'days_to_harvest' => 'nullable|integer|min:0',
            'harvest_window_days' => 'nullable|integer|min:0',
            'expected_loss_rate' => 'nullable|numeric|min:0|max:100',
            'harvest_unit' => 'nullable|string',
            'satuan_stok_id' => 'required|exists:seed_units,seed_unit_id',
            'satuan_tanam_id' => 'required|exists:seed_units,seed_unit_id',
            'satuan_panen_id' => 'required|exists:seed_units,seed_unit_id',
            'public_description' => 'nullable|string',
            'public_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'harga_jual' => 'required|numeric|min:0',
            'minimal_stok' => 'required|numeric|min:0',
        ], [
            'variety.required' => 'Varietas wajib diisi.',
            'variety.unique' => 'Varietas ini sudah digunakan. Gunakan varietas lain.',
            'satuan_stok_id.required' => 'Satuan stok wajib dipilih.',
            'satuan_tanam_id.required' => 'Satuan tanam wajib dipilih.',
            'satuan_panen_id.required' => 'Satuan panen wajib dipilih.',
            'harga_jual.required' => 'Harga satuan wajib diisi.',
            'minimal_stok.required' => 'Minimal stok wajib diisi.',
        ]);
        
        $name = $this->composePlantDisplayName($request->input('variety'), $plant->name);
        
        $plantData = [
            'name' => trim($name),
            'plant_type_id' => $data['plant_type_id'] ?? null,
            'variety' => $data['variety'],
            'description' => $data['description'] ?? null,
            'days_to_emerge' => $data['days_to_emerge'] ?? null,
            'spacing_between_plants' => $data['spacing_between_plants'] ?? null,
            'spacing_between_rows' => $data['spacing_between_rows'] ?? null,
            'sowing_depth' => $data['sowing_depth'] ?? null,
            'avg_height' => $data['avg_height'] ?? null,
            'start_method' => $this->mapStartMethod($data['start_method'] ?? null),
            'germination_stage' => $this->mapGerminationStage($data['germination_stage'] ?? null),
            'seeds_per_hole' => $data['seeds_per_hole'] ?? null,
            'light_profile' => $this->mapLightProfile($data['light_profile'] ?? null),
            'soil_condition' => $this->mapSoilCondition($data['soil_condition'] ?? null),
            'planting_detail' => $data['planting_detail'] ?? null,
            'pruning_detail' => $data['pruning_detail'] ?? null,
            'days_to_flower' => $data['days_to_flower'] ?? null,
            'days_to_harvest' => $data['days_to_harvest'] ?? null,
            'harvest_window_days' => $data['harvest_window_days'] ?? null,
            'expected_loss_rate' => $data['expected_loss_rate'] ?? null,
            'harvest_unit' => $data['harvest_unit'] ?? null,
            'satuan_stok_id' => $data['satuan_stok_id'] ?? null,
            'satuan_tanam_id' => $data['satuan_tanam_id'] ?? null,
            'satuan_panen_id' => $data['satuan_panen_id'] ?? null,
            'harga_jual' => $data['harga_jual'] ?? null,
            'minimal_stok' => $data['minimal_stok'] ?? null,
            'public_description' => $data['public_description'] ?? null,
        ];

        if ($request->hasFile('public_photo')) {
            if ($plant->public_photo_path) {
                \Storage::disk('public')->delete($plant->public_photo_path);
            }
            $plantData['public_photo_path'] = $request->file('public_photo')->store('plants/public', 'public');
        }
        
        $plant->update($plantData);
        
        return redirect()->route('plants.show', $plant)->with('success', 'Tanaman diperbarui');
    }

    /**
     * Show current plantings for a plant
     */
    public function currentPlantings(Plant $plant, Request $request)
    {
        $plant->load(['type', 'satuanTanam']);

        $plantings = Planting::whereHas('seedSource', function ($q) use ($plant) {
                $q->where('seed_varieties_id', $plant->getKey());
            })
            ->with(['field.plantingLocation', 'seedSource.plant', 'plant.satuanTanam', 'postHarvests'])
            ->whereNotNull('planted_at')
            ->orderByDesc('planted_at')
            ->get();

        $currentPlantings = $plantings->filter(fn (Planting $item) => ! $item->is_completed)->values();
        $historyPlantings = $plantings->filter(fn (Planting $item) => $item->is_completed)->values();

        return view('planting.plants.current-plantings', compact('plant', 'currentPlantings', 'historyPlantings'));
    }

    public function storeProduction(Request $request, Plant $plant)
    {
        $data = $request->validate([
            'seed_source_id' => 'required|exists:plant_seed_source,seed_source_id',
            'planting_field_id' => 'required|exists:planting_fields,id',
            'planting_batch_number' => 'required|string|max:255|unique:planting_production,planting_batch_number',
            'planted_at' => 'required|date',
            'planting_amount' => 'required|numeric|min:0',
            'estimated_harvest_date' => 'nullable|date|after_or_equal:planted_at',
        ], [
            'seed_source_id.required' => 'Benih sumber wajib dipilih.',
            'planting_field_id.required' => 'Lahan wajib dipilih.',
        ]);

        $seedSource = SeedSource::findOrFail($data['seed_source_id']);
        if ($seedSource->seed_varieties_id !== $plant->getKey()) {
            return back()->withErrors(['seed_source_id' => 'Benih sumber tidak sesuai dengan tanaman ini.'])->withInput();
        }
        if ((float) $seedSource->quantity_kg <= 0) {
            return back()->withErrors(['seed_source_id' => 'Benih sumber ini tidak tersedia.'])->withInput();
        }

        $field = \App\Models\PlantingField::findOrFail($data['planting_field_id']);

        $batch = trim((string) ($data['planting_batch_number'] ?? ''));
        if ($batch === '') {
            $year = date('Y');
            $count = Planting::whereYear('planted_at', $year)->count() + 1;
            $batch = 'TANAM-' . $year . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            while (Planting::where('planting_batch_number', $batch)->exists()) {
                $count++;
                $batch = 'TANAM-' . $year . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            }
        }
        $data['planting_batch_number'] = $batch;
        $data['satuan_panen_id'] = $plant->satuan_panen_id;
        $data['status'] = Planting::STATUS_PERENCANAAN;
        $data['is_completed'] = false;

        if ((float) $seedSource->quantity_kg < (float) $data['planting_amount']) {
            return back()->withErrors(['planting_amount' => 'Jumlah benih yang ditanam melebihi sisa benih sumber.'])->withInput();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $seedSource) {
            $seedSource->decrement('quantity_kg', $data['planting_amount']);
            Planting::create($data);
        });

        return redirect()->route('plants.current-plantings', $plant)
            ->with('success', 'Produksi penanaman berhasil ditambahkan');
    }

    /**
     * Show reports for a specific planting
     */
    public function showPlantingReports(Plant $plant, Planting $planting, Request $request)
    {
        $plant->load(['type']);
        
        // Verify that this planting belongs to this plant
        if ($planting->seedSource?->seed_varieties_id !== $plant->getKey()) {
            abort(404, 'Penanaman tidak ditemukan untuk tanaman ini.');
        }
        
        $plantingLocation = $planting->location ?? $planting->field?->plantingLocation;
        
        // Check if user has access to this planting location
        $user = auth()->user();
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Load tasks for this planting
        $statusFilter = $request->get('status', 'all'); // Default to 'all' to show "Semua Laporan" tab
        $assigneeFilter = $request->get('assignee', 'all');
        $taskYear = $request->get('task_year', '');
        $taskMonth = $request->get('task_month', '');
        
        $tasksQuery = $plantingLocation->tasks()
            ->where('planting_tasks.planting_id', $planting->planting_id)
            ->with(['assignedUser', 'planting.plant']);
        
        if ($statusFilter !== 'all') {
            $tasksQuery->where('new_status', $statusFilter);
        }
        
        if ($assigneeFilter !== 'all') {
            $tasksQuery->where('assigned_to', $assigneeFilter);
        }
        
        // Filter by year
        if ($taskYear) {
            $tasksQuery->whereYear('due_date', $taskYear);
        }
        
        // Filter by month
        if ($taskMonth) {
            $tasksQuery->whereMonth('due_date', $taskMonth);
        }
        
        $tasks = $tasksQuery->orderBy('due_date', 'asc')->get();
        
        // Get all tasks for "Semua Laporan" tab (without status filter)
        $allTasks = $plantingLocation->tasks()
            ->with(['planting.plant', 'assignedUser', 'createdByUser', 'lastEditedByUser'])
            ->where(function($query) use ($planting) {
                $query->where('planting_tasks.planting_id', $planting->planting_id)
                      ->orWhereNull('planting_tasks.planting_id');
            })
            ->orderBy('due_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get available years for task filter
        $existingYears = $plantingLocation->tasks()
            ->where('planting_tasks.planting_id', $planting->planting_id)
            ->whereNotNull('due_date')
            ->selectRaw('YEAR(due_date) as year')
            ->distinct()
            ->pluck('year');
        
        $currentYear = (int)date('Y');
        $taskYears = collect();
        
        foreach ($existingYears as $year) {
            $taskYears->push((int)$year);
        }
        
        if (!$taskYears->contains($currentYear)) {
            $taskYears->push($currentYear);
        }
        
        for ($i = 1; $i <= 5; $i++) {
            $futureYear = $currentYear + $i;
            if (!$taskYears->contains($futureYear)) {
                $taskYears->push($futureYear);
            }
        }
        
        $taskYears = $taskYears->unique()->sortDesc()->values();
        
        // Get all months
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $taskMonths = $monthNames;
        
        // Load treatments for this planting
        $treatments = $plantingLocation->treatments()
            ->where('planting_treatments.planting_id', $planting->planting_id)
            ->with(['planting.plant', 'responsiblePerson', 'editor'])
            ->orderBy('treatment_date', 'desc')
            ->get();
        
        // Load nutrients for this planting
        $nutrients = $plantingLocation->nutrients()
            ->where('planting_nutrients.planting_id', $planting->planting_id)
            ->with(['planting.plant', 'editor'])
            ->orderBy('application_date', 'desc')
            ->get();
        
        // Load notes for this planting location
        $notes = $plantingLocation->notes()
            ->with('user')
            ->orderBy('note_date', 'desc')
            ->get();
        
        // Load photos for this planting location
        $photos = $plantingLocation->photos()
            ->orderBy('taken_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Load attachments for this planting location
        $attachments = $plantingLocation->attachments()
            ->with(['creator', 'editor'])
            ->orderBy('attachment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all users for task assignment
        $users = \App\Models\User::orderBy('name')->get();
        
        // Get workers for this location (pekerja lahan)
        $locationUsers = $plantingLocation->landWorkerUsers()->orderBy('name')->get()->unique('user_id')->sortBy('name')->values();
        
        $taskTemplates = \Illuminate\Support\Facades\Schema::hasTable('task_templates')
            ? \App\Models\TaskTemplate::where('association', 'penanaman')->where('is_active', true)->orderBy('name')->get()
            : collect();
        
        // Get inventory types for treatment dropdown
        $inventoryTypes = collect();
        
        // Get active plantings for dropdowns (only this planting)
        $activePlantings = collect([$planting]);
        
        // Get all plantings for this location (for association dropdowns)
        $allPlantingsForLocation = $plantingLocation->plantings()
            ->with(['plant'])
            ->whereNotNull('planted_at')
            ->orderBy('planted_at', 'desc')
            ->get();
        
        return view('planting.plants.planting-reports', compact(
            'plant',
            'planting',
            'plantingLocation',
            'tasks',
            'statusFilter',
            'assigneeFilter',
            'taskYear',
            'taskMonth',
            'taskYears',
            'taskMonths',
            'treatments',
            'nutrients',
            'notes',
            'photos',
            'attachments',
            'users',
            'locationUsers',
            'taskTemplates',
            'inventoryTypes',
            'activePlantings',
            'allTasks',
            'allPlantingsForLocation'
        ));
    }

    public function destroy(Plant $plant)
    {
        // Prevent penangkar from deleting plants
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data tanaman.');
        }
        
        $plant->delete();
        return redirect()->route('plants.index')->with('success', 'Tanaman berhasil dihapus');
    }

    /**
     * Kolom plant_varieties.name di database live adalah VARCHAR(50).
     * Nama tampilan memakai varietas, bukan gabungan kategori + komoditas.
     */
    protected function composePlantDisplayName(?string $variety, ?string $fallback = null): string
    {
        $name = trim((string) ($variety ?: $fallback ?: 'Tanaman Baru'));
        if ($name === '') {
            $name = 'Tanaman Baru';
        }

        return mb_substr($name, 0, 50);
    }

    public function labelCertificates(Plant $plant)
    {
        $plant->load(['type', 'satuanStok']);
        $stocks = Stock::with(['postHarvest.planting', 'certificationReport', 'packagings'])
            ->where('seed_varieties_id', $plant->getKey())
            ->whereNotNull('certification_report_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (Stock $stock) => $stock->nomor_induk ?: $stock->id)
            ->map(function ($group) {
                $stock = $group->sortByDesc('created_at')->first();
                $label = $stock->certificationReport;
                $active = $group->flatMap->packagings
                    ->where('status_kemasan', StockPackaging::STATUS_TERSEDIA)
                    ->count();

                return (object) [
                    'stock' => $stock,
                    'nomor_induk' => $stock->nomor_induk,
                    'nomor_batch' => $stock->postHarvest?->planting?->planting_batch_number,
                    'kelas_benih' => $label?->warnaLabel() ?: '-',
                    'total_tersedia' => $active,
                    'isi_kemasan' => $label?->ukuran_kemasan_retail_kg,
                    'total_produk' => $group->flatMap->packagings->count(),
                    'tgl_selesai' => $stock->postHarvest?->tgl_selesai_uji,
                    'tgl_masa_edar' => $stock->tgl_kedaluwarsa ?: $stock->postHarvest?->tgl_kadaluarsa_mutu,
                ];
            })
            ->values();

        return view('planting.plants.label-certificates', compact('plant', 'stocks'));
    }

    public function labelCertificateStock(Plant $plant, Stock $stock)
    {
        if ($stock->seed_varieties_id !== $plant->getKey()) {
            abort(404);
        }
        $plant->load(['type', 'satuanStok']);
        $packagings = $stock->packagings()->with(['label', 'stock'])->orderBy('no_label_seri')->get();

        return view('planting.plants.label-certificate-stock', compact('plant', 'stock', 'packagings'));
    }

    public function labelStockHistory(Plant $plant)
    {
        $plant->load(['type', 'satuanStok']);
        $stocks = Stock::with(['packagings', 'certificationReport'])
            ->where('seed_varieties_id', $plant->getKey())
            ->orderByDesc('created_at')
            ->paginate(20);
        $activeStocks = Stock::with(['packagings.rack.warehouse', 'rack.warehouse'])
            ->where('seed_varieties_id', $plant->getKey())
            ->where('stok_saat_ini', '>', 0)
            ->where('status_stok', Stock::STATUS_SIAP)
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (Stock $stock) => $stock->hasLabel() && ! $stock->isAwaitingPackaging())
            ->values();
        $histories = StockHistory::with(['packaging', 'user'])
            ->where('seed_varieties_id', $plant->getKey())
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'history_page');

        return view('planting.plants.label-stock-history', compact('plant', 'stocks', 'activeStocks', 'histories'));
    }

    public function salesHistory(Plant $plant)
    {
        $plant->load('type');
        $sales = SaleItem::query()
            ->select(
                'receipt_number',
                DB::raw('MIN(sale_date) as sale_date'),
                DB::raw('MIN(buyer_name) as buyer_name'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(sale_item_id) as product_count'),
                DB::raw('SUM(subtotal) as total_amount'),
                DB::raw('MIN(sale_item_id) as first_id'),
                DB::raw('MIN(unit) as unit')
            )
            ->whereHas('packaging.stock', fn ($q) => $q->where('seed_varieties_id', $plant->getKey()))
            ->groupBy('receipt_number')
            ->orderByDesc('sale_date')
            ->paginate(20);

        return view('planting.plants.sales-history', compact('plant', 'sales'));
    }
}







