<?php

namespace App\Services;

use App\Models\Racun;
use Le\PDF417\PDF417;
use Le\PDF417\Renderer\ImageRenderer;

class Hub3Service
{
    public static function generirajString(Racun $racun): string
    {
        $tvrtka  = $racun->tvrtka;
        $klijent = $racun->klijent;

        $iznos = str_pad(
            str_replace(['.', ','], '', number_format((float) $racun->ukupno, 2, '.', '')),
            15,
            '0',
            STR_PAD_LEFT
        );

        $primatelj_ime    = mb_substr($tvrtka->naziv ?? '', 0, 25);
        $primatelj_ulica  = mb_substr($tvrtka->adresa ?? '', 0, 25);
        $primatelj_mjesto = mb_substr(($tvrtka->po_broj ?? '') . ' ' . ($tvrtka->mjesto ?? ''), 0, 27);
        $iban             = preg_replace('/\s+/', '', $tvrtka->iban ?? '');
        $poziv            = $racun->broj;
        $opis             = mb_substr('Uplata prema racunu ' . $racun->broj, 0, 35);

        $platitelj_ime    = mb_substr($klijent->naziv ?? '', 0, 25);
        $platitelj_ulica  = mb_substr($klijent->adresa ?? '', 0, 25);
        $platitelj_mjesto = mb_substr(($klijent->po_broj ?? '') . ' ' . ($klijent->mjesto ?? ''), 0, 27);

        return implode("\n", [
            'HRVHUB30',
            'EUR',
            $iznos,
            $platitelj_ime,
            $platitelj_ulica,
            $platitelj_mjesto,
            $primatelj_ime,
            $primatelj_ulica,
            $primatelj_mjesto,
            $iban,
            'HR99',
            $poziv,
            '',
            $opis,
        ]);
    }

    public static function generirajBarkodBase64(Racun $racun): string
    {
        $hub3String = static::generirajString($racun);

        $pdf417 = new PDF417();
        $data   = $pdf417->encode($hub3String);
        $render = new ImageRenderer([
            'scale'   => 2,
            'ratio'   => 3,
            'padding' => 5,
        ]);

        $imageData = $render->render($data);

        return 'data:image/png;base64,' . base64_encode((string) $imageData);
    }
}
