<?php

namespace App\Console\Commands;

use App\Jobs\SendExpiredBinStockNotificationJob;
use App\Models\InventoryLot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredBinStockAndNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sibesti:check-expired-bin-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired bin stock and send email notifications to warehouse staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired bin stock...');
        
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
            return $lot->warehouse_id . '-' . $lot->bin_id;
        })
        ->map(function($lots, $key) use ($today) {
            $firstLot = $lots->first();
            $warehouse = $firstLot->warehouse;
            $bin = $firstLot->bin;
            
            return [
                'warehouse_id' => $warehouse->id ?? null,
                'warehouse_name' => $warehouse->name ?? 'Gudang Tidak Diketahui',
                'bin_id' => $bin->id ?? null,
                'bin_name' => $bin->name ?? 'Bin Tidak Diketahui',
                'bin_internal_id' => $bin->internal_id ?? '-',
                'expired_count' => $lots->count(),
                'total_expired_stock' => $lots->sum('current_stock'),
                'lots' => $lots->map(function($lot) use ($today) {
                    return [
                        'id' => $lot->id,
                        'inventory_type_name' => $lot->inventoryType->name ?? '-',
                        'production_id' => $lot->production_id ?? 'Lot #' . $lot->id,
                        'current_stock' => $lot->current_stock,
                        'stock_unit' => $lot->stock_unit,
                        'expiry_date' => $lot->expiry_date->format('d M Y'),
                        'days_expired' => $lot->expiry_date->diffInDays($today),
                    ];
                }),
            ];
        })
        ->values()
        ->toArray();
        
        if (empty($expiredLots)) {
            $this->info('No expired bin stock found.');
            return 0;
        }
        
        $this->info('Found ' . count($expiredLots) . ' bins with expired stock.');
        
        // Get all admin and petugas_gudang users
        $users = User::whereIn('role', ['admin', 'petugas_gudang'])
            ->whereNotNull('email')
            ->get();
        
        foreach ($users as $user) {
            $this->info('Dispatching email notification to: ' . $user->email);
            SendExpiredBinStockNotificationJob::dispatch($expiredLots, $user->id);
        }
        
        $this->info('Expired bin stock notifications dispatched successfully.');
        return 0;
    }
}
