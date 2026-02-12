<?php

namespace App\Jobs;

use App\Mail\NoteNotificationMail;
use App\Models\PlantingLocationNote;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNoteNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $note;
    public $userIds;

    /**
     * Create a new job instance.
     */
    public function __construct(PlantingLocationNote $note, array $userIds)
    {
        $this->note = $note;
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load note with relationships
        $this->note->load(['plantingLocation']);
        
        $locationName = $this->note->plantingLocation->name ?? 'Umum';
        
        // Send email to each assigned user
        foreach ($this->userIds as $userId) {
            $user = User::find($userId);
            
            if ($user && $user->email) {
                // Check if user is penangkar or kepala_satuan_tugas
                if (in_array($user->role, ['penangkar', 'kepala_satuan_tugas'])) {
                    try {
                        Mail::to($user->email)->send(new NoteNotificationMail($this->note, $user, $locationName));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send note notification email: ' . $e->getMessage());
                    }
                }
            }
        }
    }
}
