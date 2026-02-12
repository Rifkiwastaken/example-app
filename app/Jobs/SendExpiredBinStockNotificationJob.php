<?php

namespace App\Jobs;

use App\Mail\ExpiredBinStockNotificationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendExpiredBinStockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $expiredBins;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $expiredBins, int $userId)
    {
        $this->expiredBins = $expiredBins;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        
        if ($user && $user->email && ($user->isAdmin() || $user->role === 'petugas_gudang')) {
            try {
                Mail::to($user->email)->send(new ExpiredBinStockNotificationMail($this->expiredBins, $user));
            } catch (\Exception $e) {
                \Log::error('Failed to send expired bin stock notification email: ' . $e->getMessage());
            }
        }
    }
}
