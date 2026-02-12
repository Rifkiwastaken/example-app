<?php

namespace App\Jobs;

use App\Mail\ExpiringSeedsNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendExpiringSeedsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $expiringSeeds;
    public $adminEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(array $expiringSeeds, string $adminEmail = 'ahmadfarid0410@gmail.com')
    {
        $this->expiringSeeds = $expiringSeeds;
        $this->adminEmail = $adminEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->adminEmail)->send(new ExpiringSeedsNotificationMail($this->expiringSeeds, $this->adminEmail));
        } catch (\Exception $e) {
            \Log::error('Failed to send expiring seeds notification email: ' . $e->getMessage());
        }
    }
}
