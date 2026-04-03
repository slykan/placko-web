<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RacunMail extends Mailable
{
    public function __construct(
        public string $poruka,
        public string $pdfSadrzaj,
        public string $pdfNaziv,
        public array $dodatniPrivitci = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject ?? 'Račun',
        );
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
        $privitci = [
            Attachment::fromData(fn () => $this->pdfSadrzaj, $this->pdfNaziv)
                ->withMime('application/pdf'),
        ];

        foreach ($this->dodatniPrivitci as $put) {
            if (file_exists($put)) {
                $privitci[] = Attachment::fromPath($put);
            }
        }

        return $privitci;
    }
}
