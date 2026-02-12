<?php

namespace App\Mail;

use App\Models\PlantingLocationNote;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NoteNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $note;
    public $user;
    public $locationName;

    /**
     * Create a new message instance.
     */
    public function __construct(PlantingLocationNote $note, User $user, string $locationName = 'Umum')
    {
        $this->note = $note;
        $this->user = $user;
        $this->locationName = $locationName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $detailUrl = route('planting-locations.show', $this->note->planting_location_id);
        
        return $this->subject('Catatan Baru: ' . ($this->note->title ?: 'Catatan Tanpa Judul'))
                    ->view('emails.note-notification')
                    ->with([
                        'noteTitle' => $this->note->title ?: 'Catatan Tanpa Judul',
                        'noteContent' => $this->note->content,
                        'locationName' => $this->locationName,
                        'detailUrl' => $detailUrl,
                        'userName' => $this->user->name,
                        'createdAt' => $this->note->created_at->format('d M Y H:i'),
                    ]);
    }
}
