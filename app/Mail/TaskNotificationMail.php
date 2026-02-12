<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $user;
    public $locationName;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, User $user, string $locationName = 'Umum')
    {
        $this->task = $task;
        $this->user = $user;
        $this->locationName = $locationName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $dueDate = $this->task->due_date ? $this->task->due_date->format('d M Y') : '-';
        $dueTime = $this->task->due_time ? $this->task->due_time->format('H:i') : null;
        
        $detailUrl = route('planting-locations.show', $this->task->planting_location_id);
        
        return $this->subject('Tugas Baru: ' . $this->task->title)
                    ->view('emails.task-notification')
                    ->with([
                        'taskTitle' => $this->task->title,
                        'taskDescription' => $this->task->description,
                        'locationName' => $this->locationName,
                        'dueDate' => $dueDate,
                        'dueTime' => $dueTime,
                        'priority' => $this->task->new_priority ?? 'medium',
                        'status' => $this->task->new_status ?? 'dilakukan',
                        'detailUrl' => $detailUrl,
                        'userName' => $this->user->name,
                    ]);
    }
}
