<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TaskReminderDigestMail extends Mailable
{
    use Queueable;

    /** @param Collection<int, Task> $tasks */
    public function __construct(
        public User $recipient,
        public Collection $tasks,
        public Carbon $reminderDate,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorio de tareas pendientes');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.task-reminder-digest');
    }
}
