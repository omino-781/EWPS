<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EpmsNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailTitle,
        public string $mailMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailTitle);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'title' => $this->mailTitle,
                'body' => $this->mailMessage,
            ],
        );
    }
}
