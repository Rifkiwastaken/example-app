<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lowStockItems;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(array $lowStockItems, User $user)
    {
        $this->lowStockItems = $lowStockItems;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Peringatan: Stok Benih Rendah')
                    ->view('emails.low-stock-notification')
                    ->with([
                        'lowStockItems' => $this->lowStockItems,
                        'userName' => $this->user->name,
                        'totalItems' => count($this->lowStockItems),
                    ]);
    }
}
