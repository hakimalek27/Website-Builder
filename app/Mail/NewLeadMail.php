<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// §5.1 / §13 — notifikasi admin bila lead baharu masuk.
class NewLeadMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lead baharu: '.$this->lead->mosque_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-lead',
            with: ['lead' => $this->lead],
        );
    }
}
