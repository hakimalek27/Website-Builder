<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

// §13 — mel amaran admin ringkas (fallback bila WhatsApp gagal).
class AdminAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $subjectLine, public string $bodyText) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'REKA — '.$this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(htmlString: (new HtmlString('<p>'.e($this->bodyText).'</p>'))->toHtml());
    }
}
