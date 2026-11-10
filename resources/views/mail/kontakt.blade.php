<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $zaAdmina ? 'Novi upit s kontakt obrasca' : 'Primili smo tvoju poruku' }}</title>
</head>
<body style="margin:0;padding:0;background:#eef2f2;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f2;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2ece9;">
                {{-- Zaglavlje (isti stil kao mail dobrodošlice) --}}
                <tr>
                    <td style="background:#2ba99b;background-image:linear-gradient(135deg,#1f8a7e 0%,#2ba99b 60%,#52d4b0 100%);padding:32px 32px 28px;font-family:Arial,Helvetica,sans-serif;" align="left">
                        <img src="{{ url('/img/icon-192.png') }}" width="44" height="44" alt="plačko.app" style="display:block;border:0;border-radius:11px;margin:0 0 16px;">
                        <span style="display:inline-block;background:rgba(255,255,255,.18);color:#ffffff;font-size:12px;font-weight:700;letter-spacing:.5px;padding:4px 12px;border-radius:99px;margin:0 0 12px;">{{ $p['tema'] }}</span>
                        <p style="margin:0;color:#ffffff;font-size:24px;font-weight:800;line-height:1.3;">
                            {{ $zaAdmina ? 'Novi upit s kontakt obrasca' : 'Hvala, primili smo tvoju poruku!' }}
                        </p>
                        @unless ($zaAdmina)
                            <p style="margin:10px 0 0;color:#e6fbf6;font-size:15px;line-height:1.6;">Javit ćemo ti se u pravilu unutar jednog radnog dana.</p>
                        @endunless
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px 32px 10px;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#162433;line-height:1.7;">
                        @if ($zaAdmina)
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;font-size:14px;">
                                <tr><td style="padding:3px 18px 3px 0;color:#5b6b78;">Ime</td><td style="padding:3px 0;"><strong>{{ $p['ime'] }}</strong></td></tr>
                                <tr><td style="padding:3px 18px 3px 0;color:#5b6b78;">E-mail</td><td style="padding:3px 0;"><a href="mailto:{{ $p['email'] }}" style="color:#1f8a7e;">{{ $p['email'] }}</a></td></tr>
                                <tr><td style="padding:3px 18px 3px 0;color:#5b6b78;">Tema</td><td style="padding:3px 0;">{{ $p['tema'] }}</td></tr>
                                <tr><td style="padding:3px 18px 3px 0;color:#5b6b78;">Prijavljen</td><td style="padding:3px 0;">{{ $p['korisnik'] ? 'da, korisnik #'.$p['korisnik']['id'].' ('.$p['korisnik']['email'].')' : 'ne' }}</td></tr>
                                <tr><td style="padding:3px 18px 3px 0;color:#5b6b78;">Vrijeme</td><td style="padding:3px 0;">{{ $p['vrijeme'] }}</td></tr>
                            </table>
                        @else
                            <p style="margin:0 0 18px;">Bok {{ $p['ime'] }}, ovo je kopija poruke koju si nam poslao/la {{ $p['vrijeme'] }}:</p>
                        @endif

                        <div style="background:#f7fafa;border:1px solid #e2ece9;border-left:4px solid #2ba99b;border-radius:10px;padding:18px 20px;margin:0 0 24px;font-size:14px;line-height:1.7;white-space:pre-wrap;">{{ $p['poruka'] }}</div>

                        @if ($zaAdmina)
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 26px;">
                                <tr>
                                    <td style="border-radius:10px;background:#2ba99b;">
                                        <a href="mailto:{{ $p['email'] }}?subject={{ rawurlencode('Re: '.$p['tema'].' — plačko.app') }}" style="display:inline-block;padding:13px 28px;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;">Odgovori {{ $p['ime'] }} &rarr;</a>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <p style="margin:0 0 6px;">Ako želiš nešto dodati, samo odgovori na ovaj mail.</p>
                            <p style="margin:0 0 26px;">Pozdrav,<br><strong>Ekipa plačko.app</strong></p>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="background:#f7fafa;border-top:1px solid #e2ece9;padding:20px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b7a86;line-height:1.7;" align="left">
                        @if ($zaAdmina)
                            Poslano s obrasca na <a href="{{ url('/kontakt') }}" style="color:#1f8a7e;">plačko.app/kontakt</a>. Odgovor na ovaj mail ide izravno pošiljatelju.
                        @else
                            Ovaj mail je poslan jer je na <a href="{{ url('/kontakt') }}" style="color:#1f8a7e;">plačko.app/kontakt</a> zatražena kopija poruke na ovu adresu.
                            Ako to nisi ti, slobodno ga zanemari.
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
