<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NovaRegistracijaMail extends Mailable
{
    public function __construct(
        public User $korisnik,
        public ?string $urlKorisnika = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova registracija — '.$this->korisnik->email,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.nova-registracija',
            with: [
                'korisnik' => $this->korisnik,
                'nacin' => $this->korisnik->google_id ? 'Google prijava' : 'obrazac za registraciju',
                'ukupno' => User::count(),
                'danas' => User::whereDate('created_at', today())->count(),
                'urlKorisnika' => $this->urlKorisnika,
                'tvrtka' => null,
            ],
        );
    }
}
