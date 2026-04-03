<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>plačko.app — Fakturiranje i računovodstvo za obrtnike u Hrvatskoj</title>
    <meta name="description" content="plačko.app je moderna web aplikacija za izdavanje računa, fiskalizaciju, eRačun (UBL 2.1), praćenje pretplata i porezne obrasce za paušalne obrtnike i male tvrtke u Hrvatskoj.">
    <meta name="keywords" content="fakturiranje, računi, obrtnik, paušalni obrtnik, fiskalizacija, eRačun, UBL 2.1, PO-SD, IRA, Hrvatska, računovodstvo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://plačko.app/">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://plačko.app/">
    <meta property="og:title" content="plačko.app — Fakturiranje za obrtnike u Hrvatskoj">
    <meta property="og:description" content="Računi, fiskalizacija, eRačun, pretplate i porezni obrasci. Sve na jednom mjestu, jednostavno i brzo.">
    <meta property="og:image" content="/img/placko-icon.svg">
    <meta property="og:locale" content="hr_HR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="plačko.app — Fakturiranje za obrtnike">
    <meta name="twitter:description" content="Računi, fiskalizacija, eRačun i porezni obrasci za hrvatske obrtnike.">

    <!-- Favicon -->
    <link rel="icon" href="/img/placko-icon.svg" type="image/svg+xml">

    <!-- Schema.org -->
    <script type="application/ld+json">
    @verbatim
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "plačko.app",
        "description": "Aplikacija za fakturiranje, fiskalizaciju i računovodstvo za paušalne obrtnike i male tvrtke u Hrvatskoj.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "EUR"
        },
        "url": "https://plačko.app",
        "inLanguage": "hr"
    }
    @endverbatim
    </script>

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
            background: var(--bg);
            color: var(--text);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }
        img { display: block; }

        /* ── NAVIGATION ─────────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(247,250,250,.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
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
        .nav-link:hover { color: var(--p); }
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

        /* ── HERO ───────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center;
            padding: 120px 6% 80px;
            background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(43,169,155,.13) 0%, transparent 70%),
                        radial-gradient(ellipse 60% 50% at 90% 80%, rgba(150,240,112,.10) 0%, transparent 70%),
                        var(--bg);
            position: relative; overflow: hidden;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--pl); color: var(--pd);
            font-size: 12.5px; font-weight: 700; letter-spacing: .5px;
            padding: 5px 16px; border-radius: 99px; margin-bottom: 28px;
        }
        .hero-eyebrow-dot {
            width: 7px; height: 7px; background: var(--p);
            border-radius: 50%; animation: pulse 2.2s ease-in-out infinite;
        }
        @@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

        .hero-icon {
            width: 96px; height: 96px; margin: 0 auto 32px;
            filter: drop-shadow(0 10px 28px rgba(43,169,155,.32));
            animation: float 5s ease-in-out infinite;
        }
        @@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }

        h1 {
            font-size: clamp(2.4rem, 5.5vw, 3.8rem);
            font-weight: 900; line-height: 1.1;
            letter-spacing: -1.5px; margin-bottom: 22px;
            color: var(--text);
        }
        h1 em {
            font-style: normal;
            background: linear-gradient(135deg, var(--p) 0%, #52d4b0 50%, var(--acc) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-sub {
            font-size: clamp(1rem, 2vw, 1.18rem);
            color: var(--muted); max-width: 580px; margin: 0 auto 40px;
            line-height: 1.75;
        }
        .hero-cta {
            display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;
            margin-bottom: 56px;
        }
        .btn-primary {
            padding: 14px 34px; background: var(--p); color: white;
            border-radius: 12px; font-size: 16px; font-weight: 700;
            box-shadow: 0 4px 22px rgba(43,169,155,.42);
            transition: all .25s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background: var(--pd); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(43,169,155,.48); }
        .btn-ghost {
            padding: 14px 34px; background: var(--white); color: var(--text);
            border: 1.5px solid var(--border); border-radius: 12px;
            font-size: 16px; font-weight: 600; transition: all .25s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-ghost:hover { border-color: var(--p); color: var(--p); transform: translateY(-2px); }

        .hero-trust {
            display: flex; align-items: center; gap: 24px;
            justify-content: center; flex-wrap: wrap;
            font-size: 13px; color: var(--light);
        }
        .hero-trust-item { display: flex; align-items: center; gap: 6px; }
        .hero-trust-item span { color: var(--p); font-size: 15px; }

        /* ── STATS ──────────────────────────────────────── */
        .stats-bar {
            background: var(--white);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 28px 6%;
        }
        .stats-inner {
            max-width: 960px; margin: 0 auto;
            display: flex; justify-content: space-around;
            gap: 24px; flex-wrap: wrap;
        }
        .stat { text-align: center; }
        .stat-num {
            font-size: 2rem; font-weight: 900; color: var(--p); line-height: 1;
            margin-bottom: 4px;
        }
        .stat-label { font-size: .83rem; color: var(--muted); font-weight: 500; }

        /* ── SECTIONS ───────────────────────────────────── */
        section { padding: 88px 6%; }
        .section-inner { max-width: 1120px; margin: 0 auto; }
        .section-tag {
            display: inline-block; background: var(--pl); color: var(--pd);
            font-size: 12px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; padding: 4px 14px; border-radius: 99px;
            margin-bottom: 16px;
        }
        .section-title {
            font-size: clamp(1.7rem, 3.5vw, 2.5rem);
            font-weight: 850; letter-spacing: -.5px; margin-bottom: 14px;
        }
        .section-sub {
            color: var(--muted); font-size: 1.05rem;
            max-width: 560px; line-height: 1.7;
        }
        .section-header { margin-bottom: 56px; }
        .section-header.center { text-align: center; }
        .section-header.center .section-sub { margin: 0 auto; }

        /* ── FEATURES GRID ──────────────────────────────── */
        .features-bg { background: var(--white); }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        .feature-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px 28px;
            transition: transform .2s, box-shadow .2s, border-color .2s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(43,169,155,.3);
        }
        .feature-icon-wrap {
            width: 52px; height: 52px; border-radius: 14px;
            background: var(--pl); display: flex; align-items: center;
            justify-content: center; font-size: 24px; margin-bottom: 20px;
        }
        .feature-card h3 {
            font-size: 1.05rem; font-weight: 750; margin-bottom: 10px;
        }
        .feature-card p { color: var(--muted); font-size: .92rem; line-height: 1.7; }
        .feature-card .feature-tag {
            display: inline-block; margin-top: 16px;
            font-size: 11px; font-weight: 700; letter-spacing: .5px;
            text-transform: uppercase; color: var(--p); background: var(--pl);
            padding: 3px 10px; border-radius: 99px;
        }

        /* ── HOW IT WORKS ───────────────────────────────── */
        .how-bg { background: var(--bg); }
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 40px; position: relative;
        }
        .step { text-align: center; position: relative; }
        .step-num {
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, var(--p), #52d4b0);
            color: white; font-size: 1.5rem; font-weight: 900;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 16px rgba(43,169,155,.35);
        }
        .step h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 10px; }
        .step p { color: var(--muted); font-size: .92rem; line-height: 1.7; }

        /* ── ERACUN ─────────────────────────────────────── */
        .eracun-section { background: var(--white); }
        .eracun-card {
            background: linear-gradient(135deg, var(--text) 0%, #1a4a44 100%);
            border-radius: 24px;
            padding: 64px 56px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 48px; align-items: center;
            position: relative; overflow: hidden;
        }
        .eracun-card::before {
            content: '';
            position: absolute; top: -80px; right: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(43,169,155,.3) 0%, transparent 70%);
            pointer-events: none;
        }
        .eracun-card::after {
            content: '';
            position: absolute; bottom: -60px; left: 30%;
            width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(150,240,112,.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .eracun-badge-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.12); color: #a7f3d0;
            font-size: 11.5px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; padding: 5px 14px; border-radius: 99px;
            margin-bottom: 20px; position: relative; z-index: 1;
        }
        .eracun-card h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 850; color: white; margin-bottom: 16px;
            position: relative; z-index: 1;
        }
        .eracun-card p {
            color: #a7f3d0; font-size: .98rem; line-height: 1.75;
            max-width: 500px; margin-bottom: 28px;
            position: relative; z-index: 1;
        }
        .eracun-features-list {
            display: flex; flex-wrap: wrap; gap: 10px;
            position: relative; z-index: 1;
        }
        .eracun-chip {
            background: rgba(255,255,255,.1); color: white;
            border: 1px solid rgba(255,255,255,.2);
            font-size: 13px; font-weight: 600; padding: 6px 14px;
            border-radius: 8px;
        }
        .eracun-date-box {
            background: rgba(255,255,255,.08);
            border: 1.5px solid rgba(255,255,255,.15);
            border-radius: 20px; padding: 32px 40px;
            text-align: center; flex-shrink: 0;
            position: relative; z-index: 1;
        }
        .eracun-date-box .year {
            font-size: 3.5rem; font-weight: 900; color: white; line-height: 1;
        }
        .eracun-date-box .quarter {
            font-size: 1rem; font-weight: 700;
            background: linear-gradient(90deg, var(--p), var(--acc));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-top: 6px;
        }
        .eracun-date-box .label {
            font-size: .82rem; color: #7dd8d1; margin-top: 4px;
        }
        .progress-wrap { margin-top: 28px; position: relative; z-index: 1; }
        .progress-meta {
            display: flex; justify-content: space-between;
            font-size: .8rem; color: #7dd8d1; margin-bottom: 8px;
        }
        .progress-track { background: rgba(255,255,255,.12); border-radius: 99px; height: 5px; }
        .progress-fill {
            background: linear-gradient(90deg, var(--p), var(--acc));
            border-radius: 99px; height: 5px; width: 55%;
            position: relative;
        }
        .progress-fill::after {
            content: '';
            position: absolute; right: -1px; top: 50%;
            transform: translateY(-50%);
            width: 11px; height: 11px; border-radius: 50%;
            background: var(--acc);
            box-shadow: 0 0 8px rgba(150,240,112,.6);
        }

        /* ── PRICING ────────────────────────────────────── */
        .pricing-bg { background: var(--bg); }
        .plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px; align-items: start;
        }
        .plan {
            background: var(--white); border: 1.5px solid var(--border);
            border-radius: 20px; padding: 32px 28px;
            transition: transform .2s, box-shadow .2s;
        }
        .plan:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .plan.featured {
            border-color: var(--p);
            background: var(--white);
            box-shadow: 0 4px 32px rgba(43,169,155,.16);
            position: relative;
        }
        .plan-badge {
            position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, var(--p), #52d4b0);
            color: white; font-size: 11.5px; font-weight: 700;
            letter-spacing: .5px; padding: 4px 16px; border-radius: 99px;
            white-space: nowrap;
        }
        .plan-name {
            font-size: .8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: var(--light); margin-bottom: 10px;
        }
        .plan.featured .plan-name { color: var(--p); }
        .plan-price {
            font-size: 2.4rem; font-weight: 900; line-height: 1;
            margin-bottom: 4px; color: var(--text);
        }
        .plan-price sub {
            font-size: 1rem; font-weight: 400; color: var(--muted);
            vertical-align: baseline;
        }
        .plan-desc { color: var(--muted); font-size: .88rem; margin: 10px 0 22px; }
        .plan-divider { height: 1px; background: var(--border); margin-bottom: 22px; }
        .plan-features { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .plan-features li {
            font-size: .9rem; display: flex; align-items: flex-start; gap: 10px;
            line-height: 1.45;
        }
        .plan-features li .check { color: var(--p); font-weight: 700; flex-shrink: 0; margin-top: 1px; }
        .plan-features li .cross { color: #d1d5db; flex-shrink: 0; margin-top: 1px; }
        .plan-features li.no { color: #9ca3af; }
        .plan-cta {
            display: block; text-align: center; margin-top: 28px;
            padding: 12px; border-radius: 10px; font-weight: 700; font-size: .95rem;
            transition: all .2s;
        }
        .plan-cta-outline {
            border: 1.5px solid var(--border); color: var(--text);
        }
        .plan-cta-outline:hover { border-color: var(--p); color: var(--p); }
        .plan-cta-solid {
            background: var(--p); color: white;
            box-shadow: 0 3px 14px rgba(43,169,155,.38);
        }
        .plan-cta-solid:hover { background: var(--pd); transform: translateY(-1px); }

        /* ── FAQ ────────────────────────────────────────── */
        .faq-bg { background: var(--white); }
        .faq-list { max-width: 720px; margin: 0 auto; }
        .faq-item { border-bottom: 1px solid var(--border); }
        .faq-item input[type="checkbox"] { display: none; }
        .faq-label {
            display: flex; justify-content: space-between; align-items: center;
            padding: 22px 0; cursor: pointer;
            font-size: 1rem; font-weight: 650; color: var(--text);
            user-select: none; gap: 16px;
        }
        .faq-label:hover { color: var(--p); }
        .faq-icon {
            width: 28px; height: 28px; border-radius: 50%;
            background: var(--pl); color: var(--p);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 400; flex-shrink: 0;
            transition: transform .3s, background .2s;
        }
        .faq-item input:checked ~ .faq-label .faq-icon {
            transform: rotate(45deg); background: var(--p); color: white;
        }
        .faq-body {
            max-height: 0; overflow: hidden;
            transition: max-height .35s ease, padding .35s ease;
        }
        .faq-item input:checked ~ .faq-body {
            max-height: 300px; padding-bottom: 20px;
        }
        .faq-body p { color: var(--muted); font-size: .95rem; line-height: 1.75; }

        /* ── FINAL CTA ──────────────────────────────────── */
        .cta-section { background: var(--bg); }
        .cta-card {
            background: linear-gradient(135deg, var(--p) 0%, #52d4b0 50%, #7ef0a0 100%);
            border-radius: 24px; padding: 64px 48px;
            text-align: center;
        }
        .cta-card h2 {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 900; color: white; margin-bottom: 16px;
            letter-spacing: -.5px;
        }
        .cta-card p { color: rgba(255,255,255,.85); font-size: 1.05rem; margin-bottom: 36px; }
        .btn-cta {
            padding: 15px 40px; background: white; color: var(--pd);
            border-radius: 12px; font-size: 17px; font-weight: 800;
            box-shadow: 0 4px 24px rgba(0,0,0,.15);
            transition: all .25s; display: inline-block;
        }
        .btn-cta:hover { transform: translateY(-3px); box-shadow: 0 10px 36px rgba(0,0,0,.2); }

        /* ── FOOTER ─────────────────────────────────────── */
        footer {
            background: var(--text);
            padding: 56px 6% 32px;
        }
        .footer-inner {
            max-width: 1120px; margin: 0 auto;
        }
        .footer-top {
            display: grid;
            grid-template-columns: 1.8fr 1fr 1fr 1fr;
            gap: 40px; margin-bottom: 48px;
        }
        .footer-brand img { height: 28px; margin-bottom: 16px; filter: brightness(0) invert(1) opacity(.7); }
        .footer-brand p { color: #7dd8d1; font-size: .88rem; line-height: 1.7; max-width: 260px; }
        .footer-col h4 {
            color: white; font-size: .85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .8px;
            margin-bottom: 16px;
        }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .footer-col ul li a {
            color: #7dd8d1; font-size: .88rem; transition: color .2s;
        }
        .footer-col ul li a:hover { color: white; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,.08);
            padding-top: 24px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px;
        }
        .footer-bottom p { color: #4b7772; font-size: .83rem; }
        .footer-bottom a { color: #7dd8d1; }
        .footer-bottom a:hover { color: white; }
        .footer-hr-badge {
            display: flex; align-items: center; gap: 6px;
            color: #4b7772; font-size: .83rem;
        }

        /* ── RESPONSIVE ─────────────────────────────────── */
        @@media (max-width: 900px) {
            .eracun-card { grid-template-columns: 1fr; }
            .eracun-date-box { display: none; }
            .footer-top { grid-template-columns: 1fr 1fr; }
        }
        @@media (max-width: 640px) {
            nav { padding: 0 4%; }
            .nav-link { display: none; }
            section { padding: 64px 4%; }
            .eracun-card { padding: 36px 28px; }
            .cta-card { padding: 44px 24px; }
            .footer-top { grid-template-columns: 1fr; gap: 28px; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- ══ NAVIGATION ═════════════════════════════════════ -->
<nav>
    <a href="/" class="nav-logo">
        <img src="/img/placko-logo.svg" alt="plačko.app">
    </a>
    <div class="nav-links">
        <a href="#znacajke" class="nav-link">Značajke</a>
        <a href="#cijene" class="nav-link">Cijene</a>
        <a href="#faq" class="nav-link">FAQ</a>
        <a href="/admin" class="btn-nav-outline">Prijava</a>
        <a href="/admin/register" class="btn-nav-solid">Isprobaj besplatno</a>
    </div>
</nav>

<!-- ══ HERO ═══════════════════════════════════════════ -->
<header class="hero">
    <div class="hero-eyebrow">
        <span class="hero-eyebrow-dot"></span>
        Besplatno za paušalne obrtnike
    </div>

    <img src="/img/placko-icon.svg" class="hero-icon" alt="plačko.app ikona" width="96" height="96">

    <h1>Fakturiranje bez<br><em>muke i suza.</em></h1>

    <p class="hero-sub">
        plačko.app je moderna aplikacija za izdavanje računa, fiskalizaciju,
        eRačun i porezne obrasce — napravljena za paušalne obrtnike i male
        tvrtke u Hrvatskoj.
    </p>

    <div class="hero-cta">
        <a href="/admin/register" class="btn-primary">
            ➜ Počni besplatno
        </a>
        <a href="#znacajke" class="btn-ghost">
            Što sve nudi?
        </a>
    </div>

    <div class="hero-trust">
        <div class="hero-trust-item"><span>✓</span> Bez kreditne kartice</div>
        <div class="hero-trust-item"><span>✓</span> FINA fiskalizacija</div>
        <div class="hero-trust-item"><span>✓</span> UBL 2.1 eRačun</div>
        <div class="hero-trust-item"><span>✓</span> 100% Made in Croatia</div>
    </div>
</header>

<!-- ══ STATS ══════════════════════════════════════════ -->
<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat">
            <div class="stat-num">UBL 2.1</div>
            <div class="stat-label">PEPPOL BIS 3.0 standard</div>
        </div>
        <div class="stat">
            <div class="stat-num">EN 16931</div>
            <div class="stat-label">EU norma za eRačun</div>
        </div>
        <div class="stat">
            <div class="stat-num">FINA</div>
            <div class="stat-label">Certifikati + fiskalizacija</div>
        </div>
        <div class="stat">
            <div class="stat-num">HUB-3</div>
            <div class="stat-label">Barkod za uplatu</div>
        </div>
    </div>
</div>

<!-- ══ FEATURES ═══════════════════════════════════════ -->
<section id="znacajke" class="features-bg">
    <div class="section-inner">
        <div class="section-header center">
            <span class="section-tag">Značajke</span>
            <h2 class="section-title">Sve što ti treba, ništa što ne trebaš</h2>
            <p class="section-sub">
                Napravljeno specifično za hrvatsko tržište — OIB, IBAN, PDV stope,
                fiskalizacija i eRačun bez dodatnih komplikacija.
            </p>
        </div>
        <div class="features-grid">
            <article class="feature-card">
                <div class="feature-icon-wrap">📄</div>
                <h3>Izlazni računi (IRA)</h3>
                <p>Izrada računa u sekundi s automatskim izračunom PDV-a, rabata i ukupnog iznosa. PDF preuzimanje, arhiviranje i slanje e-mailom direktno iz aplikacije.</p>
                <span class="feature-tag">HUB-3 barkod</span>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrap">🔐</div>
                <h3>Fiskalizacija</h3>
                <p>Gotovinska naplata po Zakonu o fiskalizaciji (NN 133/12). FINA certifikat, automatski ZKI i JIR kod, ispis na računu. Podrška za demo i produkcijsko okruženje.</p>
                <span class="feature-tag">FINA certifikat</span>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrap">📨</div>
                <h3>eRačun — UBL 2.1</h3>
                <p>Generiranje elektroničkih računa u PEPPOL BIS Billing 3.0 formatu (EN 16931). Spreman za B2G slanje prema državnim tijelima. B2B dolazi do Q4 2026.</p>
                <span class="feature-tag">PEPPOL ready</span>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrap">🔁</div>
                <h3>Pretplate klijenata</h3>
                <p>Pratite godišnje, kvartalne i mjesečne pretplate. Automatska e-mail upozorenja prije isteka, jednim klikom produljenje. Vlastiti SMTP server.</p>
                <span class="feature-tag">Automatizacija</span>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrap">📊</div>
                <h3>Porezni obrasci</h3>
                <p>PO-SD obrazac za paušalne obrtnike automatski izračunat iz vaših računa — paušalni izdatci 30%, osobni odbitak, porezna obveza. IRA knjiga po godinama.</p>
                <span class="feature-tag">PO-SD • IRA</span>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrap">🏢</div>
                <h3>Više tvrtki</h3>
                <p>Imaš obrt i d.o.o.? Upravljajte svim tvrtkama iz jednog korisničkog računa, potpuno odvojene financije i dokumenti, brzo prebacivanje između profila.</p>
                <span class="feature-tag">Multi-tenant</span>
            </article>
        </div>
    </div>
</section>

<!-- ══ HOW IT WORKS ════════════════════════════════════ -->
<section class="how-bg">
    <div class="section-inner">
        <div class="section-header center">
            <span class="section-tag">Kako radi</span>
            <h2 class="section-title">Spreman za rad za 3 minute</h2>
            <p class="section-sub">Nema instalacije, nema konfiguracije. Otvoriš, uneseš podatke i kreneš.</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <h3>Registracija</h3>
                <p>Kreiraj besplatan račun, unesi podatke o svom obrtu ili tvrtki — naziv, OIB, IBAN, logo.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h3>Dodaj klijente i usluge</h3>
                <p>Unesi klijente i cjenik usluga. Kod svakog novog računa birašu iz popisa — bez ponovnog tipkanja.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h3>Izdaj račun</h3>
                <p>Odaberi klijenta, stavke i pritisni Spremi. PDF je gotov, možeš ga poslati e-mailom ili preuzeti za eRačun.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ ERACUN ══════════════════════════════════════════ -->
<section class="eracun-section">
    <div class="section-inner">
        <div class="eracun-card">
            <div>
                <div class="eracun-badge-pill">⚡ Već dostupno + dolazi</div>
                <h2>eRačun — spreman za budućnost</h2>
                <p>
                    plačko.app već danas generira valjani UBL 2.1 XML u PEPPOL BIS Billing 3.0
                    formatu. Šalješ račune HEP-u, bolnicama, školama ili ministarstvima?
                    eRačun je obavezan za B2G — i mi smo već tu.
                    B2B mandate dolazi do kraja 2026. i bit ćemo spremi i za to.
                </p>
                <div class="eracun-features-list">
                    <span class="eracun-chip">UBL 2.1</span>
                    <span class="eracun-chip">PEPPOL BIS 3.0</span>
                    <span class="eracun-chip">EN 16931</span>
                    <span class="eracun-chip">B2G već danas</span>
                    <span class="eracun-chip">FINA platforma</span>
                </div>
                <div class="progress-wrap">
                    <div class="progress-meta">
                        <span>B2B FINA API integracija u razvoju</span>
                        <span>Q4 2026</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>
            <div class="eracun-date-box">
                <div class="year">2026</div>
                <div class="quarter">Q4</div>
                <div class="label">B2B mandate</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ PRICING ═════════════════════════════════════════ -->
<section id="cijene" class="pricing-bg">
    <div class="section-inner">
        <div class="section-header center">
            <span class="section-tag">Cijene</span>
            <h2 class="section-title">Jednostavne cijene, bez iznenađenja</h2>
            <p class="section-sub">Starter plan je besplatan zauvijek za paušalne obrtnike s manjim opsegom rada.</p>
        </div>
        <div class="plans">
            <div class="plan">
                <div class="plan-name">Starter</div>
                <div class="plan-price">0 € <sub>/ zauvijek</sub></div>
                <div class="plan-desc">Za paušalne obrtnike koji tek kreću.</div>
                <div class="plan-divider"></div>
                <ul class="plan-features">
                    <li><span class="check">✓</span> 1 tvrtka / obrt</li>
                    <li><span class="check">✓</span> Neograničeni računi</li>
                    <li><span class="check">✓</span> PDF + HUB-3 barkod</li>
                    <li><span class="check">✓</span> PO-SD obrazac</li>
                    <li><span class="check">✓</span> IRA knjiga</li>
                    <li class="no"><span class="cross">○</span> Slanje e-mailom</li>
                    <li class="no"><span class="cross">○</span> Više tvrtki</li>
                    <li class="no"><span class="cross">○</span> eRačun</li>
                </ul>
                <a href="/admin/register" class="plan-cta plan-cta-outline">Počni besplatno</a>
            </div>

            <div class="plan featured">
                <div class="plan-badge">Najpopularniji</div>
                <div class="plan-name">Pro</div>
                <div class="plan-price">4,99 € <sub>/ mj</sub></div>
                <div class="plan-desc">Za aktivne obrtnike i male tvrtke.</div>
                <div class="plan-divider"></div>
                <ul class="plan-features">
                    <li><span class="check">✓</span> Više tvrtki</li>
                    <li><span class="check">✓</span> Neograničeni računi</li>
                    <li><span class="check">✓</span> Slanje e-mailom (SMTP)</li>
                    <li><span class="check">✓</span> Pretplate klijenata</li>
                    <li><span class="check">✓</span> Svi obrasci</li>
                    <li><span class="check">✓</span> Arhiviranje PDF-a</li>
                    <li><span class="check">✓</span> eRačun XML download</li>
                    <li class="no"><span class="cross">○</span> FINA API slanje (Q4 2026)</li>
                </ul>
                <a href="/admin/register" class="plan-cta plan-cta-solid">Isprobaj Pro</a>
            </div>

            <div class="plan">
                <div class="plan-name">Business</div>
                <div class="plan-price">12,99 € <sub>/ mj</sub></div>
                <div class="plan-desc">Za veće timove i računovođe.</div>
                <div class="plan-divider"></div>
                <ul class="plan-features">
                    <li><span class="check">✓</span> Sve iz Pro plana</li>
                    <li><span class="check">✓</span> eRačun B2B + B2G slanje</li>
                    <li><span class="check">✓</span> Više korisnika / timova</li>
                    <li><span class="check">✓</span> Računovođa pristup</li>
                    <li><span class="check">✓</span> API pristup</li>
                    <li><span class="check">✓</span> Prioritetna podrška (SLA)</li>
                    <li><span class="check">✓</span> Fiskalizacija</li>
                </ul>
                <a href="/admin/register" class="plan-cta plan-cta-outline">Kontaktiraj nas</a>
            </div>
        </div>
    </div>
</section>

<!-- ══ FAQ ════════════════════════════════════════════ -->
<section id="faq" class="faq-bg">
    <div class="section-inner">
        <div class="section-header center">
            <span class="section-tag">FAQ</span>
            <h2 class="section-title">Često postavljana pitanja</h2>
        </div>
        <div class="faq-list">

            <div class="faq-item">
                <input type="checkbox" id="faq1">
                <label class="faq-label" for="faq1">
                    Je li plačko.app besplatan?
                    <span class="faq-icon">+</span>
                </label>
                <div class="faq-body">
                    <p>Da — Starter plan je besplatan zauvijek i uključuje sve što paušalni obrtnik s manjim opsegom rada treba: neograničene račune, PDF s HUB-3 barkodom i PO-SD obrazac. Pro plan (4,99 €/mj) donosi slanje e-mailom, pretplate klijenata i eRačun XML export.</p>
                </div>
            </div>

            <div class="faq-item">
                <input type="checkbox" id="faq2">
                <label class="faq-label" for="faq2">
                    Trebam li FINA certifikat?
                    <span class="faq-icon">+</span>
                </label>
                <div class="faq-body">
                    <p>Samo ako naplaćuješ gotovinom ili karticom — tada je fiskalizacija zakonska obveza (NN 133/12) i trebaš FINA fiskalizacijski certifikat (.p12). Za transakcijsko plaćanje (virman) fiskalizacija nije potrebna. Certifikat se uzima u FINA poslovnici, besplatan je ili uz minimalnu naknadu.</p>
                </div>
            </div>

            <div class="faq-item">
                <input type="checkbox" id="faq3">
                <label class="faq-label" for="faq3">
                    Mogu li slati eRačune državnim tijelima (B2G)?
                    <span class="faq-icon">+</span>
                </label>
                <div class="faq-body">
                    <p>Da — plačko.app već danas generira valjani UBL 2.1 XML u PEPPOL BIS Billing 3.0 formatu koji je kompatibilan s FINA eRačun platformom. B2G slanje (bolnice, škole, ministarstva, HEP i sl.) je podržano. Direktna FINA API integracija za automatsko slanje dolazi u Q4 2026.</p>
                </div>
            </div>

            <div class="faq-item">
                <input type="checkbox" id="faq4">
                <label class="faq-label" for="faq4">
                    Podržavate li više tvrtki na jednom računu?
                    <span class="faq-icon">+</span>
                </label>
                <div class="faq-body">
                    <p>Da — od Pro plana nadalje možeš upravljati neograničenim brojem tvrtki (obrt, d.o.o., j.d.o.o.) iz jednog korisničkog računa. Svaka tvrtka ima potpuno odvojene račune, klijente, usluge i dokumente.</p>
                </div>
            </div>

            <div class="faq-item">
                <input type="checkbox" id="faq5">
                <label class="faq-label" for="faq5">
                    Mogu li koristiti vlastiti e-mail server za slanje računa?
                    <span class="faq-icon">+</span>
                </label>
                <div class="faq-body">
                    <p>Da — u Postavkama možeš konfigurirati vlastiti SMTP server (host, port, TLS/SSL, korisnik, lozinka). Računi se šalju s tvojem domenom i iz tvog inboxa, ne s neke zajedničke adrese. Svaka tvrtka ima vlastite SMTP postavke.</p>
                </div>
            </div>

            <div class="faq-item">
                <input type="checkbox" id="faq6">
                <label class="faq-label" for="faq6">
                    Gdje se čuvaju moji podaci?
                    <span class="faq-icon">+</span>
                </label>
                <div class="faq-body">
                    <p>Podaci se čuvaju na sigurnim serverima u EU. FINA certifikati se pohranjuju enkriptirano na serveru, lozinke su kriptirane. Aplikacija je multi-tenant arhitekture — svaka tvrtka vidi isključivo svoje podatke.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══ FINAL CTA ═══════════════════════════════════════ -->
<section class="cta-section">
    <div class="section-inner">
        <div class="cta-card">
            <h2>Spreman prestati plaćati previše?</h2>
            <p>Kreni besplatno danas. Bez kreditne kartice, bez skrivenih troškova.</p>
            <a href="/admin/register" class="btn-cta">Registriraj se besplatno →</a>
        </div>
    </div>
</section>

<!-- ══ FOOTER ══════════════════════════════════════════ -->
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
                    <li><a href="#znacajke">Značajke</a></li>
                    <li><a href="#cijene">Cijene</a></li>
                    <li><a href="#faq">FAQ</a></li>
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
                    <li><a href="#">Uvjeti korištenja</a></li>
                    <li><a href="#">Politika privatnosti</a></li>
                    <li><a href="#">GDPR</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} <strong style="color:#7dd8d1;">plačko.app</strong> — Sva prava pridržana</p>
            <div class="footer-hr-badge">
                🇭🇷 Napravljeno u Hrvatskoj
            </div>
        </div>
    </div>
</footer>

</body>
</html>
