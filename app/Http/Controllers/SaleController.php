<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InventoryType;
use App\Models\InventoryLot;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['user', 'items'])
            ->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inventoryTypes = InventoryType::orderBy('category')->orderBy('name')->get();
        $receiptNumber = Sale::generateReceiptNumber();
        
        return view('sales.create', compact('inventoryTypes', 'receiptNumber'));
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
            'payment_method' => 'required|in:cash,transfer_bank',
            'payment_status' => 'required|in:lunas,belum_lunas',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_type_id' => 'required|exists:inventory_types,id',
            'items.*.inventory_lot_id' => 'nullable|exists:inventory_lots,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create sale
            $sale = Sale::create([
                'receipt_number' => $request->receipt_number,
                'sale_date' => $request->sale_date,
                'buyer_name' => $request->buyer_name,
                'buyer_contact' => $request->buyer_contact,
                'total_amount' => 0, // Will be calculated
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            $totalAmount = 0;

            // Process each item
            foreach ($request->items as $itemData) {
                $inventoryType = InventoryType::findOrFail($itemData['inventory_type_id']);
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $subtotal = $quantity * $unitPrice;
                $totalAmount += $subtotal;

                // Validate and reduce stock if lot is selected
                if (!empty($itemData['inventory_lot_id'])) {
                    $lot = InventoryLot::findOrFail($itemData['inventory_lot_id']);
                    
                    // Validate stock availability
                    if ($lot->current_stock < $quantity) {
                        DB::rollBack();
                        return back()->withErrors([
                            'items' => "Stok tidak mencukupi untuk lot {$lot->production_id}. Stok tersedia: {$lot->current_stock} {$lot->stock_unit}"
                        ])->withInput();
                    }

                    // Reduce stock
                    $lot->current_stock -= $quantity;
                    $lot->updateStatus();
                    $lot->save();

                    // Create inventory transaction
                    InventoryTransaction::create([
                        'inventory_type_id' => $inventoryType->id,
                        'inventory_lot_id' => $lot->id,
                        'transaction_type' => 'distribusi',
                        'quantity' => $quantity,
                        'unit' => $inventoryType->unit,
                        'warehouse_id' => $lot->warehouse_id,
                        'bin_id' => $lot->bin_id,
                        'reason' => 'Penjualan',
                        'notes' => "No. Struk: {$sale->receipt_number} - Pembeli: {$request->buyer_name}",
                        'user_id' => Auth::id(),
                    ]);
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'inventory_type_id' => $inventoryType->id,
                    'inventory_lot_id' => $itemData['inventory_lot_id'] ?? null,
                    'quantity' => $quantity,
                    'unit' => $inventoryType->unit,
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
    public function getInventoryLots(Request $request)
    {
        $inventoryTypeId = $request->input('inventory_type_id');
        
        if (!$inventoryTypeId) {
            return response()->json([]);
        }

        $lots = InventoryLot::where('inventory_type_id', $inventoryTypeId)
            ->where('current_stock', '>', 0)
            ->with(['warehouse', 'bin'])
            ->orderBy('expiry_date', 'asc') // FIFO/FEFO: oldest expiry first
            ->get()
            ->map(function ($lot) {
                return [
                    'id' => $lot->id,
                    'production_id' => $lot->production_id ?? 'Lot #' . $lot->id,
                    'current_stock' => $lot->current_stock,
                    'stock_unit' => $lot->stock_unit,
                    'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d-M-Y') : '-',
                    'warehouse_name' => $lot->warehouse->name ?? '-',
                    'bin_name' => $lot->bin->name ?? '-',
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
                            'inventory_lot_id' => $lot->id,
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

