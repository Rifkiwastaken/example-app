<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Harvest;
use App\Models\InventoryLot;
use App\Models\InventoryType;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $weatherData = $this->getWeatherData();
        
        // 1. Grafik Tren Produksi (12 bulan terakhir)
        $productionTrend = $this->getProductionTrend();
        
        // 2. Pie Chart Stok (Komposisi berdasarkan kategori)
        $stockComposition = $this->getStockComposition();
        
        // 3. Grafik Pendapatan (12 bulan terakhir)
        $revenueTrend = $this->getRevenueTrend();
        
        // 4. Alert: Benih yang akan kadaluarsa dalam 30 hari
        $expiringSeeds = $this->getExpiringSeeds();
        
        return view('dashboard.index', compact(
            'weatherData',
            'productionTrend',
            'stockComposition',
            'revenueTrend',
            'expiringSeeds'
        ));
    }
    
    /**
     * Get production trend data (last 12 months)
     */
    private function getProductionTrend()
    {
        $months = [];
        $production = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $date->format('M');
            
            $harvests = Harvest::whereYear('harvested_at', $date->year)
                ->whereMonth('harvested_at', $date->month)
                ->get();
            
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
     * Get stock composition by category
     */
    private function getStockComposition()
    {
        $lots = InventoryLot::with('inventoryType')
            ->where('current_stock', '>', 0)
            ->get();
        
        $categories = [
            'Padi' => 0,
            'Jagung' => 0,
            'Hortikultura' => 0,
            'Lainnya' => 0
        ];
        
        foreach ($lots as $lot) {
            $category = $lot->inventoryType->category ?? 'Lainnya';
            
            // Convert to kg for comparison
            $stockInKg = $lot->current_stock;
            if ($lot->stock_unit) {
                $stockInKg = $this->convertToKg($lot->current_stock, $lot->stock_unit);
            }
            
            // Map category
            if (stripos($category, 'padi') !== false || stripos($category, 'rice') !== false) {
                $categories['Padi'] += $stockInKg;
            } elseif (stripos($category, 'jagung') !== false || stripos($category, 'corn') !== false || stripos($category, 'maize') !== false) {
                $categories['Jagung'] += $stockInKg;
            } elseif (stripos($category, 'horti') !== false || stripos($category, 'vegetable') !== false || stripos($category, 'sayur') !== false) {
                $categories['Hortikultura'] += $stockInKg;
            } else {
                $categories['Lainnya'] += $stockInKg;
            }
        }
        
        // Remove zero categories
        $categories = array_filter($categories, function($value) {
            return $value > 0;
        });
        
        return [
            'labels' => array_keys($categories),
            'data' => array_values($categories)
        ];
    }
    
    /**
     * Get revenue trend data (last 12 months)
     */
    private function getRevenueTrend()
    {
        $months = [];
        $revenue = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            
            $sales = Sale::whereYear('sale_date', $date->year)
                ->whereMonth('sale_date', $date->month)
                ->sum('total_amount');
            
            $revenue[] = round($sales, 0);
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
        
        return InventoryLot::with(['inventoryType', 'warehouse', 'certification'])
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
}

