<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PretplataPodsjetnikMail extends Mailable
{
    public function __construct(
        public string $poruka,
        public string $subject,
        public ?string $cc = null,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(subject: $this->subject);

        if ($this->cc) {
            $envelope = new Envelope(
                subject: $this->subject,
                cc: [new Address($this->cc)],
            );
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.racun',
            with: ['poruka' => $this->poruka],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
