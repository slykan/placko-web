@extends('layouts.blog', [
    'title' => 'Uvjeti korištenja',
    'description' => 'Uvjeti korištenja aplikacije plačko.app.',
    'nav' => '',
])

@section('content')
    <span class="page-eyebrow">Pravno</span>
    <h1 class="page-title">Uvjeti korištenja</h1>
    <p class="page-sub">Vrijede od 13. listopada 2026.</p>

    <article class="post-body" style="margin-top:32px;">
        <h2>1. Pružatelj usluge</h2>
        <p>
            Uslugu plačko.app pruža <strong>On-Click</strong>, Ilirska 27, 31000 Osijek, Hrvatska
            (kontakt: info@on-click.hr). Registracijom i korištenjem aplikacije prihvaćaš ove uvjete.
        </p>

        <h2>2. Usluga</h2>
        <p>
            plačko.app je web aplikacija za izradu računa i ponuda, vođenje klijenata i usluga, skladišta, izradu
            poreznih pregleda i obrazaca te fiskalizaciju i izradu eRačuna. Opseg mogućnosti ovisi o odabranom
            planu prema javno objavljenom <a href="/#cijene" style="color:var(--p);font-weight:700;">cjeniku</a>.
        </p>

        <h2>3. Korisnički račun</h2>
        <ul>
            <li>Korisnik je odgovoran za točnost unesenih podataka i za čuvanje pristupnih podataka.</li>
            <li>Korisnik je odgovoran za sadržaj računa i drugih dokumenata koje izdaje putem aplikacije te za ispunjavanje svojih poreznih i računovodstvenih obveza.</li>
            <li>Zabranjeno je korištenje usluge za nezakonite svrhe, slanje neželjene pošte ili pokušaje narušavanja sigurnosti sustava.</li>
        </ul>

        <h2>4. Planovi i plaćanje</h2>
        <p>
            Osnovni plan je besplatan. Plaćeni planovi naplaćuju se prema važećem cjeniku. O promjeni cijena
            korisnike obavještavamo unaprijed; promjena ne vrijedi za već plaćeno razdoblje.
        </p>

        <h2>5. Dostupnost i podaci</h2>
        <p>
            Trudimo se da usluga bude dostupna bez prekida, ali ne jamčimo neprekidnu dostupnost. Preporučujemo da korisnik redovito preuzima PDF-ove svojih računa i
            pregleda za vlastitu arhivu.
        </p>

        <h2>6. Ograničenje odgovornosti</h2>
        <p>
            Aplikacija pomaže u izradi dokumenata prema važećim propisima, ali ne zamjenjuje stručni savjet
            knjigovođe ili porezne službe. Vodiči i alati na stranici informativnog su karaktera. U mjeri u kojoj
            to dopušta zakon, ne odgovaramo za posrednu štetu nastalu korištenjem usluge.
        </p>

        <h2>7. Prestanak korištenja</h2>
        <p>
            Korisnik može u bilo kojem trenutku prestati koristiti uslugu i zatražiti brisanje računa. Možemo
            ukinuti pristup korisniku koji krši ove uvjete.
        </p>

        <h2>8. Privatnost</h2>
        <p>Obrada osobnih podataka opisana je u <a href="/privatnost" style="color:var(--p);font-weight:700;">Politici privatnosti</a>.</p>

        <h2>9. Mjerodavno pravo</h2>
        <p>Na ove uvjete primjenjuje se pravo Republike Hrvatske. Za sporove je nadležan stvarno nadležni sud u Osijeku.</p>
    </article>
@endsection
