@extends('layouts.blog', [
    'title' => 'Politika privatnosti',
    'description' => 'Kako plačko.app prikuplja, koristi i štiti osobne podatke, uključujući kolačiće i oglase.',
    'nav' => '',
])

@section('content')
    <span class="page-eyebrow">Pravno</span>
    <h1 class="page-title">Politika privatnosti</h1>
    <p class="page-sub">Vrijedi od 13. listopada 2026.</p>

    <article class="post-body" style="margin-top:32px;">
        <h2>1. Tko obrađuje podatke</h2>
        <p>
            Voditelj obrade podataka posjetitelja web stranice i korisnika aplikacije plačko.app je
            <strong>On-Click</strong>, Ilirska 27, 31000 Osijek, Hrvatska (dalje: „mi"). Za sva pitanja o privatnosti
            piši na <a href="mailto:info@on-click.hr" style="color:var(--p);font-weight:700;">info@on-click.hr</a>.
        </p>

        <h2>2. Koje podatke prikupljamo</h2>
        <ul>
            <li><strong>Podaci o korisničkom računu</strong> — ime i prezime, e-mail, lozinka (pohranjena isključivo kao kriptografski sažetak) ili podaci iz Google prijave ako je koristiš.</li>
            <li><strong>Podaci o tvrtki/obrtu</strong> koje korisnik unese — naziv, OIB, adresa, IBAN, logo, postavke.</li>
            <li><strong>Poslovni podaci</strong> koje korisnik unosi u aplikaciju — klijenti, računi, ponude, usluge, skladište.</li>
            <li><strong>Tehnički podaci</strong> — IP adresa, vrsta preglednika i zapisi o pristupu, radi sigurnosti i rada usluge.</li>
        </ul>
        <p>
            Podatke o klijentima koje korisnik unosi u aplikaciju (npr. podatke kupaca na računima) obrađujemo
            <strong>u ime korisnika</strong>, kao izvršitelj obrade — korisnik je za te podatke voditelj obrade.
        </p>

        <h2>3. Zašto ih obrađujemo</h2>
        <ul>
            <li>za pružanje usluge (izrada računa, ponuda, obrazaca, slanje e-mailova iz aplikacije) — ugovorni odnos,</li>
            <li>za sigurnost, sprječavanje zlouporabe i otklanjanje grešaka — legitimni interes,</li>
            <li>za ispunjenje zakonskih obveza (npr. računovodstvenih) — zakonska obveza,</li>
            <li>za prikaz oglasa na javnim sadržajnim stranicama (vodiči, alati) — uz tvoju privolu gdje je to propisano.</li>
        </ul>

        <h2>4. Kolačići i oglasi</h2>
        <p>
            Aplikacija koristi nužne kolačiće za prijavu i sesiju. Za zaštitu obrazaca od robota koristimo
            Cloudflare Turnstile.
        </p>
        <p>
            Na javnim stranicama s vodičima i alatima prikazujemo oglase putem usluge <strong>Google AdSense</strong>.
            Google i njegovi partneri mogu koristiti kolačiće za prikaz oglasa na temelju tvojih prethodnih posjeta
            ovoj i drugim web stranicama. Personalizirano oglašavanje možeš isključiti u
            <a href="https://adssettings.google.com" target="_blank" rel="noopener" style="color:var(--p);font-weight:700;">Googleovim postavkama oglasa</a>,
            a više o tome kako Google koristi podatke pročitaj na
            <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener" style="color:var(--p);font-weight:700;">policies.google.com</a>.
            Unutar same aplikacije (nakon prijave) oglasa nema.
        </p>
        <p>
            Besplatni alati (kalkulatori, HUB-3 uplatnica, provjera OIB-a/IBAN-a) rade u tvom pregledniku —
            podaci koje u njih upišeš ne šalju se na naše poslužitelje.
        </p>

        <h2>5. Kome podatke dajemo</h2>
        <p>
            Podatke ne prodajemo. Koristimo pružatelje usluga koji ih obrađuju po našem nalogu: pružatelja hostinga poslužitelja, Cloudflare (zaštita i dostava sadržaja), Google (prijava putem Google računa, oglasi
            na javnim stranicama) te pružatelje e-mail usluge. Podaci za fiskalizaciju šalju se Poreznoj upravi
            kad korisnik fiskalizira račun.
        </p>

        <h2>6. Koliko dugo čuvamo podatke</h2>
        <p>
            Podatke o računu čuvamo dok je korisnički račun aktivan. Nakon brisanja računa podatke brišemo, osim
            onih koje smo zakonski dužni čuvati. Korisnik je sam odgovoran za čuvanje svojih računa u rokovima koje
            propisuju porezni i računovodstveni propisi — zato aplikacija omogućuje preuzimanje PDF-ova i pregleda.
        </p>

        <h2>7. Tvoja prava</h2>
        <p>
            Imaš pravo na pristup, ispravak, brisanje, ograničenje obrade, prenosivost podataka i prigovor, te pravo
            povući privolu u bilo kojem trenutku. Zahtjev pošalji na info@on-click.hr. Ako smatraš da su ti prava
            povrijeđena, možeš podnijeti pritužbu Agenciji za zaštitu osobnih podataka (AZOP, azop.hr).
        </p>

        <h2>8. Izmjene</h2>
        <p>O bitnim izmjenama ove politike obavijestit ćemo korisnike e-mailom ili obaviješću u aplikaciji.</p>
    </article>
@endsection
