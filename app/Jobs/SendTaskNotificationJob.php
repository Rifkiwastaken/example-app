<?php

namespace App\Jobs;

use App\Mail\TaskNotificationMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $task;
    public $userIds;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task, array $userIds)
    {
        $this->task = $task;
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load task with relationships
        $this->task->load(['plantingLocation']);
        
        $locationName = $this->task->plantingLocation->name ?? 'Umum';
        
        // Send email to each assigned user
        foreach ($this->userIds as $userId) {
            $user = User::find($userId);
            
            if ($user && $user->email) {
                // Check if user is penangkar or kepala_satuan_tugas
                if (in_array($user->role, ['penangkar', 'kepala_satuan_tugas'])) {
                    try {
                        Mail::to($user->email)->send(new TaskNotificationMail($this->task, $user, $locationName));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send task notification email: ' . $e->getMessage());
                    }
                }
            }
        }
    }
}
