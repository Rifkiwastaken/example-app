<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantType;
use App\Models\SaleItem;
use App\Models\SeedRequest;
use App\Models\SeedRequestItem;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockPackaging;
use App\Support\SeedCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeedRequestController extends Controller
{
    public function publicIndex(Request $request)
    {
        SeedRequest::releaseExpiredHolds();
        $query = SeedRequest::with('items.plant.type')->orderByDesc('request_date')->orderByDesc('created_at');
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('request_number', 'like', '%'.$q.'%')
                    ->orWhere('buyer_name', 'like', '%'.$q.'%')
                    ->orWhere('organization', 'like', '%'.$q.'%')
                    ->orWhere('buyer_contact', 'like', '%'.$q.'%');
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('landing.seed-requests.index', compact('requests'));
    }

    public function publicCreate(Request $request)
    {
        return view('landing.seed-requests.create', array_merge($this->formPayload($request->query('plant')), [
            'buyer' => session('seed_request_buyer', []),
            'buyerUrl' => route('public.seed-requests.buyer'),
            'backUrl' => route('public.seed-requests.index'),
        ]));
    }

    public function publicSaveBuyer(Request $request)
    {
        session(['seed_request_buyer' => $this->validatedBuyer($request)]);

        return redirect()->route('public.seed-requests.items');
    }

    public function publicCreateItems()
    {
        $buyer = session('seed_request_buyer');
        if (! $buyer) {
            return redirect()->route('public.seed-requests.create');
        }

        return view('landing.seed-requests.items', array_merge($this->formPayload(), [
            'buyer' => $buyer,
            'storeUrl' => route('public.seed-requests.store'),
            'backUrl' => route('public.seed-requests.create'),
            'lotsUrl' => url('/permintaan/plants'),
        ]));
    }

    public function publicShow(SeedRequest $seedRequest)
    {
        $seedRequest->load(['items.plant.type', 'packagings.stock.plant', 'sales.packaging']);

        return view('landing.seed-requests.show', compact('seedRequest'));
    }

    public function publicReceipt(SeedRequest $seedRequest)
    {
        abort_unless(in_array($seedRequest->status, [
            SeedRequest::STATUS_APPROVED,
            SeedRequest::STATUS_READY,
            SeedRequest::STATUS_TAKEN,
        ], true), 404);
        $seedRequest->load(['items.plant.type', 'packagings.stock.plant.type', 'packagings.rack.warehouse', 'sales.packaging.stock.plant.type', 'sales.packaging.label', 'sales.packaging.rack.warehouse', 'sales.user', 'sales.seedRequest.items.plant.type']);
        $sale = $seedRequest->sales->first();
        if ($sale) {
            SaleItem::hydrateFromStockHistories($seedRequest->sales);
            foreach ($seedRequest->sales as $item) {
                if (! $item->relationLoaded('seedRequest') || ! $item->seedRequest) {
                    $item->setRelation('seedRequest', $seedRequest);
                }
                $resolved = $item->resolvedPackaging();
                if ((! $item->relationLoaded('packaging') || ! $item->packaging) && $resolved) {
                    $item->setRelation('packaging', $resolved);
                }
            }
            $sale->setRelation('items', $seedRequest->sales);
            $sale->organization = $seedRequest->organization;
            $publicLayout = ! auth()->check();
            $backUrl = auth()->check()
                ? route('seed-requests.show', $seedRequest)
                : route('public.seed-requests.show', $seedRequest);
            $backLabel = 'Kembali';

            return view('sales.show', compact('sale', 'publicLayout', 'backUrl', 'backLabel'));
        }

        return view('seed-requests.receipt', compact('seedRequest'));
    }

    public function publicStore(Request $request)
    {
        return $this->persist($request, false);
    }

    public function index(Request $request)
    {
        SeedRequest::releaseExpiredHolds();
        $query = SeedRequest::with(['items.plant', 'creator'])->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('request_number', 'like', '%'.$q.'%')
                    ->orWhere('buyer_name', 'like', '%'.$q.'%')
                    ->orWhere('organization', 'like', '%'.$q.'%');
            });
        }
        $requests = $query->paginate(20)->withQueryString();

        return view('seed-requests.index', compact('requests'));
    }

    public function create()
    {
        return view('seed-requests.create', array_merge($this->formPayload(), [
            'buyer' => session('seed_request_buyer_internal', []),
            'buyerUrl' => route('seed-requests.buyer'),
            'backUrl' => route('seed-requests.index'),
        ]));
    }

    public function saveBuyer(Request $request)
    {
        session(['seed_request_buyer_internal' => $this->validatedBuyer($request)]);

        return redirect()->route('seed-requests.items');
    }

    public function createItems()
    {
        $buyer = session('seed_request_buyer_internal');
        if (! $buyer) {
            return redirect()->route('seed-requests.create');
        }

        return view('seed-requests.items', array_merge($this->formPayload(), [
            'buyer' => $buyer,
            'storeUrl' => route('seed-requests.store'),
            'backUrl' => route('seed-requests.create'),
            'lotsUrl' => url('/permintaan/plants'),
        ]));
    }

    public function store(Request $request)
    {
        return $this->persist($request, true);
    }

    public function show(SeedRequest $seedRequest)
    {
        $seedRequest->load(['items.plant.type', 'items.plant.satuanStok', 'packagings.stock.plant.type', 'packagings.stock.plant.satuanStok', 'packagings.rack.warehouse', 'sales.packaging', 'creator', 'processor']);

        return view('seed-requests.show', compact('seedRequest'));
    }

    public function approveForm(SeedRequest $seedRequest)
    {
        $seedRequest->load(['items.plant.type', 'packagings.stock.plant']);
        $plantIds = $seedRequest->items->pluck('seed_varieties_id')->filter()->all();
        $alternatives = StockPackaging::with('stock.plant')
            ->when($plantIds, fn ($q) => $q->whereHas('stock', fn ($s) => $s->whereIn('seed_varieties_id', $plantIds)))
            ->get()
            ->filter(fn (StockPackaging $pkg) => $pkg->isSellable() || $pkg->seed_request_id === $seedRequest->getKey())
            ->values();

        return view('seed-requests.approve', compact('seedRequest', 'alternatives'));
    }

    public function pickup(SeedRequest $seedRequest)
    {
        $seedRequest->load(['items.plant.type', 'packagings.stock.plant']);

        return view('seed-requests.pickup', compact('seedRequest'));
    }

    public function sell(SeedRequest $seedRequest)
    {
        if (in_array($seedRequest->status, [SeedRequest::STATUS_REJECTED, SeedRequest::STATUS_TAKEN], true)) {
            return back()->with('error', 'Permintaan ini tidak dapat dilanjutkan ke penjualan.');
        }
        $seedRequest->load('items.plant.satuanStok');
        $plants = Plant::with(['type', 'satuanStok'])
            ->whereIn('seed_varieties_id', $seedRequest->items->pluck('seed_varieties_id'))
            ->orderBy('name')
            ->get();
        $receiptNumber = SaleItem::generateReceiptNumber();

        return view('seed-requests.sell', compact('seedRequest', 'plants', 'receiptNumber'));
    }

    public function availablePackagings(Plant $plant)
    {
        return $this->availableLots($plant);
    }

    public function availableLots(Plant $plant)
    {
        $lots = SeedCertificate::activeLotsForPlant($plant)->map(fn (Stock $stock) => SeedCertificate::lotRow($stock));

        return response()->json([
            'plant' => $plant->displayName(),
            'unit' => $plant->satuanStok?->code ?: '',
            'unit_price' => (float) ($plant->harga_jual ?? 0),
            'lots' => $lots,
        ]);
    }

    public function approve(Request $request, SeedRequest $seedRequest)
    {
        $held = $seedRequest->packagings()->pluck('id')->all();
        $packagingIds = $request->input('packaging_ids') ?: $held;
        if (empty($packagingIds)) {
            return back()->withErrors(['packaging_ids' => 'Pilih kemasan yang akan ditahan.'])->withInput();
        }
        if (count($held) > 0 && count($packagingIds) !== count($held)) {
            return back()->withErrors(['packaging_ids' => 'Jumlah produk yang dikonfirmasi harus sama dengan rincian permintaan ('.count($held).' kemasan).'])->withInput();
        }

        DB::beginTransaction();
        try {
            $this->releaseHolds($seedRequest);
            $this->holdPackagings($seedRequest, $packagingIds);
            $nextStatus = $seedRequest->payment_method === 'transfer_bank' && $seedRequest->payment_proof
                ? SeedRequest::STATUS_READY
                : SeedRequest::STATUS_APPROVED;
            $seedRequest->update([
                'status' => $nextStatus,
                'processed_by' => Auth::user()->user_id ?? Auth::id(),
                'approved_at' => now(),
                'ready_at' => $nextStatus === SeedRequest::STATUS_READY ? now() : $seedRequest->ready_at,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['packaging_ids' => $e->getMessage()])->withInput();
        }

        return redirect()->route('seed-requests.show', $seedRequest)
            ->with('success', 'Permintaan telah dikonfirmasi.');
    }

    public function reject(Request $request, SeedRequest $seedRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);
        $this->releaseHolds($seedRequest);
        $seedRequest->update([
            'status' => SeedRequest::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
        ]);

        return redirect()->route('seed-requests.show', $seedRequest)
            ->with('success', 'Permintaan ditolak.');
    }

    public function markReady(SeedRequest $seedRequest)
    {
        if (! in_array($seedRequest->status, [SeedRequest::STATUS_APPROVED, SeedRequest::STATUS_READY], true)) {
            return back()->with('error', 'Permintaan belum disetujui.');
        }
        $seedRequest->update([
            'status' => SeedRequest::STATUS_READY,
            'ready_at' => now(),
            'processed_by' => Auth::user()->user_id ?? Auth::id(),
        ]);

        return back()->with('success', 'Status permintaan: siap diambil.');
    }

    public function complete(Request $request, SeedRequest $seedRequest)
    {
        $request->validate([
            'receipt_number' => 'nullable|string|max:50',
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer_bank',
            'payment_status' => 'required|in:lunas,batal',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'packaging_ids' => 'nullable|array',
            'packaging_ids.*' => 'exists:stock_packaging,id',
        ]);

        if ($request->payment_status === 'batal') {
            $this->releaseHolds($seedRequest);
            $seedRequest->update([
                'status' => SeedRequest::STATUS_REJECTED,
                'rejection_reason' => 'Tidak lunas / dibatalkan pada penjemputan.',
                'processed_by' => Auth::user()->user_id ?? Auth::id(),
            ]);

            return redirect()->route('seed-requests.show', $seedRequest)
                ->with('success', 'Permintaan ditolak dan stok dikembalikan menjadi aktif.');
        }

        $packagingIds = $request->packaging_ids ?: $seedRequest->packagings()->pluck('id')->all();
        if (empty($packagingIds)) {
            return back()->withErrors(['packaging_ids' => 'Pilih kemasan yang disalurkan.'])->withInput();
        }

        $request->merge(['payment_status' => 'lunas']);

        DB::beginTransaction();
        try {
            $paymentProofPath = $request->hasFile('payment_proof')
                ? $request->file('payment_proof')->store('payment_proofs', 'public')
                : null;

            $receiptNumber = $request->receipt_number;
            if (! filled($receiptNumber) || SaleItem::where('receipt_number', $receiptNumber)->exists()) {
                $receiptNumber = SaleItem::generateReceiptNumber();
            }

            $header = [
                'receipt_number' => $receiptNumber,
                'sale_date' => $request->sale_date,
                'buyer_name' => $seedRequest->buyer_name,
                'buyer_contact' => $seedRequest->buyer_contact,
                'buyer_nik' => $seedRequest->buyer_nik,
                'buyer_category' => $seedRequest->buyer_category,
                'buyer_category_custom' => $seedRequest->buyer_category_custom,
                'destination_province' => $seedRequest->destination_province,
                'destination_city' => $seedRequest->destination_city,
                'destination_district' => $seedRequest->destination_district,
                'destination_village' => $seedRequest->destination_village,
                'planned_location_name' => $seedRequest->planned_location_name,
                'planned_gps' => $seedRequest->planned_gps,
                'estimated_planting_area' => $seedRequest->estimated_planting_area,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'payment_proof' => $paymentProofPath,
                'notes' => $request->notes ?: $seedRequest->notes,
                'seed_request_id' => $seedRequest->getKey(),
                'user_id' => Auth::user()->user_id ?? Auth::id(),
            ];

            $totalAmount = 0;
            foreach ($packagingIds as $packagingId) {
                $pkg = StockPackaging::with('stock.plant.satuanStok')->findOrFail($packagingId);
                $plant = $pkg->stock?->plant;
                $quantity = (float) $pkg->kapasitas_per_kemasan;
                $unitPrice = (float) ($plant?->harga_jual ?? 0);
                $unit = $plant?->satuanStok?->code ?: '';
                $subtotal = $quantity * $unitPrice;
                $totalAmount += $subtotal;

                $pkg->update([
                    'status_kemasan' => StockPackaging::STATUS_DISALURKAN,
                    'hold_for_request' => false,
                    'hold_until' => null,
                    'seed_request_id' => $seedRequest->getKey(),
                ]);
                if ($pkg->stock) {
                    $pkg->stock->update([
                        'stok_saat_ini' => max(0, (float) $pkg->stock->stok_saat_ini - $quantity),
                    ]);
                }

                SaleItem::create(array_merge($header, [
                    'stock_packaging_id' => $pkg->id,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]));

                StockHistory::create([
                    'stock_id' => $pkg->stok_benih_id,
                    'stock_packaging_id' => $pkg->id,
                    'seed_varieties_id' => $pkg->stock?->seed_varieties_id,
                    'transaction_type' => 'distribusi',
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'reason' => 'Penyaluran permintaan '.$seedRequest->request_number,
                    'notes' => 'Kemasan '.$pkg->no_label_seri.' diambil oleh '.$seedRequest->buyer_name,
                    'user_id' => Auth::user()->user_id ?? Auth::id(),
                ]);
            }

            SaleItem::where('receipt_number', $receiptNumber)->update(['total_amount' => $totalAmount]);
            $seedRequest->update([
                'status' => SeedRequest::STATUS_TAKEN,
                'taken_at' => now(),
                'receipt_number' => $receiptNumber,
                'processed_by' => Auth::user()->user_id ?? Auth::id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('seed-requests.show', $seedRequest)
            ->with('success', 'Transaksi selesai. Stok kemasan dikosongkan dan permintaan ditandai telah diambil.');
    }

    protected function persist(Request $request, bool $internal)
    {
        $buyer = session($internal ? 'seed_request_buyer_internal' : 'seed_request_buyer');
        if (! $buyer) {
            return redirect()->route($internal ? 'seed-requests.create' : 'public.seed-requests.create')
                ->with('error', 'Lengkapi informasi pembeli terlebih dahulu.');
        }

        $data = $request->validate([
            'lots' => 'required|array|min:1',
            'lots.*.stock_id' => 'required|exists:stock,id',
            'lots.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,transfer_bank',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048|required_if:payment_method,transfer_bank',
            'total_amount' => 'nullable|numeric|min:0',
        ], [
            'lots.required' => 'Tambahkan minimal satu produk benih.',
            'payment_proof.required_if' => 'Lampiran bukti pembayaran wajib diunggah untuk pembayaran non-cash.',
        ]);

        DB::beginTransaction();
        try {
            $paymentProofPath = $request->hasFile('payment_proof')
                ? $request->file('payment_proof')->store('payment_proofs', 'public')
                : null;

            $seedRequest = SeedRequest::create(array_merge($buyer, [
                'request_number' => SeedRequest::generateNumber(),
                'payment_method' => $data['payment_method'],
                'payment_proof' => $paymentProofPath,
                'total_amount' => $data['total_amount'] ?? 0,
                'status' => SeedRequest::STATUS_PENDING,
                'created_by' => Auth::user()->user_id ?? null,
            ]));

            $packagingIds = $this->selectFefoPackagings($data['lots']);
            $grouped = [];
            foreach ($packagingIds as $packagingId) {
                $pkg = StockPackaging::with('stock.plant.satuanStok')->findOrFail($packagingId);
                $plant = $pkg->stock?->plant;
                $plantId = $plant?->getKey();
                $qty = (float) $pkg->kapasitas_per_kemasan;
                $price = (float) ($plant?->harga_jual ?? 0);
                if (! $plantId) {
                    continue;
                }
                if (! isset($grouped[$plantId])) {
                    $grouped[$plantId] = [
                        'quantity' => 0,
                        'unit' => $plant?->satuanStok?->code,
                        'unit_price' => $price,
                        'subtotal' => 0,
                    ];
                }
                $grouped[$plantId]['quantity'] += $qty;
                $grouped[$plantId]['subtotal'] += $qty * $price;
            }

            foreach ($grouped as $plantId => $item) {
                SeedRequestItem::create([
                    'seed_request_id' => $seedRequest->getKey(),
                    'seed_varieties_id' => $plantId,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $this->holdPackagings($seedRequest, $packagingIds);
            $seedRequest->update(['total_amount' => collect($grouped)->sum('subtotal')]);
            DB::commit();
            session()->forget($internal ? 'seed_request_buyer_internal' : 'seed_request_buyer');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['lots' => $e->getMessage() ?: 'Lengkapi form dan pilih produk yang tersedia.'])->withInput();
        }

        $route = $internal ? 'seed-requests.show' : 'public.seed-requests.show';

        return redirect()->route($route, $seedRequest)
            ->with('success', 'Silakan pantau status permintaan benih di halaman permintaan benih. Nomor: '.$seedRequest->request_number);
    }

    public function validatedBuyer(Request $request): array
    {
        return $request->validate([
            'request_date' => 'required|date',
            'buyer_name' => 'required|string|max:255',
            'buyer_contact' => 'required|string|max:255',
            'buyer_nik' => 'required|string|max:255',
            'buyer_category' => 'required|in:petani_perorangan,kelompok_tani,instansi_pemerintah,swasta,lainnya',
            'buyer_category_custom' => 'nullable|string|max:255|required_if:buyer_category,lainnya',
            'organization' => 'required|string|max:255',
            'destination_province' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'destination_district' => 'required|string|max:255',
            'destination_village' => 'required|string|max:255',
            'planned_location_name' => 'required|string|max:255',
            'planned_gps' => ['nullable', 'string', 'max:100', 'regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/'],
            'estimated_planting_area' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ], [
            'required' => 'Lengkapi form: :attribute wajib diisi.',
            'planned_gps.regex' => 'Koordinat GPS harus berformat lintang,bujur. Contoh: -0.947083, 100.417206',
        ]);
    }

    /**
     * @param  array<int, array{stock_id:int|string, quantity:int}>  $lots
     * @return array<int, int|string>
     */
    public function selectFefoPackagings(array $lots): array
    {
        $ids = [];
        foreach ($lots as $lot) {
            $stock = Stock::with('packagings')->findOrFail($lot['stock_id']);
            $needed = (int) $lot['quantity'];
            $available = $stock->packagings
                ->filter(fn (StockPackaging $pkg) => $pkg->isSellable())
                ->sortBy(fn (StockPackaging $pkg) => $pkg->stock?->tgl_kedaluwarsa?->timestamp ?? PHP_INT_MAX)
                ->values();
            if ($available->count() < $needed) {
                throw new \RuntimeException('Stok nomor induk '.($stock->nomor_induk ?: $stock->id).' tidak mencukupi.');
            }
            foreach ($available->take($needed) as $pkg) {
                $ids[] = $pkg->id;
            }
        }

        return $ids;
    }

    protected function formPayload(?string $lockedPlantId = null): array
    {
        $commodities = PlantType::orderBy('category')->orderBy('name')->get();
        $plants = Plant::with(['type', 'satuanStok', 'stocks'])->orderBy('name')->get();
        $categories = $commodities->pluck('category')->filter()->unique()->sort()->values();
        $lockedPlant = $lockedPlantId ? $plants->firstWhere('seed_varieties_id', $lockedPlantId) : null;

        return compact('commodities', 'plants', 'categories', 'lockedPlant');
    }

    protected function holdPackagings(SeedRequest $seedRequest, array $packagingIds): void
    {
        foreach ($packagingIds as $id) {
            $pkg = StockPackaging::with('stock')->findOrFail($id);
            if (! $pkg->isSellable() && ! ($pkg->hold_for_request && $pkg->seed_request_id === $seedRequest->getKey())) {
                throw new \RuntimeException('Kemasan '.$pkg->no_label_seri.' tidak dapat ditahan.');
            }
            $pkg->update([
                'hold_for_request' => true,
                'hold_until' => now()->addDays(30),
                'seed_request_id' => $seedRequest->getKey(),
            ]);
        }
    }

    protected function releaseHolds(SeedRequest $seedRequest): void
    {
        StockPackaging::where('seed_request_id', $seedRequest->getKey())
            ->where('hold_for_request', true)
            ->update([
                'hold_for_request' => false,
                'hold_until' => null,
                'seed_request_id' => null,
            ]);
    }
}
