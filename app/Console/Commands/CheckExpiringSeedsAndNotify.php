<?php

namespace App\Console\Commands;

use App\Jobs\SendExpiringSeedsNotificationJob;
use App\Models\Stock;
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
        
        // Lot stok benih yang sudah kedaluwarsa atau akan kedaluwarsa dalam 14 hari
        $expiringSeeds = Stock::with(['plant.satuanStok', 'postHarvest', 'rack.warehouse'])
            ->whereNotNull('tgl_kedaluwarsa')
            ->whereBetween('tgl_kedaluwarsa', [$today->copy()->subDays(30), $fourteenDaysFromNow])
            ->where('stok_saat_ini', '>', 0)
            ->orderBy('tgl_kedaluwarsa')
            ->get()
            ->map(function (Stock $stock) use ($today) {
                $expiry = $stock->tgl_kedaluwarsa;
                $isExpired = $expiry->isPast();

                return [
                    'id' => $stock->id,
                    'inventory_type_id' => $stock->seed_varieties_id,
                    'name' => $stock->plant->name ?? 'Benih',
                    'variety' => $stock->plant->variety ?? null,
                    'batch_no' => $stock->postHarvest?->nomor_lot ?? $stock->no_label_resmi ?? '-',
                    'location' => $stock->rack?->warehouse?->name ?? 'Tidak Diketahui',
                    'expiry_date' => $expiry->format('d M Y'),
                    'is_expired' => $isExpired,
                    'days_until' => $isExpired ? $expiry->diffInDays($today) : $today->diffInDays($expiry),
                    'stock_quantity' => (float) $stock->stok_saat_ini,
                    'stock_unit' => $stock->plant?->satuanStok?->code ?? 'kg',
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
