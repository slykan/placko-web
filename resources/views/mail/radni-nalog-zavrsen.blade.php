<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radni nalog završen</title>
</head>
<body style="margin:0;padding:0;background:#eef2f2;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f2;padding:32px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2ece9;">
                @include('mail.partials.header', ['tvrtka' => $tvrtka ?? null])
                <tr>
                    <td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#162433;line-height:1.7;">
                        <p style="margin:0 0 16px;">
                            <strong>{{ $zaposlenik->ime_prezime }}</strong> je označio radni nalog
                            <strong>{{ $nalog->broj }}</strong>@if($nalog->klijent) za {{ $nalog->klijent->naziv }}@endif kao završen.
                        </p>
                        <p style="margin:0 0 16px;">
                            Upisano: <strong>{{ number_format((float) $nalog->ukupno_sati, 2, ',', '.') }} h</strong> rada,
                            {{ $nalog->stavke()->count() }} stavki materijala@if($nalog->potpisano_at), klijent je potpisao@if($nalog->potpisao) ({{ $nalog->potpisao }})@endif @endif.
                        </p>
                        @if($nalog->izvrseni_radovi)
                            <p style="margin:0 0 24px;padding:12px 16px;background:#f7fafa;border-left:3px solid #2ba99b;">{!! nl2br(e($nalog->izvrseni_radovi)) !!}</p>
                        @endif
                        <p style="margin:0 0 24px;">Provjeri nalog i izradi račun.</p>
                        <a href="{{ $urlNaloga }}"
                           style="display:inline-block;background:#2ba99b;color:#ffffff;text-decoration:none;font-weight:700;padding:12px 24px;border-radius:8px;">
                            Otvori nalog
                        </a>
                    </td>
                </tr>
                @include('mail.partials.footer', ['tvrtka' => $tvrtka ?? null])
            </table>
        </td>
    </tr>
</table>
</body>
</html>
