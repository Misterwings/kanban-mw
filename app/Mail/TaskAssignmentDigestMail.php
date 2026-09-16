<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class TaskAssignmentDigestMail extends Mailable
{
    use Queueable;

    /** @param Collection<int, Task> $tasks */
    public function __construct(public User $recipient, public Collection $tasks)
    {
    }

    public function envelope(): Envelope
    {
        $count = $this->tasks->count();

        return new Envelope(
            subject: $count === 1 ? 'Nueva tarea asignada' : "{$count} nuevas tareas asignadas",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.task-assignment-digest');
    }
}
