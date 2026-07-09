<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Novosti' }} — plačko.app</title>
    <meta name="description" content="{{ $description ?? 'Novosti, savjeti i upute za korištenje plačko.app aplikacije za fakturiranje i računovodstvo.' }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $title ?? 'Novosti' }} — plačko.app">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:image" content="/img/placko-icon.svg">
    <meta property="og:locale" content="hr_HR">

    <link rel="icon" href="/img/placko-icon.svg" type="image/svg+xml">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --p:      #2ba99b;
            --pd:     #1f8a7e;
            --pl:     #d1f5f0;
            --acc:    #7ef0a0;
            --text:   #162433;
            --muted:  #4b5563;
            --light:  #8da4a0;
            --bg:     #f7fafa;
            --white:  #ffffff;
            --border: #e2ece9;
            --radius: 16px;
            --shadow: 0 2px 16px rgba(43,169,155,.10);
            --shadow-lg: 0 8px 40px rgba(43,169,155,.16);
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg); color: var(--text);
            line-height: 1.65; -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; }

        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(247,250,250,.88);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 6%;
            display: flex; align-items: center; justify-content: space-between;
            height: 66px;
        }
        .nav-logo img { height: 32px; }
        .nav-links { display: flex; align-items: center; gap: 10px; }
        .nav-link {
            padding: 7px 16px; color: var(--muted);
            font-size: 14px; font-weight: 500;
            border-radius: 8px; transition: color .2s;
        }
        .nav-link.active, .nav-link:hover { color: var(--p); }
        .btn-nav-outline {
            padding: 7px 18px; border: 1.5px solid var(--border);
            color: var(--text); border-radius: 9px; font-weight: 600;
            font-size: 14px; transition: all .2s;
        }
        .btn-nav-outline:hover { border-color: var(--p); color: var(--p); }
        .btn-nav-solid {
            padding: 8px 20px; background: var(--p); color: white;
            border-radius: 9px; font-weight: 600; font-size: 14px;
            box-shadow: 0 2px 10px rgba(43,169,155,.35);
            transition: all .2s;
        }
        .btn-nav-solid:hover { background: var(--pd); transform: translateY(-1px); }

        main { padding: 130px 6% 80px; max-width: 880px; margin: 0 auto; }

        .page-eyebrow {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--pl); color: var(--pd);
            font-size: 12.5px; font-weight: 700; letter-spacing: .5px;
            padding: 5px 16px; border-radius: 99px; margin-bottom: 20px;
        }
        .page-title {
            font-size: clamp(1.9rem, 4.5vw, 2.9rem);
            font-weight: 900; line-height: 1.15; letter-spacing: -1px;
            margin-bottom: 16px;
        }
        .page-sub { color: var(--muted); font-size: 1.08rem; max-width: 640px; }

        /* ── BLOG LIST ──────────────────────────────────── */
        .post-grid { display: grid; gap: 24px; margin-top: 48px; }
        .post-card {
            display: block; background: var(--white);
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 32px; transition: transform .2s, box-shadow .2s, border-color .2s;
        }
        .post-card:hover {
            transform: translateY(-4px); box-shadow: var(--shadow-lg);
            border-color: rgba(43,169,155,.3);
        }
        .post-meta { display: flex; gap: 14px; color: var(--light); font-size: .85rem; margin-bottom: 12px; }
        .post-card h2 { font-size: 1.4rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -.3px; }
        .post-card p { color: var(--muted); font-size: .98rem; line-height: 1.7; }
        .post-read-more { display: inline-block; margin-top: 16px; color: var(--p); font-weight: 700; font-size: .92rem; }

        /* ── ARTICLE ────────────────────────────────────── */
        .article-back { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); font-size: .9rem; font-weight: 600; margin-bottom: 28px; }
        .article-back:hover { color: var(--p); }
        article.post-body { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 48px; }
        article.post-body h2 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.3px; margin: 40px 0 14px; }
        article.post-body h2:first-child { margin-top: 0; }
        article.post-body h3 { font-size: 1.15rem; font-weight: 750; margin: 28px 0 10px; color: var(--pd); }
        article.post-body p { margin-bottom: 16px; color: var(--text); font-size: 1.02rem; }
        article.post-body ul { margin: 0 0 16px 22px; }
        article.post-body li { margin-bottom: 8px; font-size: 1.02rem; }
        article.post-body strong { color: var(--pd); }
        article.post-body code { background: var(--pl); color: var(--pd); padding: 2px 6px; border-radius: 5px; font-size: .9em; }
        article.post-body .callout {
            background: var(--pl); border-radius: 12px; padding: 20px 24px;
            margin: 24px 0; font-size: .96rem; color: var(--pd);
        }
        .article-cta {
            margin-top: 40px; text-align: center; background: var(--white);
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 40px 24px;
        }
        .article-cta h3 { font-size: 1.3rem; font-weight: 800; margin-bottom: 10px; }
        .article-cta p { color: var(--muted); margin-bottom: 22px; }
        .btn-primary {
            padding: 14px 34px; background: var(--p); color: white;
            border-radius: 12px; font-size: 16px; font-weight: 700;
            box-shadow: 0 4px 22px rgba(43,169,155,.42);
            transition: all .25s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background: var(--pd); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(43,169,155,.48); }

        footer { background: #10201e; color: #b8ccc8; padding: 56px 6% 28px; margin-top: 40px; }
        .footer-inner { max-width: 1120px; margin: 0 auto; }
        .footer-top { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 32px; margin-bottom: 40px; }
        .footer-brand img { height: 30px; margin-bottom: 14px; filter: brightness(0) invert(1); }
        .footer-brand p { font-size: .9rem; color: #8fa8a3; max-width: 280px; }
        .footer-col h4 { color: white; font-size: .92rem; margin-bottom: 14px; }
        .footer-col ul { list-style: none; }
        .footer-col li { margin-bottom: 10px; }
        .footer-col a { font-size: .88rem; color: #8fa8a3; transition: color .2s; }
        .footer-col a:hover { color: var(--acc); }
        .footer-bottom {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 24px; border-top: 1px solid rgba(255,255,255,.08);
            font-size: .85rem; color: #7d928e; flex-wrap: wrap; gap: 12px;
        }
        .footer-hr-badge { display: inline-flex; align-items: center; gap: 6px; font-size: .85rem; }

        @media (max-width: 720px) {
            .nav-link { display: none; }
            main { padding: 110px 5% 60px; }
            article.post-body { padding: 28px 22px; }
            .footer-top { grid-template-columns: 1fr; gap: 28px; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<nav>
    <a href="/" class="nav-logo">
        <img src="/img/placko-logo.svg" alt="plačko.app">
    </a>
    <div class="nav-links">
        <a href="/#znacajke" class="nav-link">Značajke</a>
        <a href="/#cijene" class="nav-link">Cijene</a>
        <a href="/novosti" class="nav-link active">Novosti</a>
        <a href="/#faq" class="nav-link">FAQ</a>
        <a href="/admin" class="btn-nav-outline">Prijava</a>
        <a href="/admin/register" class="btn-nav-solid">Isprobaj</a>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-brand">
                <img src="/img/placko-logo.svg" alt="plačko.app">
                <p>Moderna aplikacija za fakturiranje, fiskalizaciju i računovodstvo za paušalne obrtnike i male tvrtke u Hrvatskoj.</p>
            </div>
            <div class="footer-col">
                <h4>Proizvod</h4>
                <ul>
                    <li><a href="/#znacajke">Značajke</a></li>
                    <li><a href="/#cijene">Cijene</a></li>
                    <li><a href="/novosti">Novosti</a></li>
                    <li><a href="/admin/register">Registracija</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Aplikacija</h4>
                <ul>
                    <li><a href="/admin">Prijava</a></li>
                    <li><a href="/admin/register">Novi račun</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Pravno</h4>
                <ul>
                    <li><a href="/#">Uvjeti korištenja</a></li>
                    <li><a href="/#">Politika privatnosti</a></li>
                    <li><a href="/#">GDPR</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} <strong style="color:#7dd8d1;">plačko.app</strong> — Sva prava pridržana</p>
            <div class="footer-hr-badge">🇭🇷 Napravljeno u Hrvatskoj</div>
        </div>
    </div>
</footer>

</body>
</html>
