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

        $primatelj_ime    = mb_substr(static::ascii($tvrtka->naziv ?? ''), 0, 25);
        $primatelj_ulica  = mb_substr(static::ascii($tvrtka->adresa ?? ''), 0, 25);
        $primatelj_mjesto = mb_substr(static::ascii(trim(($tvrtka->po_broj ?? '') . ' ' . ($tvrtka->mjesto ?? ''))), 0, 27);
        $iban             = preg_replace('/\s+/', '', $tvrtka->iban ?? '');
        $poziv            = $racun->broj;
        $opis             = mb_substr(static::ascii('Uplata prema racunu ' . $racun->broj), 0, 35);

        $platitelj_ime    = mb_substr(static::ascii($klijent->naziv ?? ''), 0, 30);
        $platitelj_ulica  = mb_substr(static::ascii($klijent->adresa ?? ''), 0, 27);
        $platitelj_mjesto = mb_substr(static::ascii(trim(($klijent->po_broj ?? '') . ' ' . ($klijent->mjesto ?? ''))), 0, 27);

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
            $tvrtka->pnb ?? 'HR00',
            $poziv,
            '',
            $opis,
        ]) . "\n";
    }

    private static function ascii(string $s): string
    {
        return strtr($s, [
            'š' => 's', 'Š' => 'S',
            'č' => 'c', 'Č' => 'C',
            'ć' => 'c', 'Ć' => 'C',
            'ž' => 'z', 'Ž' => 'Z',
            'đ' => 'd', 'Đ' => 'D',
        ]);
    }

    public static function generirajBarkodBase64(Racun $racun): string
    {
        $hub3String = static::generirajString($racun);

        $pdf417 = new PDF417();
        $data   = $pdf417->encode($hub3String);
        $render = new ImageRenderer([
            'scale'   => 3,
            'ratio'   => 3,
            'padding' => 10,
        ]);

        $imageData = $render->render($data);

        return 'data:image/png;base64,' . base64_encode((string) $imageData);
    }
}
