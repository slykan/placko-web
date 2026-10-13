@extends('layouts.blog', [
    'title' => 'Kontakt',
    'description' => 'Kontakt plačko.app — pitanja o aplikaciji, prijedlozi i ispravci vodiča.',
    'nav' => '',
])

@section('content')
    <span class="page-eyebrow">Kontakt</span>
    <h1 class="page-title">Javi nam se</h1>
    <p class="page-sub">Pitanje o aplikaciji, prijedlog nove mogućnosti ili si u vodiču primijetio nešto što više nije točno? Pišemo natrag u pravilu unutar jednog radnog dana.</p>

    <div class="tool-grid">
        <a href="mailto:info@on-click.hr?subject=pla%C4%8Dko.app" class="tool-card">
            <div class="ico">✉️</div>
            <h2>E-mail</h2>
            <p>info@on-click.hr</p>
        </a>
        <div class="tool-card">
            <div class="ico">📍</div>
            <h2>Adresa</h2>
            <p>On-Click<br>Ilirska 27, 31000 Osijek<br>Hrvatska</p>
        </div>
        <a href="/admin/register" class="tool-card">
            <div class="ico">🚀</div>
            <h2>Isprobaj aplikaciju</h2>
            <p>Besplatan račun za paušaliste, bez kreditne kartice.</p>
        </a>
    </div>

    <div class="tool-text">
        <h2>Prije nego pišeš</h2>
        <ul>
            <li>Pitanja o porezu i doprinosima: pogledaj <a href="/novosti?kategorija=vodic" style="color:var(--p);font-weight:700;">vodiče</a> — za konkretan slučaj obrati se Poreznoj upravi ili knjigovođi.</li>
            <li>Kod prijave greške u aplikaciji pomaže ako navedeš što si radio, koji preglednik koristiš i (ako možeš) priložiš snimku zaslona.</li>
        </ul>
    </div>
@endsection
