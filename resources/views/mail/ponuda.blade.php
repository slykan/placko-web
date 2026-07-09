<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tvrtka?->naziv ?? 'Ponuda' }}</title>
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
                    </td>
                </tr>
                @include('mail.partials.footer', ['tvrtka' => $tvrtka ?? null])
            </table>
        </td>
    </tr>
</table>
</body>
</html>
