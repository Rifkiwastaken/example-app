<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiringSeedsNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $expiringSeeds;
    public $adminEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(array $expiringSeeds, string $adminEmail = 'ahmadfarid0410@gmail.com')
    {
        $this->expiringSeeds = $expiringSeeds;
        $this->adminEmail = $adminEmail;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $expiredCount = collect($this->expiringSeeds)->where('is_expired', true)->count();
        $nearExpiryCount = collect($this->expiringSeeds)->where('is_expired', false)->count();
        
        return $this->subject('Peringatan: Benih Mendekati/Melahwati Masa Kadaluarsa - Perlu Sertifikasi Ulang')
                    ->view('emails.expiring-seeds-notification')
                    ->with([
                        'expiringSeeds' => $this->expiringSeeds,
                        'expiredCount' => $expiredCount,
                        'nearExpiryCount' => $nearExpiryCount,
                        'totalCount' => count($this->expiringSeeds),
                    ]);
    }
}
