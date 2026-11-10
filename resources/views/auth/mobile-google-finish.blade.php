<!DOCTYPE html>
<html lang="hr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="referrer" content="no-referrer"><title>Povratak u Plačko</title></head>
<body style="margin:0;background:#f7fafa;font:17px system-ui;color:#2ba99b">
    <main style="max-width:420px;margin:64px auto;padding:24px">
        <h1>Plačko</h1>
        @if($code)
            <p>Google prijava je uspjela. Vratite se u aplikaciju kako biste je dovršili.</p>
            <a href="{{ 'placko://auth/google?'.http_build_query(['code' => $code, 'state' => $state]) }}" style="display:inline-block;background:#2ba99b;color:white;padding:16px 24px;border-radius:12px;text-decoration:none">Vrati se u Plačko</a>
            <p style="font-size:14px">Poveznica vrijedi jednu minutu. Ako je istekla, ponovno pokrenite prijavu iz aplikacije.</p>
        @else
            <p>Google prijava nije dovršena. Vratite se u Plačko i pokušajte ponovno.</p>
            <a href="placko://auth/google?error=cancelled">Vrati se u Plačko</a>
        @endif
    </main>
</body></html>
