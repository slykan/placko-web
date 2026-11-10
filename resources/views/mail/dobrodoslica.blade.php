<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dobrodošlica u plačko.app</title>
</head>
<body style="margin:0;padding:0;background:#eef2f2;">
{{-- Pretpregled u popisu poruka --}}
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Tri koraka do prvog računa: podaci obrta, klijent, račun. Sve ostalo možeš kasnije.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f2;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2ece9;">
                {{-- Zaglavlje --}}
                <tr>
                    <td style="background:#2ba99b;background-image:linear-gradient(135deg,#1f8a7e 0%,#2ba99b 60%,#52d4b0 100%);padding:36px 32px 32px;font-family:Arial,Helvetica,sans-serif;" align="left">
                        <img src="{{ url('/img/icon-192.png') }}" width="48" height="48" alt="plačko.app" style="display:block;border:0;border-radius:12px;margin:0 0 18px;">
                        <p style="margin:0;color:#ffffff;font-size:26px;font-weight:800;line-height:1.25;">
                            {{ $ime ? 'Hej '.$ime.', dobrodošlica!' : 'Dobrodošlica!' }} 👋
                        </p>
                        <p style="margin:10px 0 0;color:#e6fbf6;font-size:15px;line-height:1.6;">
                            Drago nam je što si tu. Fakturiranje od danas ide bez muke i suza.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px 32px 8px;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#162433;line-height:1.7;">
                        <p style="margin:0 0 22px;">Tvoj korisnički račun je spreman. Do prvog izdanog računa dijele te tri kratka koraka:</p>

                        @foreach ([
                            ['Podaci obrta ili tvrtke', 'Naziv, OIB, adresa i IBAN (po želji i logo) ispisuju se na svakom računu.'],
                            ['Prvi klijent i usluga', 'Spremi kupca i ono što najčešće naplaćuješ, pa ih idući put samo odabereš.'],
                            ['Izdaj račun', 'PDF s HUB-3 barkodom za plaćanje i slanje mailom jednim klikom. Gotovo.'],
                        ] as $i => [$naslov, $opis])
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;">
                                <tr>
                                    <td style="width:44px;vertical-align:top;">
                                        <div style="width:32px;height:32px;border-radius:50%;background:#e6f6f3;color:#1f8a7e;font-weight:800;font-size:15px;line-height:32px;text-align:center;">{{ $i + 1 }}</div>
                                    </td>
                                    <td style="vertical-align:top;">
                                        <p style="margin:4px 0 2px;font-weight:700;">{{ $naslov }}</p>
                                        <p style="margin:0;color:#5b6b78;font-size:14px;line-height:1.6;">{{ $opis }}</p>
                                    </td>
                                </tr>
                            </table>
                        @endforeach

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0 30px;">
                            <tr>
                                <td style="border-radius:10px;background:#2ba99b;">
                                    <a href="{{ $urlAplikacije }}" style="display:inline-block;padding:14px 30px;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;">Otvori plačko.app &rarr;</a>
                                </td>
                            </tr>
                        </table>

                        {{-- Korisno znati --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7fafa;border:1px solid #e2ece9;border-radius:12px;">
                            <tr>
                                <td style="padding:20px 22px;font-size:14px;color:#162433;line-height:1.7;">
                                    <p style="margin:0 0 8px;font-weight:700;">Korisno znati</p>
                                    <p style="margin:0 0 6px;">🧾 <strong>Fiskalizacija:</strong> za gotovinu i kartice učitaj FINA certifikat u <em>Postavke → Fiskalizacija</em>.</p>
                                    <p style="margin:0 0 6px;">🔔 <strong>Novosti:</strong> zvono gore desno javlja što je novo, a u Postavkama možeš uključiti i obavijesti na mobitelu.</p>
                                    <p style="margin:0;">📚 <strong>Vodiči</strong> za paušaliste: KPR, PO-SD, PDV prag, eRačun od 2027. &mdash; <a href="{{ $urlVodica }}" style="color:#1f8a7e;font-weight:700;">pročitaj ovdje</a>.</p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:26px 0 6px;">Zapelo je negdje ili imaš ideju što bi ti olakšalo posao? Samo odgovori na ovaj mail, čitamo svaki.</p>
                        <p style="margin:0 0 28px;">Sretno s poslom!<br><strong>Ekipa plačko.app</strong></p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#f7fafa;border-top:1px solid #e2ece9;padding:20px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b7a86;line-height:1.7;" align="left">
                        Ovaj mail je poslan jer je s ove adrese napravljen račun na <a href="{{ url('/') }}" style="color:#1f8a7e;">plačko.app</a>.
                        Ako ga nisi ti otvarao/la, javi nam se na <a href="mailto:{{ config('mail.kontakt') }}" style="color:#1f8a7e;">{{ config('mail.kontakt') }}</a>.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
