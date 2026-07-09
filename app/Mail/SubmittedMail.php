<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// §13 — notifikasi admin bila PIC menghantar borang.
class SubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Borang dihantar: '.$this->project->mosque_name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.submitted', with: ['project' => $this->project]);
    }
}
