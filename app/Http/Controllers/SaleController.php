<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $search = $request->filled('search') ? trim($request->search) : null;

        // Dashboard: total transaksi, kuantitas terjual, pendapatan (sesuai filter periode)
        $salesQuery = Sale::query();
        if ($dateFrom) {
            $salesQuery->where('sale_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->where('sale_date', '<=', $dateTo);
        }
        $saleIds = $salesQuery->pluck('sale_id');

        $totalTransactions = $saleIds->count();
        $totalQuantitySold = SaleItem::whereIn('sale_id', $saleIds)->sum('quantity');
        $totalRevenue = SaleItem::whereIn('sale_id', $saleIds)->sum('subtotal');

        // Aggregasi per tipe inventaris (dengan filter periode)
        $itemsQuery = SaleItem::query()
            ->join('sales', 'sales.sale_id', '=', 'sale_items.sale_id')
            ->select(
                'sale_items.inventory_type_id',
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as total_sales'),
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold'),
                DB::raw('COALESCE(SUM(sale_items.subtotal), 0) as total_revenue')
            )
            ->groupBy('sale_items.inventory_type_id');
        if ($dateFrom) {
            $itemsQuery->where('sales.sale_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $itemsQuery->where('sales.sale_date', '<=', $dateTo);
        }
        $aggregated = $itemsQuery->get()->keyBy('inventory_type_id');

        $inventoryTypeIds = $aggregated->keys();
        $inventoryTypesQuery = InventoryType::whereIn('inventory_type_id', $inventoryTypeIds);
        if ($category) {
            $inventoryTypesQuery->where('category', $category);
        }
        if ($search !== null && $search !== '') {
            $inventoryTypesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }
        $inventoryTypes = $inventoryTypesQuery->orderBy('category')->orderBy('name')->get();

        // Lampirkan total dari agregasi (filter periode)
        foreach ($inventoryTypes as $type) {
            $agg = $aggregated->get($type->inventory_type_id);
            $type->total_sales = $agg ? (int) $agg->total_sales : 0;
            $type->total_quantity_sold = $agg ? (float) $agg->total_quantity_sold : 0;
            $type->total_revenue = $agg ? (float) $agg->total_revenue : 0;
        }

        // Daftar kategori untuk filter dropdown (dari tipe yang punya penjualan)
        $categories = InventoryType::whereHas('saleItems')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('sales.index', compact(
            'inventoryTypes',
            'totalTransactions',
            'totalQuantitySold',
            'totalRevenue',
            'dateFrom',
            'dateTo',
            'category',
            'search',
            'categories'
        ));
    }

    /**
     * Show sales history for a specific inventory type
     */
    public function showByInventoryType(InventoryType $inventoryType)
    {
        // Get all sales that include this inventory type
        $sales = Sale::whereHas('items', function($query) use ($inventoryType) {
                $query->where('inventory_type_id', $inventoryType->inventory_type_id);
            })
            ->with(['user', 'items' => function($query) use ($inventoryType) {
                $query->where('inventory_type_id', $inventoryType->inventory_type_id)
                      ->with('inventoryType', 'inventoryLot');
            }])
            ->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate summary
        $totalSales = $sales->total();
        $totalQuantity = SaleItem::where('inventory_type_id', $inventoryType->inventory_type_id)->sum('quantity');
        $totalRevenue = SaleItem::where('inventory_type_id', $inventoryType->inventory_type_id)->sum('subtotal');

        return view('sales.by-inventory-type', compact('inventoryType', 'sales', 'totalSales', 'totalQuantity', 'totalRevenue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::with('bins')->orderBy('name')->get();
        $receiptNumber = Sale::generateReceiptNumber();
        
        // Get inventory types with available stock
        $inventoryTypes = InventoryType::whereHas('lots', function($query) {
                $query->where('current_stock', '>', 0)
                      ->where('status', '!=', 'kadaluarsa');
            })
            ->with(['lots' => function($query) {
                $query->where('current_stock', '>', 0)
                      ->where('status', '!=', 'kadaluarsa')
                      ->with(['warehouse', 'bin'])
                      ->orderBy('created_at', 'asc'); // FIFO
            }])
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        
        return view('sales.create', compact('warehouses', 'receiptNumber', 'inventoryTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required|string|unique:sales,receipt_number',
            'sale_date' => 'required|date',
            'buyer_name' => 'required|string|max:255',
            'buyer_contact' => 'nullable|string|max:255',
            'buyer_nik' => 'nullable|string|max:255',
            'buyer_category' => 'nullable|in:petani_perorangan,kelompok_tani,instansi_pemerintah,swasta,lainnya',
            'buyer_category_custom' => 'nullable|string|max:255|required_if:buyer_category,lainnya',
            'destination_province' => 'nullable|string|max:255',
            'destination_city' => 'nullable|string|max:255',
            'destination_district' => 'nullable|string|max:255',
            'destination_village' => 'nullable|string|max:255',
            'planned_location_name' => 'nullable|string|max:255',
            'estimated_planting_area' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer_bank',
            'payment_status' => 'required|in:lunas,belum_lunas',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_type_id' => 'required|exists:inventory_types,inventory_type_id',
            'items.*.warehouse_id' => 'required|exists:warehouses,warehouse_id',
            'items.*.bin_id' => 'required|exists:bins,bin_id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.package_quantity' => 'nullable|numeric|min:0',
            'items.*.package_value' => 'nullable|numeric|min:0',
            'items.*.package_unit_type' => 'nullable|in:satuan,kantong,ikat,gentong,custom',
            'items.*.package_unit_custom' => 'nullable|string|max:255|required_if:items.*.package_unit_type,custom',
        ]);

        DB::beginTransaction();
        try {
            // Handle payment proof upload
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }

            // Create sale
            $sale = Sale::create([
                'receipt_number' => $request->receipt_number,
                'sale_date' => $request->sale_date,
                'buyer_name' => $request->buyer_name,
                'buyer_contact' => $request->buyer_contact,
                'buyer_nik' => $request->buyer_nik,
                'buyer_category' => $request->buyer_category,
                'buyer_category_custom' => $request->buyer_category_custom,
                'destination_province' => $request->destination_province,
                'destination_city' => $request->destination_city,
                'destination_district' => $request->destination_district,
                'destination_village' => $request->destination_village,
                'planned_location_name' => $request->planned_location_name,
                'estimated_planting_area' => $request->estimated_planting_area,
                'total_amount' => 0, // Will be calculated
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'payment_proof' => $paymentProofPath,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            $totalAmount = 0;

            // Process each item
            foreach ($request->items as $itemData) {
                $warehouseId = $itemData['warehouse_id'];
                $binId = $itemData['bin_id'];
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                
                // Validate quantity is not negative
                if ($quantity < 0) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Jumlah jual tidak boleh negatif."
                    ])->withInput();
                }
                
                // Validate quantity is greater than 0
                if ($quantity <= 0) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Jumlah jual harus lebih dari 0."
                    ])->withInput();
                }
                
                $subtotal = $quantity * $unitPrice;
                $totalAmount += $subtotal;

                // Get bin to verify it exists
                $bin = Bin::findOrFail($binId);
                if ($bin->warehouse_id != $warehouseId) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Bin tidak sesuai dengan lokasi gudang yang dipilih."
                    ])->withInput();
                }

                // FIFO: Get lots in this bin ordered by created_at (oldest first)
                // Exclude expired lots
                $remainingQuantity = $quantity;
                $lotsUsed = [];
                
                $lots = InventoryLot::where('bin_id', $binId)
                    ->where('current_stock', '>', 0)
                    ->where('status', '!=', 'kadaluarsa') // Exclude expired lots
                    ->orderBy('created_at', 'asc') // FIFO: oldest first
                    ->get();

                if ($lots->isEmpty()) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Tidak ada stok tersedia di bin {$bin->name}. Stok yang tersedia mungkin sudah kadaluarsa."
                    ])->withInput();
                }
                
                // Additional check: verify no expired lots are being sold
                $expiredLots = InventoryLot::where('bin_id', $binId)
                    ->where('current_stock', '>', 0)
                    ->where('status', 'kadaluarsa')
                    ->count();
                
                if ($expiredLots > 0) {
                    // Check if user is trying to sell from a bin that only has expired lots
                    $totalLots = InventoryLot::where('bin_id', $binId)
                        ->where('current_stock', '>', 0)
                        ->count();
                    
                    if ($totalLots == $expiredLots) {
                        DB::rollBack();
                        return back()->withErrors([
                            'items' => "Bin {$bin->name} hanya memiliki stok benih yang sudah kadaluarsa. Stok kadaluarsa tidak dapat dijual."
                        ])->withInput();
                    }
                }

                // Check if all lots have the same inventory type
                $inventoryTypeIds = $lots->pluck('inventory_type_id')->unique();
                if ($inventoryTypeIds->count() > 1) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Bin {$bin->name} memiliki beberapa jenis stok bibit yang berbeda. Silakan pilih bin yang hanya berisi satu jenis stok bibit."
                    ])->withInput();
                }

                // Get inventory type from first lot
                $inventoryType = $lots->first()->inventoryType;
                $unit = $lots->first()->stock_unit;

                // Check total available stock
                $totalAvailableStock = $lots->sum('current_stock');
                if ($totalAvailableStock < $quantity) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Stok tidak mencukupi di bin {$bin->name}. Stok tersedia: {$totalAvailableStock} {$unit}. Jumlah yang diminta: {$quantity} {$unit}"
                    ])->withInput();
                }
                
                // Additional validation: quantity cannot exceed available stock (prevent negative stock)
                if ($quantity > $totalAvailableStock) {
                    DB::rollBack();
                    return back()->withErrors([
                        'items' => "Penjualan gagal! Jumlah jual ({$quantity} {$unit}) melebihi stok tersedia ({$totalAvailableStock} {$unit}) di bin {$bin->name}."
                    ])->withInput();
                }

                // Reduce stock using FIFO
                foreach ($lots as $lot) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    $quantityToTake = min($remainingQuantity, $lot->current_stock);
                    $lot->current_stock -= $quantityToTake;
                    $lot->updateStatus();
                    $lot->save();

                    $lotsUsed[] = [
                        'lot' => $lot,
                        'quantity' => $quantityToTake
                    ];

                    $remainingQuantity -= $quantityToTake;

                    // Create inventory transaction for each lot used
                    InventoryTransaction::create([
                        'inventory_type_id' => $lot->inventory_type_id,
                        'inventory_lot_id' => $lot->inventory_lot_id,
                        'transaction_type' => 'distribusi',
                        'quantity' => $quantityToTake,
                        'unit' => $lot->stock_unit,
                        'warehouse_id' => $warehouseId,
                        'bin_id' => $binId,
                        'reason' => 'Penjualan',
                        'notes' => "No. Struk: {$sale->receipt_number} - Pembeli: {$request->buyer_name}",
                        'user_id' => Auth::id(),
                    ]);
                }

                // Create sale item (use first lot ID for reference, but note that multiple lots may be used)
                SaleItem::create([
                    'sale_id' => $sale->sale_id,
                    'inventory_type_id' => $inventoryType->inventory_type_id,
                    'inventory_lot_id' => $lotsUsed[0]['lot']->inventory_lot_id, // Reference to first lot used
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            // Update total amount
            $sale->total_amount = $totalAmount;
            $sale->save();

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Penjualan berhasil dicatat dan stok telah dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan penjualan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'items.inventoryType', 'items.inventoryLot']);
        
        return view('sales.show', compact('sale'));
    }

    /**
     * Get inventory lots for a specific inventory type
     */
    /**
     * Get bins for a specific warehouse (for AJAX)
     */
    public function getBins(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        
        if (!$warehouseId) {
            return response()->json([]);
        }

        $bins = Bin::where('warehouse_id', $warehouseId)
            ->orderBy('name')
            ->get()
            ->map(function ($bin) {
                return [
                    'id' => $bin->bin_id,
                    'name' => $bin->name,
                    'internal_id' => $bin->internal_id,
                ];
            });

        return response()->json($bins);
    }

    /**
     * Get inventory type details with warehouse and bin info (for auto-fill)
     */
    public function getInventoryTypeDetails($id)
    {
        $inventoryType = InventoryType::with(['lots' => function($query) {
                $query->where('current_stock', '>', 0)
                      ->where('status', '!=', 'kadaluarsa')
                      ->with(['warehouse', 'bin'])
                      ->orderBy('created_at', 'asc'); // FIFO - oldest first
            }])
            ->findOrFail($id);
        
        // Get first available lot (FIFO)
        $firstLot = $inventoryType->lots->first();
        
        if (!$firstLot) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada stok tersedia untuk tipe benih ini'
            ], 404);
        }
        
        // Calculate total stock
        $totalStock = $inventoryType->lots->sum('current_stock');

        // Build unique warehouses list from all lots
        $warehousesMap = [];
        foreach ($inventoryType->lots as $lot) {
            if ($lot->warehouse_id && !isset($warehousesMap[$lot->warehouse_id])) {
                $warehousesMap[$lot->warehouse_id] = [
                    'warehouse_id' => $lot->warehouse_id,
                    'warehouse_name' => $lot->warehouse?->name ?: 'Gudang',
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'inventory_type' => [
                'id' => $inventoryType->inventory_type_id,
                'name' => $inventoryType->name,
                'unit' => $inventoryType->unit,
                'estimated_value_per_unit' => $inventoryType->estimated_value_per_unit,
                'estimated_kg_per_unit' => $inventoryType->estimated_kg_per_unit,
                'total_stock' => $totalStock,
            ],
            'warehouses' => array_values($warehousesMap),
            'first_lot' => [
                'warehouse_id' => $firstLot->warehouse_id,
                'warehouse_name' => $firstLot->warehouse?->name ?: 'Gudang',
                'bin_id' => $firstLot->bin_id,
                'bin_name' => $firstLot->bin?->name ?: 'Bin',
                'bin_internal_id' => $firstLot->bin?->internal_id ?? '',
                'stock_unit' => $firstLot->stock_unit,
                'current_stock' => $firstLot->current_stock,
            ],
            'all_lots' => $inventoryType->lots->map(function($lot) {
                return [
                    'id' => $lot->inventory_lot_id,
                    'warehouse_id' => $lot->warehouse_id,
                    'bin_id' => $lot->bin_id,
                    'current_stock' => $lot->current_stock,
                    'stock_unit' => $lot->stock_unit,
                ];
            })
        ]);
    }
    
    /**
     * Get inventory lots for a specific bin (for FIFO calculation)
     */
    public function getBinInventoryLots(Request $request)
    {
        $binId = $request->input('bin_id');
        
        if (!$binId) {
            return response()->json([]);
        }

        // Get all lots in this bin with stock > 0 and status not expired, ordered by created_at (FIFO)
        $lots = InventoryLot::where('bin_id', $binId)
            ->where('current_stock', '>', 0)
            ->where('status', '!=', 'kadaluarsa') // Exclude expired lots
            ->with(['inventoryType', 'warehouse', 'bin'])
            ->orderBy('created_at', 'asc') // FIFO: oldest first
            ->get()
            ->map(function ($lot) {
                return [
                    'id' => $lot->inventory_lot_id,
                    'inventory_type_id' => $lot->inventory_type_id,
                    'inventory_type_name' => $lot->inventoryType->name ?? '-',
                    'production_id' => $lot->production_id ?? 'Lot #' . $lot->inventory_lot_id,
                    'current_stock' => $lot->current_stock,
                    'stock_unit' => $lot->stock_unit,
                    'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d-M-Y') : '-',
                    'created_at' => $lot->created_at->format('d-M-Y H:i'),
                    'status' => $lot->status,
                ];
            });

        return response()->json($lots);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        try {
            // Restore stock for items with lot
            foreach ($sale->items as $item) {
                if ($item->inventory_lot_id) {
                    $lot = InventoryLot::find($item->inventory_lot_id);
                    if ($lot) {
                        // Restore stock
                        $lot->current_stock += $item->quantity;
                        $lot->updateStatus();
                        $lot->save();

                        // Create inventory transaction to record the restoration
                        InventoryTransaction::create([
                            'inventory_type_id' => $item->inventory_type_id,
                            'inventory_lot_id' => $lot->inventory_lot_id,
                            'transaction_type' => 'masuk',
                            'quantity' => $item->quantity,
                            'unit' => $item->unit,
                            'warehouse_id' => $lot->warehouse_id,
                            'bin_id' => $lot->bin_id,
                            'reason' => 'Pembatalan Penjualan',
                            'notes' => "Pembatalan penjualan No. Struk: {$sale->receipt_number}",
                            'user_id' => Auth::id(),
                        ]);
                    }
                }
            }

            // Delete sale items (will cascade delete automatically)
            $sale->items()->delete();
            
            // Delete sale
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil dihapus dan stok telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus penjualan: ' . $e->getMessage()]);
        }
    }
}

