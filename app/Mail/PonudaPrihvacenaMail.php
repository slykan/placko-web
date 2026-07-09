<?php

namespace App\Mail;

use App\Models\Ponuda;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PonudaPrihvacenaMail extends Mailable
{
    public function __construct(
        public Ponuda $ponuda,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ponuda '.$this->ponuda->broj.' je prihvaćena',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.ponuda-prihvacena',
            with: [
                'ponuda' => $this->ponuda,
                'tvrtka' => $this->ponuda->tvrtka,
                'urlPonude' => url('/admin/'.$this->ponuda->tvrtka_id.'/ponude/'.$this->ponuda->id.'/edit'),
            ],
        );
    }
}
