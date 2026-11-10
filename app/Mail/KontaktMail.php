<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Poruka s kontakt obrasca: adminu (odgovor ide pošiljatelju) ili kopija pošiljatelju. */
class KontaktMail extends Mailable
{
    public function __construct(public array $poruka, public bool $zaAdmina) {}

    public function envelope(): Envelope
    {
        return $this->zaAdmina
            ? new Envelope(
                subject: '['.$this->poruka['tema'].'] Upit s plačko.app — '.$this->poruka['ime'],
                replyTo: [new Address($this->poruka['email'], $this->poruka['ime'])],
            )
            : new Envelope(
                subject: 'Primili smo tvoju poruku — plačko.app',
                replyTo: [new Address(config('mail.kontakt'), 'plačko.app podrška')],
            );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.kontakt', with: ['p' => $this->poruka, 'zaAdmina' => $this->zaAdmina]);
    }
}
