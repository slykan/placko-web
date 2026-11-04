<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova registracija</title>
</head>
<body style="margin:0;padding:0;background:#eef2f2;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f2;padding:32px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2ece9;">
                @include('mail.partials.header', ['tvrtka' => null])
                <tr>
                    <td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#162433;line-height:1.7;">
                        <p style="margin:0 0 16px;font-size:16px;"><strong>Novi korisnik u plačko.app</strong></p>
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;font-size:14px;">
                            <tr><td style="padding:3px 16px 3px 0;color:#5b6b78;">Ime</td><td style="padding:3px 0;"><strong>{{ $korisnik->name }}</strong></td></tr>
                            <tr><td style="padding:3px 16px 3px 0;color:#5b6b78;">E-mail</td><td style="padding:3px 0;"><a href="mailto:{{ $korisnik->email }}" style="color:#1f8a7e;">{{ $korisnik->email }}</a></td></tr>
                            <tr><td style="padding:3px 16px 3px 0;color:#5b6b78;">Način</td><td style="padding:3px 0;">{{ $nacin }}</td></tr>
                            <tr><td style="padding:3px 16px 3px 0;color:#5b6b78;">Vrijeme</td><td style="padding:3px 0;">{{ $korisnik->created_at?->timezone(config('app.timezone'))->format('d.m.Y. H:i') }}</td></tr>
                        </table>
                        <p style="margin:0 0 24px;color:#5b6b78;">Danas registriranih: <strong>{{ $danas }}</strong> · ukupno korisnika: <strong>{{ $ukupno }}</strong></p>
                        @if($urlKorisnika)
                            <a href="{{ $urlKorisnika }}"
                               style="display:inline-block;background:#2ba99b;color:#ffffff;text-decoration:none;font-weight:700;padding:12px 24px;border-radius:8px;">
                                Otvori korisnika
                            </a>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
