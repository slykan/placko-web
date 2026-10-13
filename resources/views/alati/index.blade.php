@extends('layouts.blog', [
    'title' => 'Besplatni alati za obrtnike',
    'description' => 'Besplatni online alati za paušaliste i obrtnike: kalkulator paušalnog obrta 2026., HUB-3 uplatnica s barkodom, provjera OIB-a i IBAN-a, PDV kalkulator.',
    'nav' => 'alati',
    'ads' => true,
])

@section('content')
    <span class="page-eyebrow">Besplatni alati</span>
    <h1 class="page-title">Alati za obrtnike i paušaliste</h1>
    <p class="page-sub">Bez prijave i bez registracije. Sve se računa u tvom pregledniku — upisani podaci ne šalju se nikamo.</p>

    <div class="tool-grid">
        @foreach ($alati as $a)
            <a href="{{ url('/alati/'.$a['slug']) }}" class="tool-card">
                <div class="ico">{{ $a['ico'] }}</div>
                <h2>{{ $a['title'] }}</h2>
                <p>{{ $a['excerpt'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="tool-text">
        <h2>Vodiči uz alate</h2>
        <ul>
            <li><a href="{{ route('novosti.show', 'pausalni-obrt-2026-porez-doprinosi-rokovi') }}" style="color:var(--p);font-weight:700;">Paušalni obrt 2026.: porezni razredi, doprinosi i rokovi</a></li>
            <li><a href="{{ route('novosti.show', 'sto-mora-pisati-na-racunu-pausalni-obrt') }}" style="color:var(--p);font-weight:700;">Što mora pisati na računu paušalnog obrta</a></li>
            <li><a href="{{ route('novosti.show', 'fiskalizacija-2-0-eracun-pausalni-obrt') }}" style="color:var(--p);font-weight:700;">Fiskalizacija 2.0 i eRačun za paušaliste</a></li>
            <li><a href="{{ route('novosti.show', 'sidrena-cijena-i-cjenik-usluga-2026') }}" style="color:var(--p);font-weight:700;">Sidrena cijena od 1. listopada 2026.</a></li>
        </ul>
    </div>
@endsection
