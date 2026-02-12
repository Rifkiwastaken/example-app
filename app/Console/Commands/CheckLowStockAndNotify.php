<?php

namespace App\Console\Commands;

use App\Jobs\SendLowStockNotificationJob;
use App\Models\InventoryType;
use App\Models\User;
use Illuminate\Console\Command;

class CheckLowStockAndNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sibesti:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock and send email notifications to warehouse staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low stock items...');
        
        // Get all inventory types that have low_stock_threshold set
        $inventoryTypes = InventoryType::whereNotNull('low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->with(['plant.type'])
            ->get();
        
        $lowStockItems = [];
        
        foreach ($inventoryTypes as $type) {
            // Get total stock from seeds (certified seeds)
            $totalStock = $type->seeds()->sum('total_seed_quantity') ?? 0;
            
            // Get threshold value
            $threshold = $type->low_stock_threshold ?? 0;
            $thresholdUnit = $type->low_stock_unit ?? 'kg';
            $stockUnit = $type->unit ?? 'kg';
            
            // Convert both to kg for comparison
            $totalStockInKg = $totalStock;
            $thresholdInKg = $threshold;
            
            if ($stockUnit === 'ton') {
                $totalStockInKg = $totalStock * 1000;
            } elseif ($stockUnit === 'gram') {
                $totalStockInKg = $totalStock / 1000;
            }
            
            if ($thresholdUnit === 'ton') {
                $thresholdInKg = $threshold * 1000;
            } elseif ($thresholdUnit === 'gram') {
                $thresholdInKg = $threshold / 1000;
            }
            
            // Check if stock is below threshold
            if ($totalStockInKg < $thresholdInKg) {
                $lowStockItems[] = [
                    'inventory_type_id' => $type->id,
                    'inventory_type_name' => $type->name,
                    'plant_name' => $type->plant->name ?? $type->name,
                    'variety' => $type->plant->variety ?? null,
                    'current_stock' => $totalStock,
                    'stock_unit' => $stockUnit,
                    'threshold' => $threshold,
                    'threshold_unit' => $thresholdUnit,
                ];
            }
        }
        
        if (empty($lowStockItems)) {
            $this->info('No low stock items found.');
            return 0;
        }
        
        $this->info('Found ' . count($lowStockItems) . ' low stock items.');
        
        // Get all admin and petugas_gudang users
        $users = User::whereIn('role', ['admin', 'petugas_gudang'])
            ->whereNotNull('email')
            ->get();
        
        foreach ($users as $user) {
            $this->info('Dispatching email notification to: ' . $user->email);
            SendLowStockNotificationJob::dispatch($lowStockItems, $user->id);
        }
        
        $this->info('Low stock notifications dispatched successfully.');
        return 0;
    }
}
