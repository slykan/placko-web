<?php

namespace App\Mail;

use App\Models\RadniNalog;
use App\Models\Zaposlenik;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RadniNalogZavrsenMail extends Mailable
{
    public function __construct(
        public RadniNalog $nalog,
        public Zaposlenik $zaposlenik,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Radni nalog '.$this->nalog->broj.' je završen',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.radni-nalog-zavrsen',
            with: [
                'nalog' => $this->nalog,
                'zaposlenik' => $this->zaposlenik,
                'tvrtka' => $this->nalog->tvrtka,
                'urlNaloga' => url('/admin/'.$this->nalog->tvrtka_id.'/radni-nalozi/'.$this->nalog->id.'/edit'),
            ],
        );
    }
}
