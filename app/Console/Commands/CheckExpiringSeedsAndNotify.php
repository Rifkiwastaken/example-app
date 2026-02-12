<?php

namespace App\Console\Commands;

use App\Jobs\SendExpiringSeedsNotificationJob;
use App\Models\InventoryType;
use App\Models\InventoryTypeSeed;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringSeedsAndNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sibesti:check-expiring-seeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring seeds (H-14 or expired) and send email notification to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expiring seeds...');
        
        $today = Carbon::today();
        $fourteenDaysFromNow = $today->copy()->addDays(14);
        
        // Get all seeds that are expired or will expire within 14 days
        $expiringSeeds = InventoryTypeSeed::with([
            'inventoryType',
            'plant.type',
            'plantingLocation'
        ])
        ->whereNotNull('expiry_date')
        ->where(function($query) use ($today, $fourteenDaysFromNow) {
            $query->where('expiry_date', '<=', $fourteenDaysFromNow)
                  ->where('expiry_date', '>=', $today->copy()->subDays(30)); // Include expired up to 30 days ago
        })
        ->orderBy('expiry_date', 'asc')
        ->get()
        ->map(function($seed) use ($today) {
            $isExpired = $seed->expiry_date->isPast();
            $daysUntil = $isExpired 
                ? $seed->expiry_date->diffInDays($today) 
                : $today->diffInDays($seed->expiry_date);
            
            // Get batch number from certification report if available
            $batchNo = '-';
            $certificationReport = null;
            if ($seed->certification_report_id) {
                $certificationReport = \App\Models\CertificationReport::find($seed->certification_report_id);
                if ($certificationReport) {
                    $batchNo = $certificationReport->batch_no ?? '-';
                }
            }
            
            return [
                'id' => $seed->id,
                'inventory_type_id' => $seed->inventory_type_id,
                'name' => $seed->plant->name ?? $seed->inventoryType->name ?? 'Benih',
                'variety' => $seed->plant->variety ?? null,
                'batch_no' => $batchNo,
                'location' => $seed->plantingLocation->name ?? 'Tidak Diketahui',
                'expiry_date' => $seed->expiry_date->format('d M Y'),
                'is_expired' => $isExpired,
                'days_until' => $daysUntil,
                'stock_quantity' => $seed->total_seed_quantity ?? 0,
                'stock_unit' => $seed->total_seed_unit ?? 'kg',
            ];
        })
        ->toArray();
        
        if (empty($expiringSeeds)) {
            $this->info('No expiring seeds found.');
            return 0;
        }
        
        $expiredCount = collect($expiringSeeds)->where('is_expired', true)->count();
        $nearExpiryCount = collect($expiringSeeds)->where('is_expired', false)->count();
        
        $this->info('Found ' . count($expiringSeeds) . ' expiring seeds (' . $expiredCount . ' expired, ' . $nearExpiryCount . ' near expiry).');
        
        // Send to admin email
        $adminEmail = 'ahmadfarid0410@gmail.com';
        $this->info('Dispatching email notification to admin: ' . $adminEmail);
        SendExpiringSeedsNotificationJob::dispatch($expiringSeeds, $adminEmail);
        
        $this->info('Expiring seeds notification dispatched successfully.');
        return 0;
    }
}
