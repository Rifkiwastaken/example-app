<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\Plant;
use App\Models\PlantType;
use App\Models\PlantingLocation;
use App\Models\Stock;
use App\Models\StockPackaging;
use App\Support\SeedCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource (dashboard + filter).
     */
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : null;
        $category = $request->filled('category') ? $request->category : null;
        $plantName = $request->filled('plant_name') ? $request->plant_name : null;
        $variety = $request->filled('variety') ? $request->variety : null;
        $search = $request->filled('search') ? trim($request->search) : null;

        // Dashboard: total transaksi, kuantitas terjual, pendapatan (data di sale_items)
        $baseQuery = SaleItem::query();
        if ($dateFrom) {
            $baseQuery->where('sale_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->where('sale_date', '<=', $dateTo);
        }
        $saleIds = (clone $baseQuery)->select('receipt_number')->distinct()->pluck('receipt_number');

        $totalTransactions = $saleIds->count();
        $totalQuantitySold = (clone $baseQuery)->sum('quantity');
        $totalRevenue = (clone $baseQuery)->sum('subtotal');

        $itemsQuery = SaleItem::query()
            ->join('stock_packaging as sp', 'sp.id', '=', 'sale_items.stock_packaging_id')
            ->join('stock as st', 'st.id', '=', 'sp.stok_benih_id')
            ->select(
                'st.seed_varieties_id as plant_id',
                DB::raw('COUNT(DISTINCT sale_items.receipt_number) as total_sales'),
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold'),
                DB::raw('COALESCE(SUM(sale_items.subtotal), 0) as total_revenue')
            )
            ->whereNotNull('st.seed_varieties_id')
            ->groupBy('st.seed_varieties_id');
        if ($dateFrom) {
            $itemsQuery->where('sale_items.sale_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $itemsQuery->where('sale_items.sale_date', '<=', $dateTo);
        }
        $aggregated = $itemsQuery->get()->keyBy('plant_id');

        $plantsQuery = Plant::with(['type', 'satuanStok'])->whereIn('seed_varieties_id', $aggregated->keys());
        if ($search !== null && $search !== '') {
            $plantsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('variety', 'like', '%'.$search.'%');
            });
        }
        $receiptsQuery = SaleItem::query()
            ->leftJoin('stock_packaging as sp', 'sp.id', '=', 'sale_items.stock_packaging_id')
            ->leftJoin('stock as st', 'st.id', '=', 'sp.stok_benih_id')
            ->leftJoin('plant_varieties as pv', 'pv.seed_varieties_id', '=', 'st.seed_varieties_id')
            ->leftJoin('plant_commodities as pc', 'pc.seed_commodity_id', '=', 'pv.seed_commodity_id')
            ->select(
                'sale_items.receipt_number',
                DB::raw('MIN(sale_items.sale_date) as sale_date'),
                DB::raw('MIN(sale_items.buyer_name) as buyer_name'),
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('COUNT(sale_items.sale_item_id) as product_count'),
                DB::raw('SUM(sale_items.subtotal) as total_amount'),
                DB::raw('MIN(sale_items.sale_item_id) as first_id'),
                DB::raw("GROUP_CONCAT(DISTINCT TRIM(CONCAT(COALESCE(pc.name, pv.name, ''), CASE WHEN pv.variety IS NULL OR pv.variety = '' THEN '' ELSE CONCAT(' - ', pv.variety) END)) SEPARATOR ', ') as plant_names")
            )
            ->groupBy('sale_items.receipt_number')
            ->orderBy('sale_date')
            ->orderBy('sale_items.receipt_number');
        if ($dateFrom) {
            $receiptsQuery->where('sale_items.sale_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $receiptsQuery->where('sale_items.sale_date', '<=', $dateTo);
        }
        if ($search) {
            $receiptsQuery->where(function ($q) use ($search) {
                $q->where('sale_items.receipt_number', 'like', '%'.$search.'%')
                    ->orWhere('sale_items.buyer_name', 'like', '%'.$search.'%');
            });
        }
        if ($category) {
            $receiptsQuery->where('pc.category', $category);
        }
        if ($plantName) {
            $receiptsQuery->where('pc.name', $plantName);
        }
        if ($variety) {
            $receiptsQuery->where('pv.variety', $variety);
        }
        $receipts = $receiptsQuery->paginate(20)->withQueryString();
        $categories = PlantType::query()->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');
        $filterPlants = Plant::with('type')->orderBy('name')->get();
        $plantFilterData = $filterPlants->map(fn (Plant $p) => [
            'category' => $p->type?->category,
            'name' => $p->type?->name,
            'variety' => $p->variety,
        ])->values();

        return view('sales.index', compact(
            'receipts',
            'totalTransactions',
            'totalQuantitySold',
            'totalRevenue',
            'dateFrom',
            'dateTo',
            'category',
            'plantName',
            'variety',
            'search',
            'categories',
            'plantFilterData'
        ));
    }

    /**
     * Show sales history for a specific inventory type
     */
    public function showByPlant(Plant $plant)
    {
        $soldItems = SaleItem::whereHas('packaging.stock', function ($q) use ($plant) {
                $q->where('seed_varieties_id', $plant->getKey());
            })
            ->with([
                'user',
                'packaging.stock.plant',
                'packaging.stock.certificationReport',
                'packaging.stock.postHarvest.planting.field.plantingLocation',
                'packaging.label',
                'packaging.rack.warehouse',
            ])
            ->orderBy('sale_date', 'desc')
            ->get();

        return view('sales.by-plant', compact('plant', 'soldItems'));
    }

    public function availablePackagings(Plant $plant)
    {
        $packagings = StockPackaging::whereHas('stock', function ($q) use ($plant) {
                $q->where('seed_varieties_id', $plant->getKey());
            })
            ->with(['stock', 'rack.warehouse'])
            ->get()
            ->filter(fn (StockPackaging $pkg) => $pkg->isSellable())
            ->sortBy(fn (StockPackaging $pkg) => $pkg->stock?->tgl_kedaluwarsa?->timestamp ?? PHP_INT_MAX)
            ->values()
            ->map(function (StockPackaging $pkg) use ($plant) {
                return [
                    'id' => $pkg->id,
                    'no_label_seri' => $pkg->no_label_seri,
                    'quantity' => (float) $pkg->kapasitas_per_kemasan,
                    'unit' => $plant->satuanStok?->code ?: '',
                    'warehouse' => $pkg->rack?->warehouse?->name ?: '-',
                    'rack' => $pkg->rack?->name ?: '-',
                    'unit_price' => (float) ($plant->harga_jual ?? 0),
                    'expiry' => $pkg->stock?->tgl_kedaluwarsa?->format('Y-m-d'),
                ];
            });

        return response()->json(['packagings' => $packagings, 'unit_price' => (float) ($plant->harga_jual ?? 0)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('seed-requests.create', [
            'buyer' => session('sale_buyer', []),
            'buyerUrl' => route('sales.buyer'),
            'backUrl' => route('sales.index'),
            'heading' => 'Informasi Pembeli',
        ]);
    }

    public function saveBuyer(Request $request)
    {
        session(['sale_buyer' => app(SeedRequestController::class)->validatedBuyer($request)]);

        return redirect()->route('sales.items');
    }

    public function createItems()
    {
        $buyer = session('sale_buyer');
        if (! $buyer) {
            return redirect()->route('sales.create');
        }
        $commodities = PlantType::orderBy('category')->orderBy('name')->get();
        $plants = Plant::with(['type', 'satuanStok', 'stocks'])->orderBy('name')->get();
        $categories = $commodities->pluck('category')->filter()->unique()->sort()->values();

        return view('seed-requests.items', compact('buyer', 'plants', 'categories') + [
            'storeUrl' => route('sales.store'),
            'backUrl' => route('sales.create'),
            'lotsUrl' => url('/permintaan/plants'),
            'heading' => 'Rincian Item dan Pembayaran',
            'submitLabel' => 'Catat penjualan',
        ]);
    }

    public function availableLots(Plant $plant)
    {
        $lots = SeedCertificate::activeLotsForPlant($plant)->map(fn (Stock $stock) => SeedCertificate::lotRow($stock));

        return response()->json(['plant' => $plant->displayName(), 'lots' => $lots]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $buyer = session('sale_buyer');
        if (! $buyer) {
            return redirect()->route('sales.create')->with('error', 'Lengkapi informasi pembeli terlebih dahulu.');
        }

        $request->validate([
            'lots' => 'required|array|min:1',
            'lots.*.stock_id' => 'required|exists:stock,id',
            'lots.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,transfer_bank',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048|required_if:payment_method,transfer_bank',
        ]);

        $packagingIds = app(SeedRequestController::class)->selectFefoPackagings($request->lots);
        $receiptNumber = SaleItem::generateReceiptNumber();

        DB::beginTransaction();
        try {
            $paymentProofPath = $request->hasFile('payment_proof')
                ? $request->file('payment_proof')->store('payment_proofs', 'public')
                : null;

            $header = [
                'receipt_number' => $receiptNumber,
                'sale_date' => $buyer['request_date'] ?? now()->toDateString(),
                'buyer_name' => $buyer['buyer_name'],
                'buyer_contact' => $buyer['buyer_contact'] ?? null,
                'buyer_nik' => $buyer['buyer_nik'] ?? null,
                'buyer_category' => $buyer['buyer_category'] ?? null,
                'buyer_category_custom' => $buyer['buyer_category_custom'] ?? null,
                'destination_province' => $buyer['destination_province'] ?? null,
                'destination_city' => $buyer['destination_city'] ?? null,
                'destination_district' => $buyer['destination_district'] ?? null,
                'destination_village' => $buyer['destination_village'] ?? null,
                'planned_location_name' => $buyer['planned_location_name'] ?? null,
                'planned_gps' => $buyer['planned_gps'] ?? null,
                'estimated_planting_area' => $buyer['estimated_planting_area'] ?? null,
                'payment_method' => $request->payment_method,
                'payment_status' => 'lunas',
                'payment_proof' => $paymentProofPath,
                'notes' => $buyer['notes'] ?? null,
                'user_id' => auth()->user()->user_id ?? Auth::id(),
            ];

            $totalAmount = 0;
            $firstSale = null;
            foreach ($packagingIds as $packagingId) {
                $pkg = StockPackaging::with('stock.plant.satuanStok')->findOrFail($packagingId);
                $plant = $pkg->stock?->plant;
                $quantity = (float) $pkg->kapasitas_per_kemasan;
                $unitPrice = (float) ($plant?->harga_jual ?? 0);
                $unit = $plant?->satuanStok?->code ?: '';
                $subtotal = $quantity * $unitPrice;
                $totalAmount += $subtotal;

                $pkg->update(['status_kemasan' => StockPackaging::STATUS_DISALURKAN]);
                if ($pkg->stock) {
                    $pkg->stock->update([
                        'stok_saat_ini' => max(0, (float) $pkg->stock->stok_saat_ini - $quantity),
                        'updated_by' => auth()->user()->user_id ?? Auth::id(),
                    ]);
                }

                $firstSale = SaleItem::create(array_merge($header, [
                    'stock_packaging_id' => $pkg->id,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]));

                \App\Models\StockHistory::create([
                    'stock_id' => $pkg->stok_benih_id,
                    'stock_packaging_id' => $pkg->id,
                    'seed_varieties_id' => $pkg->stock?->seed_varieties_id,
                    'transaction_type' => 'distribusi',
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'reason' => 'Penjualan '.$receiptNumber,
                    'notes' => 'Kemasan '.$pkg->no_label_seri.' terjual ke '.$buyer['buyer_name'],
                    'user_id' => auth()->user()->user_id ?? Auth::id(),
                ]);
            }

            SaleItem::where('receipt_number', $receiptNumber)->update(['total_amount' => $totalAmount]);
            session()->forget('sale_buyer');
            DB::commit();

            return redirect()->route('sales.show', $firstSale)
                ->with('success', 'Penjualan berhasil dicatat dan stok telah dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan penjualan: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource (sale_id resolved via route binding to first SaleItem).
     */
    public function show(SaleItem $sale)
    {
        $items = SaleItem::where('receipt_number', $sale->receipt_number)
            ->with([
                'packaging.stock.plant.type',
                'packaging.stock.postHarvest',
                'packaging.label',
                'packaging.rack.warehouse',
                'seedRequest.items.plant.type',
                'seedRequest.packagings.stock.plant.type',
                'seedRequest.packagings.rack.warehouse',
                'user',
            ])
            ->orderBy('sale_item_id')
            ->get();

        SaleItem::hydrateFromStockHistories($items);
        foreach ($items as $item) {
            $resolved = $item->resolvedPackaging();
            if (! $item->relationLoaded('packaging') || ! $item->packaging) {
                if ($resolved) {
                    $item->setRelation('packaging', $resolved);
                }
            }
        }

        $sale->setRelation('items', $items);
        $sale->loadMissing(['user', 'seedRequest']);
        $sale->organization = $items->first()?->displayOrganization();

        return view('sales.show', compact('sale'));
    }

    /**
     * Remove the specified resource from storage (sale_id via binding = first SaleItem).
     */
    public function destroy(SaleItem $sale)
    {
        $items = SaleItem::where('receipt_number', $sale->receipt_number)->get();
        DB::beginTransaction();
        try {
            // Restore stock for items with lot
            foreach ($items as $item) {
                $pkg = $item->packaging;
                if ($pkg) {
                    $pkg->update(['status_kemasan' => StockPackaging::STATUS_TERSEDIA]);
                    if ($pkg->stock) {
                        $pkg->stock->update([
                            'stok_saat_ini' => (float) $pkg->stock->stok_saat_ini + (float) $item->quantity,
                        ]);
                    }

                    \App\Models\StockHistory::create([
                        'stock_id' => $pkg->stok_benih_id,
                        'stock_packaging_id' => $pkg->id,
                        'seed_varieties_id' => $pkg->stock?->seed_varieties_id,
                        'transaction_type' => 'penyesuaian_tambah',
                        'quantity' => (float) $item->quantity,
                        'unit' => $item->unit,
                        'reason' => 'Pembatalan penjualan '.$sale->receipt_number,
                        'notes' => 'Kemasan '.$pkg->no_label_seri.' dikembalikan ke stok',
                        'user_id' => auth()->user()->user_id ?? Auth::id(),
                    ]);
                }
            }

            SaleItem::where('receipt_number', $sale->receipt_number)->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil dihapus dan stok telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus penjualan: ' . $e->getMessage()]);
        }
    }
}

