@extends('layouts.blog', [
    'title' => 'O plačko.app',
    'description' => 'plačko.app je hrvatska aplikacija za fakturiranje, ponude i porezne obrasce za paušalne obrtnike i male tvrtke. Tko stoji iza nje i zašto je nastala.',
    'nav' => '',
])

@section('content')
    <span class="page-eyebrow">O nama</span>
    <h1 class="page-title">Fakturiranje bez muke i suza — napravljeno u Osijeku</h1>

    <article class="post-body" style="margin-top:32px;">
        <p>
            <strong>plačko.app</strong> je aplikacija za izdavanje računa i ponuda, fiskalizaciju, eRačun i porezne
            obrasce, napravljena posebno za <strong>paušalne obrtnike i male tvrtke u Hrvatskoj</strong>. Nastala je iz
            vrlo konkretne potrebe: većina programa za račune ili je preskupa za mali obrt, ili je napravljena
            za knjigovođe, a ne za ljude koji račun izdaju između dva posla.
        </p>

        <h2>Tko stoji iza aplikacije</h2>
        <p>
            plačko.app razvija <strong>On-Click</strong> iz Osijeka — mali tim koji se godinama bavi izradom web
            stranica, web aplikacija i mobilnih aplikacija za obrtnike i tvrtke. I sami smo obrt: aplikaciju
            svakodnevno koristimo za vlastite račune i ponude, pa svaka promjena prvo prođe kroz naše ruke.
        </p>

        <h2>Što radimo drukčije</h2>
        <ul>
            <li><strong>Hrvatski propisi su ugrađeni</strong> — OIB, HUB-3 barkod, numeracija računa, PO-SD, knjiga izlaznih računa, fiskalizacija i UBL 2.1 eRačun.</li>
            <li><strong>Besplatno za paušaliste</strong> — osnovni plan nema rok trajanja i nema ograničenja broja računa.</li>
            <li><strong>Bez instalacije</strong> — radi u pregledniku i može se instalirati na mobitel kao aplikacija.</li>
            <li><strong>Transparentne cijene</strong> — cjenik je javan, uz sidrene cijene i CSV verziju.</li>
        </ul>

        <h2>Besplatni sadržaj</h2>
        <p>
            Uz aplikaciju objavljujemo <a href="/novosti?kategorija=vodic" style="color:var(--p);font-weight:700;">vodiče</a>
            o porezu, doprinosima, fiskalizaciji i eRačunu te <a href="/alati" style="color:var(--p);font-weight:700;">besplatne alate</a>
            — kalkulator paušalnog obrta, generator HUB-3 uplatnice, provjeru OIB-a i IBAN-a i PDV kalkulator.
            Vodiče redovito ažuriramo kad se propisi promijene.
        </p>

        <h2>Kontakt</h2>
        <p>
            On-Click · Ilirska 27, 31000 Osijek, Hrvatska<br>
            E-mail: <a href="mailto:info@on-click.hr" style="color:var(--p);font-weight:700;">info@on-click.hr</a><br>
            Web: <a href="https://www.on-click.hr" style="color:var(--p);font-weight:700;" target="_blank" rel="noopener">on-click.hr</a>
        </p>
    </article>
@endsection
