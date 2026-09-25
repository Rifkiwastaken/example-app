<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Bin;
use App\Models\CertificationReport;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingPostHarvest;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockPackaging;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        Stock::expireAllPastDue();
        $plants = Plant::with(['type', 'satuanStok', 'stocks.packagings', 'plantings.location'])
            ->join('plant_commodities', 'plant_varieties.seed_commodity_id', '=', 'plant_commodities.seed_commodity_id')
            ->select('plant_varieties.*')
            ->orderBy('plant_commodities.category')
            ->orderBy('plant_commodities.name')
            ->orderBy('plant_varieties.variety')
            ->get();
        foreach ($plants as $plant) {
            $plant->total_stock = (float) $plant->stocks
                ->where('status_stok', Stock::STATUS_SIAP)
                ->sum('stok_saat_ini');
            $plant->certified_lots = $plant->stocks->count();
        }

        return view('warehouse.seed-stock.index', compact('plants'));
    }

    public function show(Plant $plant, Request $request)
    {
        $this->expireStocks($plant);
        $plant->load(['type', 'satuanStok']);
        $tab = $request->get('tab', 'detail');

        $stocks = Stock::where('seed_varieties_id', $plant->getKey())
            ->with(['postHarvest.planting.field.plantingLocation', 'labels.packagings', 'rack.warehouse', 'packagings.rack.warehouse', 'packagings.stock'])
            ->orderByDesc('created_at')
            ->get();

        $activeLots = $stocks->filter(function (Stock $s) {
            if (! $s->hasLabel() || $s->isAwaitingPackaging() || (float) $s->stok_saat_ini <= 0) {
                return false;
            }

            return $s->status_stok === Stock::STATUS_SIAP || $s->isPastExpiry();
        })->values();
        $pendingLabelLots = $stocks->filter(fn (Stock $s) => $s->isAwaitingStorage())->values();
        $awaitingLabelLots = $stocks->filter(fn (Stock $s) => $s->isAwaitingLabel())->values();
        $emptyLots = $stocks->filter(fn (Stock $s) => (float) $s->stok_saat_ini <= 0 && $s->status_stok !== Stock::STATUS_PELABELAN && ! $s->isAwaitingPackaging())->values();

        $packagingIds = StockPackaging::whereIn('stok_benih_id', $stocks->pluck('id'))->pluck('id');
        $unpackaged = $pendingLabelLots->count();
        $inWarehouse = (float) $stocks->where('status_stok', Stock::STATUS_SIAP)->sum('stok_saat_ini');
        $readyStock = (float) $stocks->where('status_stok', Stock::STATUS_SIAP)->sum('stok_saat_ini');
        $isLowStock = $plant->minimal_stok !== null && (float) $plant->minimal_stok > 0 && $readyStock < (float) $plant->minimal_stok;
        $soldQty = (float) SaleItem::whereIn('stock_packaging_id', $packagingIds)->sum('quantity');
        $revenue = (float) SaleItem::whereIn('stock_packaging_id', $packagingIds)->sum('subtotal');

        $labResults = PlantingPostHarvest::whereHas('planting.seedSource', function ($q) use ($plant) {
            $q->where('seed_varieties_id', $plant->getKey());
        })->get();
        $totalHarvest = (float) $labResults->sum('realisasi_produksi');
        $totalCertified = (float) $labResults->where('status_kelulusan_lab', PlantingPostHarvest::LULUS)->sum('total_hasil_uji');

        $warehouses = Warehouse::with('bins')->orderBy('name')->get();
        $notes = Attachment::forStockPlant($plant->getKey())->latest()->get();
        $photos = collect();

        return view('warehouse.seed-stock.show', compact(
            'plant',
            'tab',
            'stocks',
            'activeLots',
            'pendingLabelLots',
            'awaitingLabelLots',
            'emptyLots',
            'unpackaged',
            'inWarehouse',
            'soldQty',
            'revenue',
            'totalHarvest',
            'totalCertified',
            'warehouses',
            'notes',
            'photos',
            'readyStock',
            'isLowStock'
        ));
    }

    public function labResult(Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $tests = PlantingPostHarvest::where('nomor_induk', $stock->nomor_induk)
            ->with('creator')
            ->orderBy('uji_ke')
            ->get();
        abort_unless($tests->isNotEmpty() || $stock->postHarvest, 404, 'Lot ini belum terhubung ke hasil uji lab.');

        return view('warehouse.seed-stock.lab-result', [
            'plant' => $plant,
            'stock' => $stock,
            'tests' => $tests->isNotEmpty() ? $tests : collect([$stock->postHarvest]),
            'postHarvest' => $stock->postHarvest,
        ]);
    }

    /**
     * Sertifikat lot: menampilkan seluruh hasil uji pada nomor induk yang sama
     * (uji pertama dan uji ulang) beserta label yang sudah dirilis.
     */
    public function certificate(Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $tests = PlantingPostHarvest::where('nomor_induk', $stock->nomor_induk)
            ->with('creator')
            ->orderBy('uji_ke')
            ->get();
        $labels = CertificationReport::whereIn('planting_post_harvest_id', $tests->pluck('planting_post_harvest_id'))
            ->orderBy('created_at')
            ->get();

        return view('public.seed-certificate', [
            'stock' => $stock,
            'cert' => \App\Support\SeedCertificate::certificatePayload($stock),
        ]);
    }

    public function showPackaging(StockPackaging $packaging)
    {
        $packaging->load(['stock.plant.type', 'stock.postHarvest', 'label', 'rack.warehouse', 'sales']);
        $plant = $packaging->stock?->plant;
        abort_unless($plant, 404);

        return view('warehouse.seed-stock.packaging-show', compact('packaging', 'plant'));
    }

    public function createLabel(Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $stock->load(['postHarvest.planting.field.plantingLocation', 'labels.packagings', 'certificationReport.packagings']);
        abort_unless($stock->postHarvest, 404, 'Lot ini belum terhubung ke hasil uji lab.');

        if ($pending = $stock->pendingLabelReport()) {
            return redirect()->route('seed-stock.label.storage', [$plant, $stock, $pending])
                ->with('success', 'Label sudah tercatat. Lanjutkan dengan memilih lokasi dan tempat penyimpanan.');
        }

        $prefill = [
            'id_label_rilis' => CertificationReport::generateLabelId(),
            'nomor_lot' => $stock->postHarvest->nomor_lot,
            'no_sertifikat_bpsb_final' => $stock->postHarvest->no_sertifikat_lab_bpsb,
            'total_volume' => (float) ($stock->postHarvest->total_hasil_uji ?? $stock->stok_saat_ini),
        ];

        return view('warehouse.seed-stock.label-create', compact('plant', 'stock', 'prefill'));
    }

    public function storeLabel(Request $request, Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $stock->load('postHarvest.planting.field.plantingLocation');
        abort_unless($stock->postHarvest, 404, 'Lot ini belum terhubung ke hasil uji lab.');

        $maxSize = (float) ($stock->postHarvest->total_hasil_uji ?? $stock->stok_saat_ini);

        $data = $request->validate([
            'id_label_rilis' => 'required|string|max:50|unique:certification_reports,id_label_rilis',
            'nomor_lot' => 'required|string|max:50',
            'no_sertifikat_bpsb_final' => 'required|string|max:100|unique:certification_reports,no_sertifikat_bpsb_final',
            'warna_label' => 'required|in:Kuning,Putih,Ungu,Biru',
            'tipe_kemasan' => 'required|in:karung_bulk,plastik_ritel,pot_satuan',
            'ukuran_kemasan_retail_kg' => 'required|numeric|min:0.01|max:'.$maxSize,
            'jumlah_lembar_label_dicetak' => 'required|integer|min:1|max:5000',
            'no_seri_label_awal' => 'required|string|max:50',
            'tgl_pemasangan_label' => 'nullable|date',
            'lampiran_qr_label' => 'required|file|max:10240',
        ], [
            'ukuran_kemasan_retail_kg.max' => 'Ukuran kemasan tidak boleh melebihi total hasil uji ('.number_format($maxSize, 2).').',
        ]);

        $printedQty = (float) $data['ukuran_kemasan_retail_kg'] * (int) $data['jumlah_lembar_label_dicetak'];
        if ($printedQty - $maxSize > 0.0001) {
            return back()->withErrors([
                'ukuran_kemasan_retail_kg' => 'Total kemasan (ukuran × jumlah label) tidak boleh melebihi total hasil uji.',
            ])->withInput();
        }

        $report = CertificationReport::create(array_merge($data, [
            'planting_post_harvest_id' => $stock->planting_post_harvest_id,
            'no_seri_label_akhir' => CertificationReport::buildEndSerial($data['no_seri_label_awal'], (int) $data['jumlah_lembar_label_dicetak']),
            'lampiran_qr_label' => $request->file('lampiran_qr_label')->store('seed-labels', 'public'),
            'created_by' => auth()->user()->user_id,
        ]));

        $planting = $stock->postHarvest?->planting;
        if ($planting && ! $planting->is_completed) {
            $planting->update([
                'is_completed' => true,
                'completed_at' => $planting->completed_at ?: now(),
            ]);
        }

        $location = $planting?->field?->plantingLocation;
        if ($location) {
            return redirect()->route('planting-locations.planting-history', $location)
                ->with('success', 'Label benih tersimpan. Lanjutkan input data stok benih untuk memilih lokasi penyimpanan.');
        }

        return redirect()->route('seed-stock.show', [$plant, 'tab' => 'lots'])
            ->with('success', 'Label benih tersimpan. Lanjutkan input data stok benih untuk memilih lokasi penyimpanan.');
    }

    public function createStorage(Plant $plant, Stock $stock, CertificationReport $report)
    {
        $this->ensureStockPlant($plant, $stock);
        $warehouses = Warehouse::with('bins')->orderBy('name')->get();

        return view('warehouse.seed-stock.label-storage', compact('plant', 'stock', 'report', 'warehouses'));
    }

    public function storeStorage(Request $request, Plant $plant, Stock $stock, CertificationReport $report)
    {
        $this->ensureStockPlant($plant, $stock);
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,warehouse_id',
            'rak_gudang_id' => 'required|exists:warehouse_racks,warehouse_bin_id',
        ]);

        $rack = Bin::findOrFail($data['rak_gudang_id']);
        if ($rack->warehouse_id !== $data['warehouse_id']) {
            return back()->withErrors(['rak_gudang_id' => 'Tempat penyimpanan tidak termasuk lokasi yang dipilih.']);
        }

        DB::transaction(function () use ($report, $rack, $stock) {
            foreach ($report->serialList() as $serial) {
                StockPackaging::create([
                    'stok_benih_id' => $stock->id,
                    'certification_report_id' => $report->getKey(),
                    'rak_gudang_id' => $rack->warehouse_bin_id,
                    'no_label_seri' => StockPackaging::where('no_label_seri', $serial)->exists()
                        ? $serial.'-'.$stock->id
                        : $serial,
                    'jenis_kemasan' => $report->tipe_kemasan,
                    'kapasitas_per_kemasan' => $report->ukuran_kemasan_retail_kg,
                    'status_kemasan' => StockPackaging::STATUS_TERSEDIA,
                    'qr_code_token' => StockPackaging::generateToken(),
                ]);
            }

            $stock->update([
                'rak_gudang_id' => $rack->warehouse_bin_id,
                'certification_report_id' => $report->getKey(),
                'status_stok' => Stock::STATUS_SIAP,
                'updated_by' => auth()->user()->user_id,
            ]);
        });

        $total = (float) $report->jumlah_lembar_label_dicetak * (float) $report->ukuran_kemasan_retail_kg;
        $this->logStock($stock, 'stok_masuk', $total, $report->jumlah_lembar_label_dicetak.' kemasan berlabel ditempatkan di '.$rack->name);

        return redirect()->route('seed-stock.show', [$plant, 'tab' => 'lots'])
            ->with('success', 'Stok benih berhasil dimasukkan ke lokasi penyimpanan.');
    }

    public function showLot(Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $this->expireStocks($plant);
        $stock->load(['packagings.rack.warehouse', 'packagings.sales', 'packagings.label', 'packagings.stock', 'postHarvest', 'plant.satuanStok']);
        $packagings = StockPackaging::sorted($stock->packagings);

        return view('warehouse.seed-stock.lot-packagings', compact('plant', 'stock', 'packagings'));
    }

    public function sticker(Plant $plant, Stock $stock, StockPackaging $packaging)
    {
        $this->ensureStockPlant($plant, $stock);
        $packaging->load(['stock.postHarvest', 'stock.plant.type', 'label', 'rack', 'sales']);

        return view('warehouse.seed-stock.sticker', compact('plant', 'stock', 'packaging'));
    }

    public function printLabels(Request $request, Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $ids = $request->input('packaging_ids', []);
        $packagings = $stock->packagings()->whereIn('id', $ids)
            ->with(['stock.postHarvest', 'stock.plant.type', 'label', 'rack', 'sales'])
            ->get();

        return view('warehouse.seed-stock.print-labels', compact('plant', 'stock', 'packagings'));
    }

    public function adjustPackagings(Request $request, Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        $data = $this->validateAdjustment($request);

        $this->applyAdjustment($stock, $data['packaging_ids'], $data['reason']);

        return back()->with('success', 'Penyesuaian stok dicatat: '.$data['reason']);
    }

    public function recertify(Request $request, Plant $plant, Stock $stock)
    {
        $this->ensureStockPlant($plant, $stock);
        abort_unless($stock->postHarvest, 422, 'Hasil uji lab belum lengkap. Lengkapi hasil uji sebelum sertifikasi ulang.');
        $ids = $request->input('packaging_ids', []);
        if (empty($ids)) {
            return back()->withErrors(['packaging_ids' => 'Pilih produk berdasarkan nomor label yang ingin disertifikasi ulang.']);
        }

        $reason = trim((string) $request->input('reason', 'Sertifikasi ulang'));
        if ($reason === '') {
            $reason = 'Sertifikasi ulang';
        }

        return $this->applyRecertification($stock, $reason, $ids);
    }

    public function rackPackagings(Warehouse $warehouse, Bin $bin, Request $request)
    {
        $statusFilter = $request->get('status', 'all');

        Stock::expireAllPastDue();
        $query = StockPackaging::where('rak_gudang_id', $bin->warehouse_bin_id)
            ->with(['stock.plant.satuanStok', 'stock.postHarvest.planting.field.plantingLocation', 'label', 'rack.warehouse', 'sales']);

        if ($statusFilter !== 'all') {
            $query->where('status_kemasan', $statusFilter);
        }

        $packagings = StockPackaging::sorted($query->get());

        $statusCounts = StockPackaging::where('rak_gudang_id', $bin->warehouse_bin_id)
            ->selectRaw('status_kemasan, COUNT(*) as total')
            ->groupBy('status_kemasan')
            ->pluck('total', 'status_kemasan');

        return view('warehouse.locations.rack-packagings', compact('warehouse', 'bin', 'packagings', 'statusFilter', 'statusCounts'));
    }

    public function adjustRackPackagings(Request $request, Warehouse $warehouse, Bin $bin)
    {
        $data = $this->validateAdjustment($request);

        $packagings = StockPackaging::whereIn('id', $data['packaging_ids'])
            ->where('rak_gudang_id', $bin->warehouse_bin_id)
            ->get();

        foreach ($packagings->groupBy('stok_benih_id') as $stockId => $group) {
            $stock = Stock::find($stockId);
            if ($stock) {
                $this->applyAdjustment($stock, $group->pluck('id')->all(), $data['reason']);
            }
        }

        return back()->with('success', 'Penyesuaian stok dicatat: '.$data['reason']);
    }

    public function recertifyRackPackagings(Request $request, Warehouse $warehouse, Bin $bin)
    {
        $data = $request->validate([
            'packaging_ids' => 'required|array|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $packagings = StockPackaging::whereIn('id', $data['packaging_ids'])
            ->where('rak_gudang_id', $bin->warehouse_bin_id)
            ->get();

        $stockId = $packagings->pluck('stok_benih_id')->unique();
        if ($stockId->count() !== 1) {
            return back()->withErrors(['packaging_ids' => 'Pilih kemasan dari satu lot benih yang sama untuk sertifikasi ulang.']);
        }

        $stock = Stock::findOrFail($stockId->first());
        abort_unless($stock->postHarvest, 422, 'Hasil uji lab belum lengkap.');

        $reason = trim((string) ($data['reason'] ?? 'Sertifikasi ulang')) ?: 'Sertifikasi ulang';

        return $this->applyRecertification($stock, $reason, $packagings->pluck('id')->all());
    }

    /**
     * @return array{packaging_ids: array<int, int>, reason: string}
     */
    protected function validateAdjustment(Request $request): array
    {
        $data = $request->validate([
            'packaging_ids' => 'required|array|min:1',
            'reason_code' => 'required|in:'.implode(',', array_keys(StockPackaging::ALASAN_PENYESUAIAN)),
            'reason_other' => 'required_if:reason_code,lainnya|nullable|string|max:255',
        ], [
            'reason_other.required_if' => 'Tuliskan alasan penyesuaian stok.',
        ]);

        return [
            'packaging_ids' => $data['packaging_ids'],
            'reason' => $data['reason_code'] === 'lainnya'
                ? $data['reason_other']
                : StockPackaging::ALASAN_PENYESUAIAN[$data['reason_code']],
        ];
    }

    /**
     * @param  array<int, int|string>  $packagingIds
     */
    protected function applyAdjustment(Stock $stock, array $packagingIds, string $reason): void
    {
        $reduced = (float) $stock->packagings()->whereIn('id', $packagingIds)->sum('kapasitas_per_kemasan');

        $stock->packagings()->whereIn('id', $packagingIds)->update([
            'status_kemasan' => StockPackaging::STATUS_TIDAK_AKTIF,
            'hold_for_recert' => false,
            'alasan_penyesuaian' => $reason,
            'tgl_penyesuaian' => now(),
        ]);

        $stock->update([
            'stok_saat_ini' => max(0, (float) $stock->stok_saat_ini - $reduced),
            'updated_by' => auth()->user()->user_id,
        ]);

        $this->logStock($stock, 'penyesuaian_kurang', $reduced, count($packagingIds).' kemasan dinonaktifkan', $reason);
    }

    /**
     * Menahan kemasan terpilih lalu mengarahkan ke form hasil uji lab berikutnya
     * pada produksi asal lot ini.
     *
     * @param  array<int, int|string>|null  $packagingIds
     */
    protected function applyRecertification(Stock $stock, string $reason, ?array $packagingIds)
    {
        $stock->loadMissing('postHarvest.planting.field.plantingLocation');
        $planting = $stock->postHarvest?->planting;
        $location = $planting?->field?->plantingLocation;

        if (! $planting || ! $location) {
            return back()->withErrors(['reason' => 'Lot ini tidak terhubung ke produksi penanaman sehingga tidak bisa diuji ulang.']);
        }

        $selected = collect($packagingIds ?? [])->filter()->values();
        if ($selected->isEmpty()) {
            return back()->withErrors(['packaging_ids' => 'Pilih produk berdasarkan nomor label yang ingin disertifikasi ulang.']);
        }

        $query = $stock->packagings()
            ->whereIn('id', $selected->all())
            ->where('status_kemasan', '!=', StockPackaging::STATUS_DISALURKAN);
        $qty = (float) (clone $query)->sum('kapasitas_per_kemasan');
        if ($qty <= 0) {
            return back()->withErrors(['packaging_ids' => 'Produk yang dipilih tidak tersedia untuk sertifikasi ulang.']);
        }

        $newPlanting = null;
        DB::transaction(function () use ($stock, $query, $qty, $reason, $planting, &$newPlanting) {
            (clone $query)->update([
                'hold_for_recert' => false,
                'status_kemasan' => StockPackaging::STATUS_TIDAK_AKTIF,
                'alasan_penyesuaian' => StockPackaging::ALASAN_PENYESUAIAN['kadaluarsa'],
                'tgl_penyesuaian' => now(),
            ]);

            $remaining = (float) $stock->packagings()
                ->where('status_kemasan', StockPackaging::STATUS_TERSEDIA)
                ->where('hold_for_recert', false)
                ->where('hold_for_request', false)
                ->sum('kapasitas_per_kemasan');

            $stock->update([
                'stok_saat_ini' => $remaining,
                'status_stok' => $remaining > 0 ? Stock::STATUS_SIAP : Stock::STATUS_UJI_ULANG,
                'hold_for_recert' => $remaining <= 0,
                'updated_by' => auth()->user()->user_id,
            ]);

            $this->logStock($stock, 'stok_keluar', $qty, 'Stok tidak aktif karena sertifikasi ulang', $reason);
            $newPlanting = $this->openRecertProduction($planting, $reason);
        });

        return redirect()->route('planting-locations.plantings.lab-result.create', [
            $location,
            $newPlanting,
            'alasan_uji_ulang' => $reason,
            'volume_uji_ulang' => $qty,
        ])->with('success', 'Produk terpilih tidak aktif. Lanjutkan isi hasil uji lab pada produksi sertifikasi ulang.');
    }

    protected function openRecertProduction(Planting $source, string $reason): Planting
    {
        $batch = trim((string) $source->planting_batch_number).'-SU';
        $suffix = 1;
        while (Planting::where('planting_batch_number', $batch)->exists()) {
            $batch = trim((string) $source->planting_batch_number).'-SU-'.$suffix++;
        }

        return Planting::create([
            'seed_source_id' => $source->seed_source_id,
            'planting_field_id' => $source->planting_field_id,
            'planting_batch_number' => $batch,
            'planting_amount' => $source->planting_amount,
            'planting_amount_seeds' => $source->planting_amount_seeds,
            'progress' => $source->progress,
            'planted_at' => $source->planted_at,
            'estimated_harvest_date' => $source->estimated_harvest_date,
            'target_kelas' => $source->target_kelas,
            'no_form_bpsb' => $source->no_form_bpsb,
            'tanggal_daftar_bpsb' => $source->tanggal_daftar_bpsb,
            'file_path' => $source->file_path,
            'file_name' => $source->file_name,
            'satuan_panen_id' => $source->satuan_panen_id,
            'is_completed' => false,
            'completed_at' => null,
            'status' => Planting::STATUS_PASCA_PANEN,
            'description' => trim(($source->description ? $source->description."\n" : '').'[recert:'.$source->getKey().'] '.$reason),
        ]);
    }

    public function storeNote(Request $request, Plant $plant)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'attachment_date' => 'nullable|date',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);
        $file = $request->file('file');
        Attachment::create([
            'module' => Attachment::MODULE_STOCK,
            'seed_varieties_id' => $plant->getKey(),
            'planting_location_id' => null,
            'stock_id' => $request->input('stock_id'),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'attachment_date' => $data['attachment_date'] ?? now()->toDateString(),
            'created_by' => auth()->user()->user_id,
            'file_path' => $file ? $file->store('attachments', 'public') : null,
            'file_name' => $file?->getClientOriginalName(),
            'file_size' => $file?->getSize(),
            'mime_type' => $file?->getMimeType(),
        ]);

        return back()->with('success', 'Lampiran disimpan');
    }

    public function storePhoto(Request $request, Plant $plant)
    {
        return $this->storeNote($request, $plant);
    }

    /**
     * Catat riwayat pergerakan stok benih (lot induk / kemasan).
     */
    protected function logStock(Stock $stock, string $type, float $quantity, ?string $notes = null, ?string $reason = null, ?int $packagingId = null): void
    {
        StockHistory::create([
            'stock_id' => $stock->id,
            'stock_packaging_id' => $packagingId,
            'seed_varieties_id' => $stock->seed_varieties_id,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'unit' => $stock->plant?->satuanStok?->code,
            'reason' => $reason,
            'notes' => $notes,
            'user_id' => auth()->user()?->user_id,
        ]);
    }

    protected function ensureStockPlant(Plant $plant, Stock $stock): void
    {
        if ($stock->seed_varieties_id !== $plant->getKey()) {
            abort(404);
        }
    }

    protected function expireStocks(Plant $plant): void
    {
        Stock::where('seed_varieties_id', $plant->getKey())->get()->each->syncExpired();
    }
}
