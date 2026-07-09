<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponuda prihvaćena — plačko.app</title>
    <link rel="icon" href="/img/placko-icon.svg" type="image/svg+xml">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f7fafa;
            color: #162433;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2ece9;
            border-radius: 16px;
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 40px rgba(43,169,155,.16);
        }
        .check {
            width: 64px; height: 64px; margin: 0 auto 24px;
            background: #d1f5f0; color: #1f8a7e;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px;
        }
        h1 { font-size: 1.4rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -.3px; }
        p { color: #4b5563; font-size: .98rem; line-height: 1.6; }
        .broj { color: #1f8a7e; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="check">✓</div>
        <h1>Ponuda prihvaćena</h1>
        <p>
            Hvala! Obavijestili smo tvrtku <strong>{{ $ponuda->tvrtka->naziv ?? '' }}</strong>
            da ste prihvatili ponudu <span class="broj">{{ $ponuda->broj }}</span>.
            Uskoro će vas kontaktirati u vezi daljnjih koraka.
        </p>
    </div>
</body>
</html>
