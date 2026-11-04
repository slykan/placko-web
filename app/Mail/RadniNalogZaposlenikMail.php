<?php

namespace App\Mail;

use App\Models\RadniNalog;
use App\Models\Zaposlenik;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RadniNalogZaposlenikMail extends Mailable
{
    public function __construct(
        public RadniNalog $nalog,
        public Zaposlenik $zaposlenik,
        public string $poruka,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Radni nalog '.$this->nalog->broj.($this->nalog->klijent ? ' — '.$this->nalog->klijent->naziv : ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.radni-nalog-zaposlenik',
            with: [
                'nalog' => $this->nalog,
                'tvrtka' => $this->nalog->tvrtka,
                'poruka' => $this->poruka,
                'urlNaloga' => route('moji-nalozi.show', [$this->zaposlenik->portal_token, $this->nalog->id]).'?prihvati=1',
                'urlSvih' => $this->zaposlenik->portalUrl(),
            ],
        );
    }
}
