<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CertificationHarvest;
use App\Models\SaleItem;
use App\Models\Task;
use App\Models\User;
use App\Models\PlantingLocationNote;
use App\Models\CertificationReport;
use App\Models\Bin;
use App\Models\Warehouse;
use App\Models\Plant;
use App\Models\PlantType;
use App\Models\Planting;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockPackaging;
use App\Models\PlantingPostHarvest;

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
        $productionTrend = ['labels' => [], 'data' => []];
        $stockComposition = ['labels' => [], 'data' => []];
        $revenueTrend = $this->getRevenueTrend('all');
        if ($isAdmin) {
            $plants = Plant::with('type')->orderBy('name')->get();
            $inventoryTypes = collect();
            $productionTableData = $this->getProductionTableData($plantFilter);
            $stockTableData = $this->getStockTableData($inventoryTypeFilter);
            $revenueTableData = $this->getRevenueTableData($inventoryTypeFilter);
        } else {
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
        
        $year = (int) $request->get('year', now()->year);
        $commodityId = $request->get('commodity_id');

        $certifiedQuery = function (int $forYear) use ($commodityId) {
            $q = PlantingPostHarvest::where('status_kelulusan_lab', PlantingPostHarvest::LULUS)
                ->whereYear('tgl_selesai_uji', $forYear);
            if ($commodityId) {
                $q->whereHas('planting.seedSource.variety', fn ($p) => $p->where('seed_commodity_id', $commodityId));
            }
            return $q;
        };
        $certifiedThis = $certifiedQuery($year)->sum('total_hasil_uji');
        $certifiedLast = $certifiedQuery($year - 1)->sum('total_hasil_uji');
        $certTrend = $certifiedLast > 0 ? round((($certifiedThis - $certifiedLast) / $certifiedLast) * 100, 1) : 0;

        $pass = PlantingPostHarvest::whereYear('tgl_selesai_uji', $year)
            ->where('status_kelulusan_lab', PlantingPostHarvest::LULUS)->count();
        $fail = PlantingPostHarvest::whereYear('tgl_selesai_uji', $year)
            ->where('status_kelulusan_lab', PlantingPostHarvest::TIDAK_LULUS)->count();
        $passRate = ($pass + $fail) > 0 ? round($pass / ($pass + $fail) * 100, 1) : 0;

        $lossRate = (float) PlantingPostHarvest::whereYear('tgl_selesai_uji', $year)
            ->selectRaw('AVG(realisasi_produksi - total_hasil_uji) as shrinkage')
            ->value('shrinkage');

        $totalRevenueQuery = SaleItem::whereYear('sale_date', $year);
        if ($commodityId) {
            $totalRevenueQuery->whereHas('packaging.stock.plant', fn ($p) => $p->where('seed_commodity_id', $commodityId));
        }
        $totalRevenue = (float) $totalRevenueQuery->sum('subtotal');

        $months = collect(range(1, 12))->map(fn ($m) => Carbon::create($year, $m, 1)->translatedFormat('M'));
        $prodMonthly = [];
        $saleMonthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $prodMonthly[] = (float) PlantingPostHarvest::where('status_kelulusan_lab', PlantingPostHarvest::LULUS)
                ->whereYear('tgl_selesai_uji', $year)->whereMonth('tgl_selesai_uji', $m)->sum('total_hasil_uji');
            $saleMonthly[] = (float) SaleItem::whereYear('sale_date', $year)->whereMonth('sale_date', $m)->sum('quantity');
        }

        $stockByPlantId = Stock::query()
            ->where('status_stok', Stock::STATUS_SIAP)
            ->whereNotNull('seed_varieties_id')
            ->select('seed_varieties_id', DB::raw('SUM(stok_saat_ini) as qty'))
            ->groupBy('seed_varieties_id')
            ->pluck('qty', 'seed_varieties_id');

        $prodByPlantId = PlantingPostHarvest::query()
            ->join('planting_production as pp', 'pp.planting_production_id', '=', 'planting_post_harvest.planting_id')
            ->join('plant_seed_source as ss', 'ss.seed_source_id', '=', 'pp.seed_source_id')
            ->where('planting_post_harvest.status_kelulusan_lab', PlantingPostHarvest::LULUS)
            ->whereYear('planting_post_harvest.tgl_selesai_uji', $year)
            ->whereNotNull('ss.seed_varieties_id')
            ->select('ss.seed_varieties_id', DB::raw('SUM(planting_post_harvest.total_hasil_uji) as qty'))
            ->groupBy('ss.seed_varieties_id')
            ->pluck('qty', 'ss.seed_varieties_id');

        $saleByPlantId = SaleItem::query()
            ->join('stock_packaging as sp', 'sp.id', '=', 'sale_items.stock_packaging_id')
            ->join('stock as st', 'st.id', '=', 'sp.stok_benih_id')
            ->whereYear('sale_items.sale_date', $year)
            ->whereNotNull('st.seed_varieties_id')
            ->select('st.seed_varieties_id', DB::raw('SUM(sale_items.quantity) as qty'))
            ->groupBy('st.seed_varieties_id')
            ->pluck('qty', 'st.seed_varieties_id');

        $plantsForCharts = Plant::with('type')
            ->when($commodityId, fn ($q) => $q->where('seed_commodity_id', $commodityId))
            ->get();

        [$stockByCategory, $stockByVariety] = $this->composeCategoryCharts($plantsForCharts, $stockByPlantId);
        [$prodByCategory, $prodByVariety] = $this->composeCategoryCharts($plantsForCharts, $prodByPlantId);
        [$saleByCategory, $saleByVariety] = $this->composeCategoryCharts($plantsForCharts, $saleByPlantId);

        $lossRows = StockHistory::whereIn('transaction_type', ['penyesuaian_kurang', 'penghapusan', 'pengurangan'])
            ->whereYear('created_at', $year)
            ->get();
        $lossMatrix = [
            'kedaluwarsa' => 0,
            'hama' => 0,
            'rusak' => 0,
        ];
        foreach ($lossRows as $row) {
            $text = mb_strtolower(($row->reason ?? '').' '.($row->notes ?? ''));
            $qty = abs((float) $row->quantity);
            if (str_contains($text, 'kedaluwarsa') || str_contains($text, 'expired') || str_contains($text, 'kadaluarsa')) {
                $lossMatrix['kedaluwarsa'] += $qty;
            } elseif (str_contains($text, 'hama') || str_contains($text, 'tikus')) {
                $lossMatrix['hama'] += $qty;
            } else {
                $lossMatrix['rusak'] += $qty;
            }
        }

        $mapPoints = SaleItem::whereYear('sale_date', $year)->whereNotNull('planned_gps')->get(['planned_gps', 'quantity', 'destination_city', 'buyer_name']);
        $geoPoints = [];
        foreach ($mapPoints as $item) {
            $parts = preg_split('/[,\s]+/', trim((string) $item->planned_gps));
            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $geoPoints[] = [
                    'lat' => (float) $parts[0],
                    'lng' => (float) $parts[1],
                    'qty' => (float) $item->quantity,
                    'label' => $item->destination_city ?: $item->buyer_name,
                ];
            }
        }

        $expiredLots = Stock::with('rack')
            ->whereDate('tgl_kedaluwarsa', '<=', now())
            ->where('stok_saat_ini', '>', 0)
            ->orderBy('tgl_kedaluwarsa')
            ->take(8)
            ->get();
        $lowStocks = Plant::with('satuanStok')->whereNotNull('minimal_stok')->where('minimal_stok', '>', 0)->get()
            ->map(function ($plant) {
                $available = (float) Stock::where('seed_varieties_id', $plant->getKey())->where('status_stok', Stock::STATUS_SIAP)->sum('stok_saat_ini');
                $plant->available_stock = $available;
                return $plant;
            })->filter(fn ($p) => $p->available_stock < (float) $p->minimal_stok)->values();

        $commodities = PlantType::orderBy('name')->get();

        $inventoryTypes = collect();
        
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
            'year',
            'certifiedThis',
            'certTrend',
            'passRate',
            'lossRate',
            'totalRevenue',
            'months',
            'prodMonthly',
            'saleMonthly',
            'stockByCategory',
            'stockByVariety',
            'prodByCategory',
            'prodByVariety',
            'saleByCategory',
            'saleByVariety',
            'lossMatrix',
            'geoPoints',
            'expiredLots',
            'lowStocks',
            'commodities',
            'commodityId',
            'plants',
            'inventoryTypes',
            'isAdmin',
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
            
            $harvestsQuery = CertificationHarvest::whereYear('tgl_panen', $date->year)
                ->whereMonth('tgl_panen', $date->month);

            // Filter by plant if specified
            if ($plantFilter && $plantFilter !== 'all') {
                $harvestsQuery->whereHas('planting.seedSource', fn ($q) => $q->where('seed_varieties_id', $plantFilter));
            }

            $totalTon = $this->convertToTon((float) $harvestsQuery->sum('volume_kotor_panen_kg'), 'kg');

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
        $plantsQuery = Plant::with(['type', 'satuanStok']);
        if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
            $plantsQuery->where('seed_varieties_id', $inventoryTypeFilter);
        }
        $plants = $plantsQuery->get();

        $soldRows = $this->soldQuantitiesByPlant($inventoryTypeFilter);

        $composition = [];
        foreach ($plants as $plant) {
            $soldQuantity = (float) data_get($soldRows->get($plant->seed_varieties_id), 'sold_qty', 0);
            $soldInKg = $this->convertToKg($soldQuantity, $plant->satuanStok?->code ?? 'kg');

            if ($soldInKg > 0) {
                $typeName = $plant->name;
                if ($plant->variety) {
                    $typeName .= ' - ' . $plant->variety;
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
            
            // Revenue from sale_items for this month
            $itemsQuery = SaleItem::whereYear('sale_date', $date->year)
                ->whereMonth('sale_date', $date->month);
            if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
                $itemsQuery->join('stock_packaging as sp', 'sp.id', '=', 'sale_items.stock_packaging_id')
                    ->join('stock as st', 'st.id', '=', 'sp.stok_benih_id')
                    ->where('st.seed_varieties_id', $inventoryTypeFilter);
            }
            $totalRevenue = $itemsQuery->sum('subtotal');
            
            $revenue[] = round($totalRevenue, 0);
        }
        
        return [
            'labels' => $months,
            'data' => $revenue
        ];
    }
    
    /**
     * Aggregate sold quantity / transaction count per plant variety
     * through sale_items -> stock_packaging -> stock.
     */
    private function soldQuantitiesByPlant($plantFilter = 'all')
    {
        return SaleItem::query()
            ->join('stock_packaging as sp', 'sp.id', '=', 'sale_items.stock_packaging_id')
            ->join('stock as st', 'st.id', '=', 'sp.stok_benih_id')
            ->whereNotNull('st.seed_varieties_id')
            ->select(
                'st.seed_varieties_id',
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as sold_qty'),
                DB::raw('COALESCE(SUM(sale_items.subtotal), 0) as total_revenue'),
                DB::raw('COUNT(sale_items.sale_item_id) as sold_products'),
                DB::raw('COUNT(DISTINCT sale_items.receipt_number) as sale_count')
            )
            ->when(
                $plantFilter && $plantFilter !== 'all',
                fn ($q) => $q->where('st.seed_varieties_id', $plantFilter)
            )
            ->groupBy('st.seed_varieties_id')
            ->get()
            ->keyBy('seed_varieties_id');
    }

    /**
     * Get seeds expiring in next 30 days (master lot level)
     */
    private function getExpiringSeeds()
    {
        $today = Carbon::today();
        $thirtyDaysLater = Carbon::today()->addDays(30);

        return \App\Models\Stock::with(['plant', 'rack.warehouse'])
            ->whereNotNull('tgl_kedaluwarsa')
            ->whereBetween('tgl_kedaluwarsa', [$today, $thirtyDaysLater])
            ->where('stok_saat_ini', '>', 0)
            ->orderBy('tgl_kedaluwarsa', 'asc')
            ->get()
            ->map(function ($stock) use ($today) {
                $stock->days_until_expiry = $today->diffInDays(Carbon::parse($stock->tgl_kedaluwarsa));
                return $stock;
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
            'btg' => 0.0001,
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
            'btg' => 0.1,
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
                return $this->normalizeWeatherData($response->json());
            }
        } catch (\Exception $e) {
            // Fallback data jika API tidak tersedia
            return $this->fallbackWeatherData();
        }

        return $this->fallbackWeatherData();
    }

    private function fallbackWeatherData(): array
    {
        return $this->normalizeWeatherData([
            'name' => 'Lubuk Minturun, Padang',
            'main' => [
                'temp' => 28,
                'temp_max' => 30,
                'temp_min' => 24,
                'feels_like' => 32,
                'humidity' => 80,
            ],
            'weather' => [
                [
                    'description' => 'Scattered Clouds',
                    'icon' => '03d',
                ],
            ],
            'wind' => [
                'speed' => 1,
            ],
            'clouds' => [
                'all' => 25,
            ],
        ]);
    }

    private function normalizeWeatherData(?array $data): ?array
    {
        if (! is_array($data) || empty($data['main'])) {
            return null;
        }

        $temp = $data['main']['temp'] ?? null;
        $data['main']['temp'] = $temp ?? 0;
        $data['main']['temp_max'] = $data['main']['temp_max'] ?? $temp ?? 0;
        $data['main']['temp_min'] = $data['main']['temp_min'] ?? $temp ?? 0;
        $data['main']['feels_like'] = $data['main']['feels_like'] ?? $temp ?? 0;
        $data['main']['humidity'] = $data['main']['humidity'] ?? 0;

        $data['weather'] = is_array($data['weather'] ?? null) ? $data['weather'] : [];
        $data['weather'][0] = is_array($data['weather'][0] ?? null) ? $data['weather'][0] : [];
        $data['weather'][0]['description'] = $data['weather'][0]['description'] ?? '-';

        $data['wind'] = is_array($data['wind'] ?? null) ? $data['wind'] : [];
        $data['wind']['speed'] = $data['wind']['speed'] ?? 0;

        $data['clouds'] = is_array($data['clouds'] ?? null) ? $data['clouds'] : [];
        $data['clouds']['all'] = $data['clouds']['all'] ?? 0;

        return $data;
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
                    $query->whereHas('planting', function ($q) use ($assignedLocationIds) {
                        $q->whereIn('planting_location_id', $assignedLocationIds);
                    });
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
                    $query->whereHas('planting', function ($q) use ($assignedLocationIds) {
                        $q->whereIn('planting_location_id', $assignedLocationIds);
                    });
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
                    $query->whereHas('planting', function ($q) use ($assignedLocationIds) {
                        $q->whereIn('planting_location_id', $assignedLocationIds);
                    });
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
                    $query->whereHas('planting', function ($q) use ($assignedLocationIds) {
                        $q->whereIn('planting_location_id', $assignedLocationIds);
                    });
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
        return PlantingPostHarvest::with(['planting.seedSource.variety', 'stock.plant'])
            ->where('status_kelulusan_lab', PlantingPostHarvest::LULUS)
            ->whereDate('tgl_kadaluarsa_mutu', '<', Carbon::today())
            ->orderBy('tgl_kadaluarsa_mutu')
            ->get()
            ->filter(function (PlantingPostHarvest $test) {
                // Sudah ada uji ulang yang lebih baru pada nomor induk yang sama.
                return ! PlantingPostHarvest::where('nomor_induk', $test->nomor_induk)
                    ->where('uji_ke', '>', (int) $test->uji_ke)
                    ->exists();
            })
            ->values();
    }
    
    /**
     * Get expiring seeds (within 3 months or expired) for responsible person
     */
    private function getExpiringSeedsForUser(User $user)
    {
        $today = Carbon::today();
        $limit = $today->copy()->addMonths(3);

        return Stock::with(['plant.type'])
            ->where('status_stok', Stock::STATUS_SIAP)
            ->where('stok_saat_ini', '>', 0)
            ->whereNotNull('tgl_kedaluwarsa')
            ->whereDate('tgl_kedaluwarsa', '<=', $limit)
            ->orderBy('tgl_kedaluwarsa')
            ->get()
            ->map(function (Stock $stock) use ($today) {
                $stock->notification_type = 'seed';
                $stock->expiry_date = $stock->tgl_kedaluwarsa;
                $stock->is_expired = $stock->tgl_kedaluwarsa?->lt($today);
                $stock->plant_id = $stock->seed_varieties_id;

                return $stock;
            });
    }
    
    /**
     * Get expired inventory lots in bins (for admin and warehouse staff)
     */
    private function getExpiredBinStocks()
    {
        $today = Carbon::today();

        // Kemasan benih yang masih tersimpan di rak tetapi lot induknya sudah kedaluwarsa
        $expiredPackagings = \App\Models\StockPackaging::with([
            'stock.plant',
            'rack.warehouse',
        ])
        ->whereNotNull('rak_gudang_id')
        ->whereIn('status_kemasan', [
            \App\Models\StockPackaging::STATUS_TERSEDIA,
            \App\Models\StockPackaging::STATUS_TIDAK_AKTIF,
        ])
        ->whereHas('stock', function ($q) use ($today) {
            $q->whereNotNull('tgl_kedaluwarsa')->whereDate('tgl_kedaluwarsa', '<', $today);
        })
        ->get()
        ->groupBy('rak_gudang_id')
        ->map(function ($packagings) use ($today) {
            $rack = $packagings->first()->rack;
            $warehouse = $rack?->warehouse;

            return [
                'warehouse_id' => $warehouse->warehouse_id ?? null,
                'warehouse_name' => $warehouse->name ?? 'Gudang Tidak Diketahui',
                'warehouse_bin_id' => $rack->warehouse_bin_id ?? null,
                'bin_name' => $rack->name ?? 'Rak Tidak Diketahui',
                'bin_internal_id' => $rack->internal_id ?? '-',
                'expired_count' => $packagings->count(),
                'total_expired_stock' => $packagings->sum('kapasitas_per_kemasan'),
                'lots' => $packagings->map(function ($packaging) use ($today) {
                    $expiry = $packaging->stock?->tgl_kedaluwarsa;

                    return [
                        'id' => $packaging->id,
                        'inventory_type_name' => $packaging->stock?->plant?->name ?? '-',
                        'production_id' => $packaging->no_label_seri,
                        'current_stock' => $packaging->kapasitas_per_kemasan,
                        'stock_unit' => $packaging->stock?->plant?->satuanStok?->code ?? '',
                        'expiry_date' => $expiry?->format('d M Y') ?? '-',
                        'days_expired' => $expiry ? $expiry->diffInDays($today) : 0,
                    ];
                })->values(),
                'notification_type' => 'expired_bin_stock',
            ];
        })
        ->values();

        return $expiredPackagings;
    }
    
    /**
     * Get low stock notifications (for admin and warehouse staff)
     */
    private function getLowStockNotifications()
    {
        // Varietas benih yang punya minimal stok (mengikuti satuan stok)
        $plants = Plant::with(['satuanStok', 'type'])
            ->whereNotNull('minimal_stok')
            ->where('minimal_stok', '>', 0)
            ->get();

        $lowStockItems = collect();

        foreach ($plants as $plant) {
            $unit = $plant->satuanStok?->code ?? '';
            $totalStock = (float) \App\Models\Stock::where('seed_varieties_id', $plant->seed_varieties_id)
                ->where('status_stok', \App\Models\Stock::STATUS_SIAP)
                ->sum('stok_saat_ini');
            $threshold = (float) $plant->minimal_stok;

            if ($totalStock < $threshold) {
                $lowStockItems->push([
                    'inventory_type_id' => $plant->seed_varieties_id,
                    'inventory_type_name' => $plant->name,
                    'plant_name' => $plant->name,
                    'variety' => $plant->variety,
                    'category' => $plant->type->name ?? null,
                    'sku' => $plant->seed_varieties_id,
                    'current_stock' => $totalStock,
                    'stock_unit' => $unit,
                    'threshold' => $threshold,
                    'threshold_unit' => $unit,
                    'difference' => abs($threshold - $totalStock),
                    'notification_type' => 'low_stock',
                ]);
            }
        }

        return $lowStockItems->sortBy('difference')->values();
    }
    
    /**
     * Get production table data (all plantings grouped by plant, then category)
     */
    private function getProductionTableData($plantFilter = 'all')
    {
        $plantings = Planting::with(['seedSource.variety.type', 'seedSource.variety.satuanPanen', 'postHarvests', 'certificationHarvests'])
            ->when(
                $plantFilter && $plantFilter !== 'all',
                fn ($q) => $q->whereHas('seedSource', fn ($s) => $s->where('seed_varieties_id', $plantFilter))
            )
            ->get();

        return $plantings
            ->filter(fn ($planting) => $planting->seedSource?->seed_varieties_id && $planting->seedSource?->variety)
            ->groupBy(fn ($planting) => $planting->seedSource->seed_varieties_id)
            ->map(function ($group, $plantId) {
                $plant = $group->first()->seedSource?->variety;
                $unit = $plant?->satuanPanen?->code ?? 'kg';
                $postHarvests = $group->flatMap(fn ($planting) => $planting->postHarvests);
                $fieldHarvests = $group->flatMap(fn ($planting) => $planting->certificationHarvests);
                $totalQuantity = (float) $postHarvests->sum(function ($row) {
                    $lab = (float) ($row->total_hasil_uji ?? 0);
                    $realisasi = (float) ($row->realisasi_produksi ?? 0);

                    return $lab > 0 ? $lab : $realisasi;
                });
                if ($totalQuantity <= 0) {
                    $totalQuantity = (float) $fieldHarvests->sum('volume_kotor_panen_kg');
                }
                if ($totalQuantity <= 0) {
                    $totalQuantity = (float) $group->sum(fn ($planting) => (float) ($planting->planting_amount ?? 0));
                }
                $latestHarvest = $postHarvests->max('tgl_panen') ?: $fieldHarvests->max('tgl_panen');
                $harvestCount = max($postHarvests->count(), $fieldHarvests->count(), $group->count());

                return [
                    'plant_id' => $plantId,
                    'plant_name' => $plant->name ?? 'N/A',
                    'variety' => $plant->variety ?? null,
                    'category' => $plant?->type?->category ?: 'Lainnya',
                    'total_quantity' => $totalQuantity,
                    'total_ton' => round($this->convertToTon($totalQuantity, $unit), 2),
                    'unit' => $unit,
                    'harvest_count' => $harvestCount,
                    'latest_harvest_date' => $latestHarvest,
                ];
            })
            ->sortBy(fn ($row) => mb_strtolower(($row['category'] ?? 'Lainnya').'|'.($row['plant_name'] ?? '').'|'.($row['variety'] ?? '')))
            ->values();
    }
    
    /**
     * Get stock table data (inventory types with sold quantities)
     */
    private function getStockTableData($inventoryTypeFilter = 'all')
    {
        $plantsQuery = Plant::with(['type', 'satuanStok']);
        if ($inventoryTypeFilter && $inventoryTypeFilter !== 'all') {
            $plantsQuery->where('seed_varieties_id', $inventoryTypeFilter);
        }
        $plants = $plantsQuery->get();

        $soldRows = $this->soldQuantitiesByPlant($inventoryTypeFilter);
        [$adjustmentQty, $adjustmentProducts] = $this->adjustmentQuantitiesByPlant($inventoryTypeFilter);

        $tableData = $plants->map(function ($plant) use ($soldRows, $adjustmentQty, $adjustmentProducts) {
            $sold = $soldRows->get($plant->seed_varieties_id);
            $unit = $plant->satuanStok?->code ?? 'kg';
            $totalSoldQuantity = (float) data_get($sold, 'sold_qty', 0);
            $totalSoldInKg = $this->convertToKg($totalSoldQuantity, $unit);
            $soldProducts = (int) data_get($sold, 'sold_products', 0);

            $currentStock = (float) \App\Models\Stock::where('seed_varieties_id', $plant->seed_varieties_id)
                ->where('status_stok', \App\Models\Stock::STATUS_SIAP)
                ->sum('stok_saat_ini');
            $currentStockInKg = $this->convertToKg($currentStock, $unit);
            $adjustedQty = (float) $adjustmentQty->get($plant->seed_varieties_id, 0);
            $adjustedProducts = (int) $adjustmentProducts->get($plant->seed_varieties_id, 0);

            return [
                'inventory_type_id' => $plant->seed_varieties_id,
                'inventory_type_name' => $plant->name,
                'plant_name' => $plant->name,
                'variety' => $plant->variety,
                'category' => $plant->type?->category ?: 'Lainnya',
                'sku' => $plant->seed_varieties_id,
                'current_stock' => $currentStock,
                'current_stock_kg' => round($currentStockInKg, 2),
                'sold_quantity' => $totalSoldQuantity,
                'sold_quantity_kg' => round($totalSoldInKg, 2),
                'sold_products' => $soldProducts,
                'adjusted_quantity' => $adjustedQty,
                'adjusted_products' => $adjustedProducts,
                'unit' => $unit,
                'sale_count' => (int) data_get($sold, 'sale_count', 0),
                'harga_jual' => (float) ($plant->harga_jual ?? 0),
                'minimal_stok' => $plant->minimal_stok !== null ? (float) $plant->minimal_stok : null,
                'is_low_stock' => $plant->minimal_stok !== null && (float) $plant->minimal_stok > 0 && $currentStock < (float) $plant->minimal_stok,
            ];
        })
        ->sortBy(fn ($row) => mb_strtolower(($row['category'] ?? 'Lainnya').'|'.($row['plant_name'] ?? '').'|'.($row['variety'] ?? '')))
        ->values();

        return $tableData;
    }
    
    /**
     * Get revenue table data (sales grouped by inventory type)
     */
    private function getRevenueTableData($inventoryTypeFilter = 'all')
    {
        $rows = $this->soldQuantitiesByPlant($inventoryTypeFilter);
        $plantIds = $rows->keys()->values();
        $plants = Plant::with(['type', 'satuanStok'])
            ->whereIn('seed_varieties_id', $plantIds)
            ->get()
            ->keyBy('seed_varieties_id');

        return $rows->map(function ($row, $plantId) use ($plants) {
            $plant = $plants->get($plantId);
            $totalRevenue = (float) ($row->total_revenue ?? 0);
            $totalQuantity = (float) ($row->sold_qty ?? 0);

            return [
                'inventory_type_id' => $plantId,
                'inventory_type_name' => $plant?->name ?? 'N/A',
                'plant_name' => $plant?->name ?? 'N/A',
                'variety' => $plant?->variety,
                'category' => $plant?->type?->category ?: 'Lainnya',
                'sku' => $plantId,
                'total_revenue' => round($totalRevenue, 2),
                'total_quantity' => round($totalQuantity, 2),
                'sold_products' => (int) ($row->sold_products ?? 0),
                'unit' => $plant?->satuanStok?->code ?? '',
                'sale_count' => (int) ($row->sale_count ?? 0),
                'average_price' => $totalQuantity > 0 ? round($totalRevenue / $totalQuantity, 2) : 0,
            ];
        })
        ->sortBy(fn ($row) => mb_strtolower(($row['category'] ?? 'Lainnya').'|'.($row['plant_name'] ?? '').'|'.($row['variety'] ?? '')))
        ->values();
    }

    /**
     * Build category totals + variety drill-down rows from a per-plant quantity map.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Plant>  $plants
     * @param  \Illuminate\Support\Collection<string, float|int|string>  $qtyByPlantId
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function composeCategoryCharts($plants, $qtyByPlantId): array
    {
        $byVariety = $plants->map(function (Plant $plant) use ($qtyByPlantId) {
            return [
                'category' => $plant->type?->category ?: 'Lainnya',
                'label' => trim(($plant->name ?? '').($plant->variety ? ' · '.$plant->variety : '')) ?: ($plant->variety ?: $plant->name),
                'value' => (float) $qtyByPlantId->get($plant->getKey(), 0),
            ];
        })->filter(fn ($row) => $row['value'] > 0)->values();

        $byCategory = $byVariety
            ->groupBy('category')
            ->map(fn ($rows, $category) => [
                'label' => $category ?: 'Lainnya',
                'value' => (float) $rows->sum('value'),
            ])
            ->filter(fn ($row) => $row['value'] > 0)
            ->values();

        return [$byCategory, $byVariety];
    }

    /**
     * Adjustment volume (satuan) and adjusted packaging count (produk) per plant.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function adjustmentQuantitiesByPlant($plantFilter = 'all'): array
    {
        $qty = StockHistory::query()
            ->whereIn('transaction_type', ['penyesuaian_tambah', 'penyesuaian_kurang'])
            ->whereNotNull('seed_varieties_id')
            ->when(
                $plantFilter && $plantFilter !== 'all',
                fn ($q) => $q->where('seed_varieties_id', $plantFilter)
            )
            ->select('seed_varieties_id', DB::raw('SUM(ABS(quantity)) as qty'))
            ->groupBy('seed_varieties_id')
            ->pluck('qty', 'seed_varieties_id');

        $products = StockPackaging::query()
            ->join('stock as st', 'st.id', '=', 'stock_packaging.stok_benih_id')
            ->whereNotNull('st.seed_varieties_id')
            ->where(function ($q) {
                $q->whereNotNull('stock_packaging.tgl_penyesuaian')
                    ->orWhereIn('stock_packaging.status_kemasan', [
                        StockPackaging::STATUS_TIDAK_AKTIF,
                        StockPackaging::STATUS_AFKIR,
                    ]);
            })
            ->when(
                $plantFilter && $plantFilter !== 'all',
                fn ($q) => $q->where('st.seed_varieties_id', $plantFilter)
            )
            ->select('st.seed_varieties_id', DB::raw('COUNT(stock_packaging.id) as cnt'))
            ->groupBy('st.seed_varieties_id')
            ->pluck('cnt', 'st.seed_varieties_id');

        return [$qty, $products];
    }
}

