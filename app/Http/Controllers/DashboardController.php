<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Harvest;
use App\Models\InventoryLot;
use App\Models\InventoryType;
use App\Models\InventoryTypeSeed;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Task;
use App\Models\User;
use App\Models\PlantingLocationNote;
use App\Models\CertificationReport;
use App\Models\Bin;
use App\Models\Warehouse;
use App\Models\Plant;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $weatherData = $this->getWeatherData();
        
        $user = auth()->user();
        $isAdmin = $user && $user->isAdmin();
        
        // Get filter parameters
        $plantFilter = $request->get('plant_filter', 'all');
        $inventoryTypeFilter = $request->get('inventory_type_filter', 'all');
        
        // Get filter options (only for admin)
        $plants = collect();
        $inventoryTypes = collect();
        if ($isAdmin) {
            $plants = Plant::with('type')->orderBy('name')->get();
            $inventoryTypes = InventoryType::with('plant.type')->orderBy('name')->get();
            
            // 1. Grafik Tren Produksi (12 bulan terakhir) - with filter
            $productionTrend = $this->getProductionTrend($plantFilter);
            
            // 2. Pie Chart Stok (Komposisi berdasarkan kategori) - with filter
            $stockComposition = $this->getStockComposition($inventoryTypeFilter);
            
            // 3. Grafik Pendapatan (12 bulan terakhir) - with filter
            $revenueTrend = $this->getRevenueTrend($inventoryTypeFilter);
            
            // Get table data
            $productionTableData = $this->getProductionTableData($plantFilter);
            $stockTableData = $this->getStockTableData($inventoryTypeFilter);
            $revenueTableData = $this->getRevenueTableData($inventoryTypeFilter);
        } else {
            // For non-admin, set empty data
            $productionTrend = ['labels' => [], 'data' => []];
            $stockComposition = ['labels' => [], 'data' => []];
            $revenueTrend = ['labels' => [], 'data' => []];
            $productionTableData = collect();
            $stockTableData = collect();
            $revenueTableData = collect();
        }
        
        // Statistik Tugas (hanya untuk admin, kepala_satuan_tugas, penangkar)
        $upcomingTasks = null;
        $taskNotifications = null;
        $noteNotifications = null;
        
        if ($user && ($user->isAdmin() || in_array($user->role, ['kepala_satuan_tugas', 'penangkar']))) {
            $upcomingTasks = $this->getUpcomingTasks($user);
            $taskNotifications = $this->getTaskNotifications($user);
            $noteNotifications = $this->getNoteNotifications($user);
        }
        
        // 6. Notifikasi Sertifikasi yang Melewati Masa Edar (hanya untuk admin)
        $expiredCertifications = collect();
        if ($user && $user->isAdmin()) {
            $expiredCertifications = $this->getExpiredCertifications();
        }
        
        // 7. Notifikasi Benih yang Mendekati/Melewati Masa Kadaluarsa (sesuai penanggung jawab)
        $expiringSeeds = collect();
        if ($user) {
            $expiringSeeds = $this->getExpiringSeedsForUser($user);
        }
        
        // 8. Notifikasi Benih di Bin yang Melewati Masa Kadaluarsa (hanya untuk admin dan petugas gudang)
        $expiredBinStocks = collect();
        if ($user && ($user->isAdmin() || $user->role === 'petugas_gudang')) {
            $expiredBinStocks = $this->getExpiredBinStocks();
        }
        
        // 9. Notifikasi Stok Benih Rendah (hanya untuk admin dan petugas gudang)
        $lowStockNotifications = collect();
        if ($user && ($user->isAdmin() || $user->role === 'petugas_gudang')) {
            $lowStockNotifications = $this->getLowStockNotifications();
        }
        
        return view('dashboard.index', compact(
            'weatherData',
            'productionTrend',
            'stockComposition',
            'revenueTrend',
            'upcomingTasks',
            'taskNotifications',
            'noteNotifications',
            'expiredCertifications',
            'expiringSeeds',
            'expiredBinStocks',
            'lowStockNotifications',
            'isAdmin',
            'plants',
            'inventoryTypes',
            'plantFilter',
            'inventoryTypeFilter',
            'productionTableData',
            'stockTableData',
            'revenueTableData'
        ));
    }
    
    /**
     * Get production trend data (last 12 months)
     */
    private function getProductionTrend($plantFilter = 'all')
    {
        $months = [];
        $production = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $date->format('M');
            
            $harvestsQuery = Harvest::whereYear('harvested_at', $date->year)
                ->whereMonth('harvested_at', $date->month);
            
            // Filter by plant if specified
            if ($plantFilter && $plantFilter !== 'all') {
                $harvestsQuery->where('plant_id', $plantFilter);
            }
            
            $harvests = $harvestsQuery->get();
            
            $totalTon = 0;
            foreach ($harvests as $harvest) {
                $totalTon += $this->convertToTon($harvest->quantity, $harvest->unit);
            }
            
            $production[] = round($totalTon, 2);
        }
        
        return [
            'labels' => $months,
            'data' => $production
        ];
    }
    
    /**
     * Get stock composition by inventory type (showing sold stock)
     */
    private function getStockComposition($inventoryTypeFilter = 'all')
    {
        // Get all inventory types
        $inventoryTypesQuery = InventoryType::with('plant.type');
        
        // Filter by inventory type if specified
        if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
            $inventoryTypesQuery->where('id', $inventoryTypeFilter);
        }
        
        $inventoryTypes = $inventoryTypesQuery->get();
        
        $composition = [];
        
        foreach ($inventoryTypes as $type) {
            // Get total sold quantity from sale_items
            $soldQuantity = SaleItem::where('inventory_type_id', $type->plant_type_id)
                ->sum('quantity');
            
            // Convert to kg for comparison
            $soldInKg = $this->convertToKg($soldQuantity, $type->unit ?? 'kg');
            
            if ($soldInKg > 0) {
                $typeName = $type->name;
                if ($type->plant && $type->plant->variety) {
                    $typeName .= ' - ' . $type->plant->variety;
                }
                $composition[$typeName] = $soldInKg;
            }
        }
        
        // If no data, return empty
        if (empty($composition)) {
            return [
                'labels' => [],
                'data' => []
            ];
        }
        
        return [
            'labels' => array_keys($composition),
            'data' => array_values($composition)
        ];
    }
    
    /**
     * Get revenue trend data (last 12 months) - filtered by inventory type
     */
    private function getRevenueTrend($inventoryTypeFilter = 'all')
    {
        $months = [];
        $revenue = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            
            // Get sales for this month
            $salesQuery = Sale::whereYear('sale_date', $date->year)
                ->whereMonth('sale_date', $date->month);
            
            $sales = $salesQuery->get();
            
            $totalRevenue = 0;
            
            foreach ($sales as $sale) {
                // Get sale items for this sale
                $itemsQuery = SaleItem::where('sale_id', $sale->sale_id);
                
                // Filter by inventory type if specified
                if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
                    $itemsQuery->where('inventory_type_id', $inventoryTypeFilter);
                }
                
                $items = $itemsQuery->get();
                
                // Sum subtotals from filtered items
                foreach ($items as $item) {
                    $totalRevenue += $item->subtotal;
                }
            }
            
            $revenue[] = round($totalRevenue, 0);
        }
        
        return [
            'labels' => $months,
            'data' => $revenue
        ];
    }
    
    /**
     * Get seeds expiring in next 30 days
     */
    private function getExpiringSeeds()
    {
        $today = Carbon::today();
        $thirtyDaysLater = Carbon::today()->addDays(30);
        
        return InventoryLot::with(['inventoryType.plant', 'warehouse', 'certification'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', $thirtyDaysLater)
            ->where('current_stock', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($lot) use ($today) {
                $lot->days_until_expiry = $today->diffInDays(Carbon::parse($lot->expiry_date));
                return $lot;
            });
    }
    
    /**
     * Convert quantity to ton
     */
    private function convertToTon($quantity, $unit)
    {
        $unit = strtolower($unit);
        
        $factors = [
            'kg' => 0.001,
            'kilogram' => 0.001,
            'gram' => 0.000001,
            'ton' => 1,
            'kuintal' => 0.1,
            'ikat' => 0.0001,
            'barel' => 0.15,
            'tandan' => 0.005,
            'gantang' => 0.002,
            'lusin' => 0.0005,
            'batang' => 0.0001,
            'kiloliter' => 1,
            'liter' => 0.001,
            'mililiter' => 0.000001,
            'satuan' => 0.0001,
        ];
        
        return $quantity * ($factors[$unit] ?? 1);
    }
    
    /**
     * Convert quantity to kg
     */
    private function convertToKg($quantity, $unit)
    {
        $unit = strtolower($unit);
        
        $factors = [
            'kg' => 1,
            'kilogram' => 1,
            'gram' => 0.001,
            'ton' => 1000,
            'kuintal' => 100,
            'ikat' => 0.1,
            'barel' => 150,
            'tandan' => 5,
            'gantang' => 2,
            'lusin' => 0.5,
            'batang' => 0.1,
            'kiloliter' => 1000,
            'liter' => 1,
            'mililiter' => 0.001,
            'satuan' => 0.1,
        ];
        
        return $quantity * ($factors[$unit] ?? 1);
    }

    private function getWeatherData()
    {
        try {
            // Menggunakan OpenWeatherMap API untuk lokasi Lubuk Minturun, Padang
            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => -0.9478, // Latitude untuk Lubuk Minturun, Padang
                'lon' => 100.4172, // Longitude untuk Lubuk Minturun, Padang
                'appid' => 'your_api_key_here', // Ganti dengan API key yang valid
                'units' => 'metric',
                'lang' => 'id'
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Fallback data jika API tidak tersedia
            return [
                'name' => 'Lubuk Minturun, Padang',
                'main' => [
                    'temp' => 28,
                    'feels_like' => 32,
                    'humidity' => 80
                ],
                'weather' => [
                    [
                        'description' => 'Scattered Clouds',
                        'icon' => '03d'
                    ]
                ],
                'wind' => [
                    'speed' => 1
                ],
                'clouds' => [
                    'all' => 25
                ]
            ];
        }

        return null;
    }
    
    /**
     * Get task statistics by status
     */
    private function getTaskStatistics(User $user)
    {
        $query = Task::query();
        
        // Filter berdasarkan role
        if (!$user->isAdmin()) {
            // Untuk kepala_satuan_tugas dan penangkar, hanya tampilkan tugas dari lokasi yang ditugaskan
            if ($user->role === 'kepala_satuan_tugas' || $user->role === 'penangkar') {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $query->whereIn('planting_location_id', $assignedLocationIds);
                } else {
                    // Jika tidak ada lokasi yang ditugaskan, return empty result
                    $query->whereRaw('1 = 0');
                }
            }
        }
        
        // Hitung berdasarkan status
        $statistics = [
            'selesai' => (clone $query)->where('new_status', 'selesai')->count(),
            'dalam_progress' => (clone $query)->where('new_status', 'dalam_progress')->count(),
            'dilakukan' => (clone $query)->where('new_status', 'dilakukan')->count(),
            'tidak_selesai' => (clone $query)->where('new_status', 'tidak_selesai')->count(),
        ];
        
        return $statistics;
    }
    
    /**
     * Get upcoming tasks (in progress with deadline)
     */
    private function getUpcomingTasks(User $user)
    {
        $query = Task::with(['assignedUser', 'plantingLocation'])
            ->where('new_status', 'dalam_progress')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', Carbon::today())
            ->orderBy('due_date', 'asc')
            ->limit(10);
        
        // Filter berdasarkan role
        if (!$user->isAdmin()) {
            // Untuk kepala_satuan_tugas dan penangkar, hanya tampilkan tugas dari lokasi yang ditugaskan
            if ($user->role === 'kepala_satuan_tugas' || $user->role === 'penangkar') {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $query->whereIn('planting_location_id', $assignedLocationIds);
                } else {
                    // Jika tidak ada lokasi yang ditugaskan, return empty result
                    $query->whereRaw('1 = 0');
                }
            }
        }
        
        return $query->get()->map(function ($task) {
            $task->days_until_deadline = Carbon::today()->diffInDays(Carbon::parse($task->due_date), false);
            return $task;
        });
    }
    
    /**
     * Get task notifications (tasks close to deadline)
     */
    private function getTaskNotifications(User $user)
    {
        $query = Task::with(['assignedUser', 'plantingLocation'])
            ->whereIn('new_status', ['dalam_progress', 'dilakukan'])
            ->whereNotNull('due_date')
            ->where('due_date', '>=', Carbon::today())
            ->where('due_date', '<=', Carbon::today()->addDays(3)) // 3 hari ke depan
            ->orderBy('due_date', 'asc');
        
        // Filter berdasarkan role
        if (!$user->isAdmin()) {
            // Untuk kepala_satuan_tugas dan penangkar, hanya tampilkan tugas dari lokasi yang ditugaskan
            if ($user->role === 'kepala_satuan_tugas' || $user->role === 'penangkar') {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $query->whereIn('planting_location_id', $assignedLocationIds);
                } else {
                    // Jika tidak ada lokasi yang ditugaskan, return empty result
                    $query->whereRaw('1 = 0');
                }
            }
        }
        
        return $query->get()->map(function ($task) {
            $task->days_until_deadline = Carbon::today()->diffInDays(Carbon::parse($task->due_date), false);
            $task->is_urgent = $task->days_until_deadline <= 1;
            return $task;
        });
    }

    /**
     * Get note notifications (unread notes assigned to user)
     */
    private function getNoteNotifications(User $user)
    {
        $query = PlantingLocationNote::with(['plantingLocation', 'user'])
            ->whereNotNull('assigned_to')
            ->whereJsonContains('assigned_to', $user->user_id)
            ->where(function($q) use ($user) {
                $q->whereNull('read_by')
                  ->orWhereJsonDoesntContain('read_by', $user->user_id);
            })
            ->orderBy('note_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10);
        
        // Filter berdasarkan role
        if (!$user->isAdmin()) {
            // Untuk kepala_satuan_tugas dan penangkar, hanya tampilkan catatan dari lokasi yang ditugaskan
            if ($user->role === 'kepala_satuan_tugas' || $user->role === 'penangkar') {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $query->whereIn('planting_location_id', $assignedLocationIds);
                } else {
                    // Jika tidak ada lokasi yang ditugaskan, return empty result
                    $query->whereRaw('1 = 0');
                }
            }
        }
        
        return $query->get();
    }
    
    /**
     * Get expired certifications that need renewal.
     * Sertifikasi yang stoknya sudah dihapus (data stok telah dihapus) tidak ditampilkan di notifikasi.
     */
    private function getExpiredCertifications()
    {
        return CertificationReport::with([
            'certification.harvest.plant.type',
            'certification.harvest.location',
            'certification.harvest.plant',
            'inventoryTypes',
        ])
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<', Carbon::today())
        ->where(function($query) {
            $query->whereNull('report_type')
                  ->orWhere('report_type', '!=', 'Laporan Sertifikasi Ulang');
        })
        ->orderBy('expiry_date', 'asc')
        ->get()
        ->filter(function($report) {
            // Sudah ada sertifikasi ulang -> tidak perlu notifikasi
            $hasRenewal = CertificationReport::where('certification_id', $report->certification_id)
                ->where('report_type', 'Laporan Sertifikasi Ulang')
                ->where('created_at', '>', $report->expiry_date)
                ->exists();
            if ($hasRenewal) {
                return false;
            }
            // Jika pernah ditambahkan ke stok tapi data stok sudah dihapus -> jangan tampilkan notifikasi
            $report->load('inventoryTypes');
            $hasBeenAddedToStock = $report->inventoryTypes->count() > 0;
            if ($hasBeenAddedToStock) {
                $linkedTypeIds = $report->inventoryTypes->pluck('inventory_type_id')->toArray();
                $seedStillExists = InventoryTypeSeed::where('certification_report_id', $report->certification_report_id)
                    ->whereIn('inventory_type_id', $linkedTypeIds)
                    ->exists();
                if (!$seedStillExists) {
                    return false; // Stok telah dihapus, jangan notifikasi
                }
            }
            return true;
        });
    }
    
    /**
     * Get expiring seeds (within 3 months or expired) for responsible person
     */
    private function getExpiringSeedsForUser(User $user)
    {
        $threeMonthsFromNow = Carbon::today()->addMonths(3);
        
        // Get inventory types where user is responsible
        $inventoryTypeIds = InventoryType::where('responsible_person_id', $user->user_id)
            ->pluck('inventory_type_id')
            ->toArray();
        
        if (empty($inventoryTypeIds)) {
            return collect();
        }
        
        // Get seeds that are expired or expiring within 3 months
        $seeds = InventoryTypeSeed::with([
            'inventoryType',
            'plant.type',
            'plantingLocation'
        ])
        ->whereIn('inventory_type_id', $inventoryTypeIds)
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<=', $threeMonthsFromNow)
        ->orderBy('expiry_date', 'asc')
        ->get()
        ->map(function($seed) {
            $seed->is_expired = $seed->expiry_date->isPast();
            $seed->is_near_expiry = $seed->expiry_date->isFuture() && $seed->expiry_date->diffInMonths(now()) <= 3;
            $seed->notification_type = 'seed';
            return $seed;
        });
        
        // Also get certified seeds from certification reports linked to inventory types
        $certifiedSeeds = collect();
        $inventoryTypes = InventoryType::whereIn('id', $inventoryTypeIds)->get();
        foreach ($inventoryTypes as $inventoryType) {
            foreach ($inventoryType->certificationReports as $certReport) {
                if ($certReport->expiry_date && $certReport->expiry_date <= $threeMonthsFromNow) {
                    $certReport->load([
                        'certification.plant.type',
                        'certification.harvest',
                        'certification.plantingLocation'
                    ]);
                    $certReport->is_expired = $certReport->expiry_date->isPast();
                    $certReport->is_near_expiry = $certReport->expiry_date->isFuture() && $certReport->expiry_date->diffInMonths(now()) <= 3;
                    $certReport->notification_type = 'certified_seed';
                    $certReport->inventory_type = $inventoryType;
                    $certifiedSeeds->push($certReport);
                }
            }
        }
        
        return $seeds->merge($certifiedSeeds);
    }
    
    /**
     * Get expired inventory lots in bins (for admin and warehouse staff)
     */
    private function getExpiredBinStocks()
    {
        $today = Carbon::today();
        
        // Get all expired inventory lots that are in bins and have stock > 0
        $expiredLots = InventoryLot::with([
            'inventoryType',
            'warehouse',
            'bin'
        ])
        ->whereNotNull('bin_id')
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<', $today)
        ->where('current_stock', '>', 0)
        ->orderBy('expiry_date', 'asc')
        ->get()
        ->groupBy(function($lot) {
            // Group by warehouse and bin for easier notification display
            return $lot->warehouse_id . '-' . $lot->bin_id;
        })
        ->map(function($lots, $key) use ($today) {
            $firstLot = $lots->first();
            $warehouse = $firstLot->warehouse;
            $bin = $firstLot->bin;
            
            return [
                'warehouse_id' => $warehouse->warehouse_id ?? null,
                'warehouse_name' => $warehouse->name ?? 'Gudang Tidak Diketahui',
                'bin_id' => $bin->bin_id ?? null,
                'bin_name' => $bin->name ?? 'Bin Tidak Diketahui',
                'bin_internal_id' => $bin->internal_id ?? '-',
                'expired_count' => $lots->count(),
                'total_expired_stock' => $lots->sum('current_stock'),
                'lots' => $lots->map(function($lot) use ($today) {
                    return [
                        'id' => $lot->inventory_lot_id,
                        'inventory_type_name' => $lot->inventoryType->name ?? '-',
                        'production_id' => $lot->production_id ?? 'Lot #' . $lot->inventory_lot_id,
                        'current_stock' => $lot->current_stock,
                        'stock_unit' => $lot->stock_unit,
                        'expiry_date' => $lot->expiry_date->format('d M Y'),
                        'days_expired' => $lot->expiry_date->diffInDays($today),
                    ];
                }),
                'notification_type' => 'expired_bin_stock',
            ];
        })
        ->values();
        
        return $expiredLots;
    }
    
    /**
     * Get low stock notifications (for admin and warehouse staff)
     */
    private function getLowStockNotifications()
    {
        // Get all inventory types that have low_stock_threshold set
        $inventoryTypes = InventoryType::whereNotNull('low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->with(['plant.type'])
            ->get();
        
        $lowStockItems = collect();
        
        foreach ($inventoryTypes as $type) {
            // Get total stock from seeds (certified seeds)
            $totalStock = $type->total_stock_from_seeds ?? 0;
            
            // Get threshold value
            $threshold = $type->low_stock_threshold ?? 0;
            $thresholdUnit = $type->low_stock_unit ?? 'kg';
            $stockUnit = $type->unit ?? 'kg';
            
            // Convert both to kg for comparison
            $totalStockInKg = $totalStock;
            $thresholdInKg = $threshold;
            
            // Convert total stock to kg
            if ($stockUnit === 'ton') {
                $totalStockInKg = $totalStock * 1000;
            } elseif ($stockUnit === 'gram') {
                $totalStockInKg = $totalStock / 1000;
            }
            
            // Convert threshold to kg
            if ($thresholdUnit === 'ton') {
                $thresholdInKg = $threshold * 1000;
            } elseif ($thresholdUnit === 'gram') {
                $thresholdInKg = $threshold / 1000;
            }
            
            // Check if stock is below threshold
            if ($totalStockInKg < $thresholdInKg) {
                // Calculate difference in the stock unit
                $difference = $threshold - $totalStock;
                if ($thresholdUnit !== $stockUnit) {
                    // If units are different, convert threshold to stock unit
                    if ($thresholdUnit === 'ton' && $stockUnit === 'kg') {
                        $difference = ($threshold * 1000) - $totalStock;
                    } elseif ($thresholdUnit === 'kg' && $stockUnit === 'ton') {
                        $difference = $threshold - ($totalStock * 1000);
                    } else {
                        // Use kg as base
                        $difference = $thresholdInKg - $totalStockInKg;
                        if ($stockUnit === 'ton') {
                            $difference = $difference / 1000;
                        } elseif ($stockUnit === 'gram') {
                            $difference = $difference * 1000;
                        }
                    }
                }
                
                $lowStockItems->push([
                    'inventory_type_id' => $type->inventory_type_id,
                    'inventory_type_name' => $type->name,
                    'plant_name' => $type->plant->name ?? $type->name,
                    'variety' => $type->plant->variety ?? null,
                    'category' => $type->category,
                    'sku' => $type->sku,
                    'current_stock' => $totalStock,
                    'stock_unit' => $stockUnit,
                    'threshold' => $threshold,
                    'threshold_unit' => $thresholdUnit,
                    'difference' => abs($difference),
                    'notification_type' => 'low_stock',
                ]);
            }
        }
        
        return $lowStockItems->sortBy('difference')->values();
    }
    
    /**
     * Get production table data (harvests grouped by plant)
     */
    private function getProductionTableData($plantFilter = 'all')
    {
        $harvestsQuery = Harvest::with(['plant.type', 'planting', 'location'])
            ->where('quantity', '>', 0);
        
        // Filter by plant if specified
        if ($plantFilter && $plantFilter !== 'all') {
            $harvestsQuery->where('plant_id', $plantFilter);
        }
        
        $harvests = $harvestsQuery->orderBy('harvested_at', 'desc')->get();
        
        // Group by plant
        $grouped = $harvests->groupBy('plant_id')->map(function($plantHarvests, $plantId) {
            $firstHarvest = $plantHarvests->first();
            $plant = $firstHarvest->plant;
            
            $totalQuantity = 0;
            $totalTon = 0;
            $unit = $firstHarvest->unit ?? 'kg';
            
            foreach ($plantHarvests as $harvest) {
                $totalQuantity += $harvest->quantity;
                $totalTon += $this->convertToTon($harvest->quantity, $harvest->unit ?? 'kg');
            }
            
            return [
                'plant_id' => $plantId,
                'plant_name' => $plant->name ?? 'N/A',
                'variety' => $plant->variety ?? null,
                'plant_type' => $plant->type->name ?? null,
                'total_quantity' => $totalQuantity,
                'total_ton' => round($totalTon, 2),
                'unit' => $unit,
                'harvest_count' => $plantHarvests->count(),
                'latest_harvest_date' => $plantHarvests->max('harvested_at'),
            ];
        })->values();
        
        return $grouped;
    }
    
    /**
     * Get stock table data (inventory types with sold quantities)
     */
    private function getStockTableData($inventoryTypeFilter = 'all')
    {
        $inventoryTypesQuery = InventoryType::with(['plant.type']);
        
        // Filter by inventory type if specified
        if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
            $inventoryTypesQuery->where('id', $inventoryTypeFilter);
        }
        
        $inventoryTypes = $inventoryTypesQuery->get();
        
        $tableData = $inventoryTypes->map(function($type) {
            // Get total sold quantity from sale_items
            $soldItems = SaleItem::where('inventory_type_id', $type->plant_type_id)->get();
            
            $totalSoldQuantity = $soldItems->sum('quantity');
            $totalSoldInKg = $this->convertToKg($totalSoldQuantity, $type->unit ?? 'kg');
            
            // Get current stock from seeds
            $currentStock = $type->total_stock_from_seeds ?? 0;
            $currentStockInKg = $this->convertToKg($currentStock, $type->unit ?? 'kg');
            
            return [
                'inventory_type_id' => $type->plant_type_id,
                'inventory_type_name' => $type->name,
                'plant_name' => $type->plant->name ?? $type->name,
                'variety' => $type->plant->variety ?? null,
                'category' => $type->category,
                'sku' => $type->sku,
                'current_stock' => $currentStock,
                'current_stock_kg' => round($currentStockInKg, 2),
                'sold_quantity' => $totalSoldQuantity,
                'sold_quantity_kg' => round($totalSoldInKg, 2),
                'unit' => $type->unit ?? 'kg',
                'sale_count' => $soldItems->groupBy('sale_id')->count(),
            ];
        })->filter(function($item) {
            // Only show items that have been sold
            return $item['sold_quantity'] > 0;
        })->values();
        
        return $tableData;
    }
    
    /**
     * Get revenue table data (sales grouped by inventory type)
     */
    private function getRevenueTableData($inventoryTypeFilter = 'all')
    {
        $saleItemsQuery = SaleItem::with(['inventoryType.plant.type', 'sale']);
        
        // Filter by inventory type if specified
        if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
            $saleItemsQuery->where('inventory_type_id', $inventoryTypeFilter);
        }
        
        $saleItems = $saleItemsQuery->get();
        
        // Group by inventory type
        $grouped = $saleItems->groupBy('inventory_type_id')->map(function($items, $inventoryTypeId) {
            $firstItem = $items->first();
            $inventoryType = $firstItem->inventoryType;
            
            $totalRevenue = $items->sum('subtotal');
            $totalQuantity = $items->sum('quantity');
            $saleCount = $items->groupBy('sale_id')->count();
            
            return [
                'inventory_type_id' => $inventoryTypeId,
                'inventory_type_name' => $inventoryType->name ?? 'N/A',
                'plant_name' => $inventoryType->plant->name ?? $inventoryType->name,
                'variety' => $inventoryType->plant->variety ?? null,
                'category' => $inventoryType->category,
                'sku' => $inventoryType->sku,
                'total_revenue' => round($totalRevenue, 2),
                'total_quantity' => round($totalQuantity, 2),
                'unit' => $firstItem->unit ?? 'kg',
                'sale_count' => $saleCount,
                'average_price' => $totalQuantity > 0 ? round($totalRevenue / $totalQuantity, 2) : 0,
            ];
        })->values();
        
        return $grouped;
    }
}

