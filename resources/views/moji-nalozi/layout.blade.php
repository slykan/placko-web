<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title') — {{ $tvrtka->naziv }}</title>
    <link rel="icon" href="/img/placko-icon.svg" type="image/svg+xml">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f2f6f6; color: #162433; font-size: 16px; line-height: 1.5; }
        a { color: #1f8a7e; }
        .top { background: #2ba99b; color: #fff; padding: 14px 16px; }
        .top .wrap { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .top strong { font-size: 1.05rem; }
        .top small { opacity: .9; font-size: .85rem; }
        .top a { color: #fff; text-decoration: none; font-weight: 600; font-size: .95rem; }
        .wrap { max-width: 720px; margin: 0 auto; }
        main.wrap { padding: 16px; }
        h1 { font-size: 1.3rem; margin-bottom: 4px; }
        h2 { font-size: 1.05rem; margin: 0 0 10px; }
        .card { background: #fff; border: 1px solid #e2ece9; border-radius: 14px; padding: 16px; margin-bottom: 14px; }
        .muted { color: #5b6b78; font-size: .9rem; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
        .b-otvoren { background: #dbeafe; color: #1e40af; } .b-u_tijeku { background: #fef3c7; color: #92400e; }
        .b-zavrsen { background: #d1fae5; color: #065f46; } .b-naplacen { background: #d1f5f0; color: #1f8a7e; } .b-otkazan { background: #eee; color: #555; }
        .list a.item { display: block; text-decoration: none; color: inherit; background: #fff; border: 1px solid #e2ece9; border-radius: 12px; padding: 14px; margin-bottom: 10px; }
        .list a.item:active { background: #f7fafa; }
        .item .row1 { display: flex; justify-content: space-between; gap: 8px; align-items: center; }
        .late { color: #b91c1c; font-weight: 700; }
        dl { display: grid; grid-template-columns: auto 1fr; gap: 6px 14px; }
        dt { color: #5b6b78; font-size: .9rem; } dd { font-weight: 600; word-break: break-word; }
        .box { background: #f7fafa; border-radius: 10px; padding: 12px; white-space: pre-line; }
        label { display: block; font-size: .85rem; color: #5b6b78; margin-bottom: 3px; }
        input[type=text], input[type=number], input[type=date], textarea {
            width: 100%; font: inherit; padding: 10px 12px; border: 1px solid #cfdcd9; border-radius: 10px; background: #fff;
        }
        textarea { min-height: 96px; }
        input:focus, textarea:focus { outline: 2px solid #2ba99b; border-color: #2ba99b; }
        .redak { display: grid; gap: 8px; padding: 10px; border: 1px solid #e2ece9; border-radius: 10px; margin-bottom: 8px; position: relative; }
        .redak.rad { grid-template-columns: 1fr 90px; }
        .redak.mat { grid-template-columns: 1fr 90px 80px; }
        .redak .puna { grid-column: 1 / -1; }
        .makni { position: absolute; top: 4px; right: 6px; border: 0; background: none; color: #b91c1c; font-size: 1.3rem; cursor: pointer; line-height: 1; padding: 4px; }
        .redak { padding-right: 34px; }
        .btn { display: inline-block; border: 0; border-radius: 10px; padding: 12px 18px; font: inherit; font-weight: 700; cursor: pointer; text-decoration: none; text-align: center; }
        .btn-p { background: #2ba99b; color: #fff; } .btn-s { background: #e6f4f2; color: #1f8a7e; } .btn-full { width: 100%; padding: 15px; font-size: 1.05rem; }
        .flash { background: #d1fae5; color: #065f46; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; font-weight: 600; }
        .err { background: #fee2e2; color: #991b1b; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; }
        .prihvati { background: #fff7ed; border-color: #fdba74; }
        .prihvati.istakni { box-shadow: 0 0 0 3px #fdba74; }
        canvas { width: 100%; height: 180px; border: 1px dashed #9fb6b2; border-radius: 10px; background: #fff; touch-action: none; display: block; }
        .potpis-img { max-width: 100%; max-height: 160px; border: 1px solid #e2ece9; border-radius: 10px; background: #fff; }
        .check { display: flex; gap: 10px; align-items: flex-start; font-weight: 600; font-size: 1rem; color: #162433; }
        .check input { width: 22px; height: 22px; margin-top: 1px; accent-color: #2ba99b; }
        .ostali li { list-style: none; padding: 6px 0; border-bottom: 1px solid #eef2f2; font-size: .95rem; }
        footer { text-align: center; color: #8a9aa6; font-size: .8rem; padding: 20px 0 40px; }
        @media (max-width: 420px) { .redak.mat { grid-template-columns: 1fr 80px; } .redak.mat .jm { grid-column: 2; } }
    </style>
</head>
<body>
<header class="top"><div class="wrap">
    <div><strong>{{ $tvrtka->naziv }}</strong><br><small>{{ $zaposlenik->ime_prezime }}</small></div>
    @yield('nav')
</div></header>
<main class="wrap">
    @if(session('poruka'))<div class="flash">{{ session('poruka') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
    @yield('content')
</main>
<footer>Radni nalozi · plačko.app<br>Ovaj link je osoban — nemoj ga prosljeđivati.</footer>
@stack('scripts')
</body>
</html>
