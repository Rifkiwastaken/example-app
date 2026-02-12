<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredBinStockNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $expiredBins;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(array $expiredBins, User $user)
    {
        $this->expiredBins = $expiredBins;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Peringatan: Benih Kadaluarsa di Bin')
                    ->view('emails.expired-bin-stock-notification')
                    ->with([
                        'expiredBins' => $this->expiredBins,
                        'userName' => $this->user->name,
                        'totalBins' => count($this->expiredBins),
                    ]);
    }
}
