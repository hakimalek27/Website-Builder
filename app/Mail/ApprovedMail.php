<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// §13 — maklum admin bila PIC meluluskan draf.
class ApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Draf DILULUSKAN: '.$this->project->mosque_name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.approved', with: ['project' => $this->project]);
    }
}
