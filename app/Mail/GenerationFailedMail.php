<?php

namespace App\Mail;

use App\Models\Generation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// §8.6 langkah 7 / §13 — maklum admin bila penjanaan gagal muktamad.
class GenerationFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Generation $generation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Penjanaan draf GAGAL: '.$this->generation->project->mosque_name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.generation-failed', with: ['generation' => $this->generation]);
    }
}
