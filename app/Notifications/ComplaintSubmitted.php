<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ComplaintSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Complaint $complaint,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'complaint_id' => $this->complaint->id,
            'title' => $this->complaint->title,
            'description' => $this->complaint->description,
            'priority' => $this->complaint->priority,
            'student_name' => $this->complaint->user->name,
        ]);
    }
}
