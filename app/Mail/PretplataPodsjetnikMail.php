<?php

namespace App\Mail;

use App\Models\Tvrtka;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PretplataPodsjetnikMail extends Mailable
{
    public function __construct(
        public string $poruka,
        public string $naslov,
        public ?string $ccAdresa = null,
        public ?Tvrtka $tvrtka = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->naslov,
            cc: $this->ccAdresa ? [new Address($this->ccAdresa)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pretplata',
            with: ['poruka' => $this->poruka, 'tvrtka' => $this->tvrtka],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
