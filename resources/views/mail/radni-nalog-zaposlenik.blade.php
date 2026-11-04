<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radni nalog {{ $nalog->broj }}</title>
</head>
<body style="margin:0;padding:0;background:#eef2f2;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f2;padding:32px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2ece9;">
                @include('mail.partials.header', ['tvrtka' => $tvrtka ?? null])
                <tr>
                    <td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#162433;line-height:1.7;">
                        {!! nl2br(e($poruka)) !!}

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background:#f7fafa;border:1px solid #e2ece9;border-radius:8px;">
                            <tr><td style="padding:16px 18px;font-size:14px;line-height:1.8;">
                                <strong style="font-size:15px;">{{ $nalog->broj }}</strong><br>
                                @if($nalog->klijent)Klijent: <strong>{{ $nalog->klijent->naziv }}</strong>@if($nalog->klijent->kontakt_broj), tel. {{ $nalog->klijent->kontakt_broj }}@endif<br>@endif
                                @if($nalog->predmet)Predmet: {{ $nalog->predmet }}<br>@endif
                                @if($nalog->lokacija)Lokacija: {{ $nalog->lokacija }}<br>@endif
                                @if($nalog->rok)Rok: <strong>{{ $nalog->rok->format('d.m.Y.') }}</strong>@endif
                            </td></tr>
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="border-radius:8px;background:#2ba99b;">
                                    <a href="{{ $urlNaloga }}"
                                       style="display:inline-block;padding:13px 28px;color:#ffffff;font-weight:700;font-size:15px;text-decoration:none;">
                                        ✓ Prihvati i otvori nalog
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:18px 0 0;font-size:13px;color:#4b5563;">
                            Na nalogu upisuješ sate, materijal i izvršene radove, a klijent se može potpisati na ekranu.
                            Svi tvoji nalozi: <a href="{{ $urlSvih }}" style="color:#1f8a7e;font-weight:700;">Moji nalozi</a>
                            — spremi si taj link, osobni je i ne treba lozinku.
                        </p>
                    </td>
                </tr>
                @include('mail.partials.footer', ['tvrtka' => $tvrtka ?? null])
            </table>
        </td>
    </tr>
</table>
</body>
</html>
