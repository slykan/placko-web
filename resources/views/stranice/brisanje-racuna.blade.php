@extends('layouts.blog', [
    'title' => 'Brisanje računa',
    'description' => 'Kako trajno obrisati korisnički račun na plačko.app i koji se podaci pritom brišu.',
    'nav' => '',
])

@section('content')
    <span class="page-eyebrow">Pravno</span>
    <h1 class="page-title">Brisanje računa</h1>
    <p class="page-sub">Korisnički račun i podatke u aplikaciji plačko.app možeš obrisati sam, u bilo kojem trenutku.</p>

    @if (request()->boolean('obrisan'))
        <div style="margin-top:24px;padding:16px 20px;border-radius:12px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-weight:600;">
            Tvoj račun i podaci su obrisani. Hvala što si koristio plačko.app.
        </div>
    @endif

    <article class="post-body" style="margin-top:32px;">
        <h2>Kako obrisati račun</h2>
        <ol>
            <li>Prijavi se na <a href="/admin/login" style="color:var(--p);font-weight:700;">placko.app</a> (u pregledniku ili u Android aplikaciji).</li>
            <li>Otvori <strong>Postavke</strong> i spusti se do dna stranice, do dijela <strong>Opasna zona</strong>.</li>
            <li>Klikni <strong>Obriši račun i sve podatke</strong>, za potvrdu upiši svoju email adresu i potvrdi.</li>
        </ol>
        <p>Ako se ne možeš prijaviti, zahtjev za brisanje pošalji s email adrese računa na
            <a href="mailto:info@on-click.hr?subject=Brisanje%20ra%C4%8Duna%20pla%C4%8Dko.app" style="color:var(--p);font-weight:700;">info@on-click.hr</a>,
            a račun ćemo obrisati u roku od 30 dana.</p>

        <h2>Što se briše</h2>
        <ul>
            <li>korisnički račun (ime, email, lozinka ili Google prijava) i sve prijave na uređajima,</li>
            <li>sve tvrtke i obrti kojima si jedini korisnik, sa svim podacima: računi, ponude, klijenti, usluge, skladište, primke, inventure, radni nalozi, zaposlenici, pretplate i ulazni eRačuni,</li>
            <li>logo, FINA i eRačun certifikati te arhivirani PDF-ovi računa.</li>
        </ul>
        <p>Tvrtke koje dijeliš s drugim korisnicima ostaju njima, a tebe se samo uklanja iz njih.</p>

        <h2>Prije brisanja</h2>
        <p>Brisanje je trajno i ne može se poništiti. Izdani računi su knjigovodstvene isprave koje se po zakonu čuvaju
            11 godina, pa ih prije brisanja preuzmi (PDF, eRačun XML, izvještaji).</p>
    </article>
@endsection
