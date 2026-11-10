<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Mail dobrodošlice novom korisniku, odmah nakon registracije (obrazac ili Google). */
class DobrodosliceMail extends Mailable
{
    public function __construct(public User $korisnik) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dobrodošlica u plačko.app 👋',
            replyTo: [new Address(config('mail.kontakt'), 'plačko.app podrška')],
        );
    }

    public function content(): Content
    {
        $ime = trim((string) strtok((string) $this->korisnik->name, ' '));

        return new Content(
            view: 'mail.dobrodoslica',
            with: [
                'ime' => $ime,
                'urlAplikacije' => url('/admin'),
                'urlVodica' => url('/novosti?kategorija=vodic'),
            ],
        );
    }
}
