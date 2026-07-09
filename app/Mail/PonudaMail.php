<?php

namespace App\Mail;

use App\Models\Tvrtka;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PonudaMail extends Mailable
{
    public function __construct(
        public string $poruka,
        public string $pdfSadrzaj,
        public string $pdfNaziv,
        public array $dodatniPrivitci = [],
        public ?Tvrtka $tvrtka = null,
        public ?string $prihvatiUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject ?? 'Ponuda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.ponuda',
            with: ['poruka' => $this->poruka, 'tvrtka' => $this->tvrtka, 'prihvatiUrl' => $this->prihvatiUrl],
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
