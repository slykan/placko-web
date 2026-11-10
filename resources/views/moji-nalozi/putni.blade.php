@extends('moji-nalozi.layout')

@section('title', 'Putni nalog '.$nalog->broj)

@section('nav')
    <a href="{{ route('moji-nalozi.index', $zaposlenik->portal_token) }}">← Moji nalozi</a>
@endsection

@php
    $e = fn ($v) => number_format((float) $v, 2, ',', '.');
    $dt = fn ($d) => $d?->format('Y-m-d\TH:i');
    $o = $nalog->obracun;
@endphp

@push('head')
<style>
    select { width: 100%; font: inherit; padding: 10px 12px; border: 1px solid #cfdcd9; border-radius: 10px; background: #fff; }
    input[type=datetime-local], input[type=file] { width: 100%; font: inherit; padding: 9px 10px; border: 1px solid #cfdcd9; border-radius: 10px; background: #fff; }
    .dva { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .polje { margin-bottom: 12px; }
    .trosak { display: flex; justify-content: space-between; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eef2f2; font-size: .95rem; }
    .trosak:last-child { border-bottom: 0; }
    .ukloni { font-size: .82rem; color: #b91c1c; display: flex; gap: 5px; align-items: center; white-space: nowrap; }
    .zbroj { display: flex; justify-content: space-between; padding: 4px 0; }
    .zbroj.velik { font-weight: 800; font-size: 1.1rem; border-top: 1px solid #e2ece9; margin-top: 6px; padding-top: 10px; }
    @media (max-width: 420px) { .dva { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<h1>Putni nalog {{ $nalog->broj }}</h1>
<p class="muted" style="margin-bottom:12px;">{{ $nalog->vrsta === 'inozemni' ? 'Inozemstvo' : 'Tuzemno' }} · izdan {{ $nalog->datum_naloga->format('d.m.Y.') }}</p>

<div class="card">
    <dl>
        <dt>Odredište</dt><dd>{{ $nalog->odrediste }}@if($nalog->vrsta === 'inozemni') ({{ $nalog->drzava }})@endif</dd>
        <dt>Svrha</dt><dd>{{ $nalog->svrha }}</dd>
        <dt>Prijevoz</dt><dd>{{ \App\Models\PutniNalog::prijevozi()[$nalog->prijevoz] ?? $nalog->prijevoz }}@if($nalog->registracija) · {{ $nalog->registracija }}@endif</dd>
        @if((float) $nalog->predujam > 0)<dt>Predujam</dt><dd>{{ $e($nalog->predujam) }} €</dd>@endif
    </dl>
</div>

<form method="POST" action="{{ route('moji-nalozi.putni.spremi', [$zaposlenik->portal_token, $nalog->id]) }}" enctype="multipart/form-data">
    @csrf
    <fieldset @disabled(! $mozeUredivati) style="border:0;">
    <div class="card">
        <h2>Vrijeme putovanja</h2>
        <div class="dva">
            <div class="polje"><label for="polazak">Polazak</label><input type="datetime-local" id="polazak" name="polazak" value="{{ old('polazak', $dt($nalog->polazak_at)) }}"></div>
            <div class="polje"><label for="povratak">Povratak</label><input type="datetime-local" id="povratak" name="povratak" value="{{ old('povratak', $dt($nalog->povratak_at)) }}"></div>
            @if($nalog->vrsta === 'inozemni')
                <div class="polje"><label for="granica_odlazak">Prelazak granice — odlazak</label><input type="datetime-local" id="granica_odlazak" name="granica_odlazak" value="{{ old('granica_odlazak', $dt($nalog->granica_odlazak_at)) }}"></div>
                <div class="polje"><label for="granica_povratak">Prelazak granice — povratak</label><input type="datetime-local" id="granica_povratak" name="granica_povratak" value="{{ old('granica_povratak', $dt($nalog->granica_povratak_at)) }}"></div>
            @endif
        </div>
        @if($nalog->jeAutomobil())
            <h2 style="margin-top:6px;">Kilometri ({{ $nalog->registracija }})</h2>
            <div class="dva">
                <div class="polje"><label for="km_pocetno">Brojilo na polasku</label><input type="number" inputmode="numeric" id="km_pocetno" name="km_pocetno" value="{{ old('km_pocetno', $nalog->km_pocetno) }}"></div>
                <div class="polje"><label for="km_zavrsno">Brojilo na povratku</label><input type="number" inputmode="numeric" id="km_zavrsno" name="km_zavrsno" value="{{ old('km_zavrsno', $nalog->km_zavrsno) }}"></div>
            </div>
        @endif
    </div>

    <div class="card">
        <h2>Troškovi i računi</h2>
        @forelse($nalog->troskovi as $t)
            <div class="trosak">
                <div>{{ \App\Models\PutniNalog::vrsteTroskova()[$t->vrsta] ?? $t->vrsta }}{{ $t->opis ? ' — '.$t->opis : '' }}
                    <div class="muted">{{ $e($t->iznos) }} €{{ $t->privitak ? ' · račun priložen' : '' }}</div></div>
                @if($mozeUredivati)<label class="ukloni"><input type="checkbox" name="ukloni[]" value="{{ $t->id }}"> ukloni</label>@endif
            </div>
        @empty
            <p class="muted" style="margin-bottom:8px;">Još nema upisanih troškova.</p>
        @endforelse

        @if($mozeUredivati)
            <h2 style="margin-top:14px;">Dodaj trošak</h2>
            <div class="dva">
                <div class="polje"><label for="trosak_vrsta">Vrsta</label>
                    <select id="trosak_vrsta" name="trosak_vrsta"><option value="">—</option>
                        @foreach(\App\Models\PutniNalog::vrsteTroskova() as $k => $v)<option value="{{ $k }}" @selected(old('trosak_vrsta') === $k)>{{ $v }}</option>@endforeach
                    </select></div>
                <div class="polje"><label for="trosak_iznos">Iznos (€)</label><input type="number" step="0.01" inputmode="decimal" id="trosak_iznos" name="trosak_iznos" value="{{ old('trosak_iznos') }}"></div>
            </div>
            <div class="polje"><label for="trosak_opis">Opis</label><input type="text" id="trosak_opis" name="trosak_opis" value="{{ old('trosak_opis') }}" placeholder="npr. cestarina Zagreb – Rijeka"></div>
            <div class="polje"><label for="trosak_slika">Slika računa</label><input type="file" id="trosak_slika" name="trosak_slika" accept="image/*,application/pdf" capture="environment"></div>
        @endif
    </div>

    <div class="card">
        <h2>Izvješće s puta</h2>
        <textarea name="izvjesce" placeholder="Što je obavljeno na putu.">{{ old('izvjesce', $nalog->izvjesce) }}</textarea>
    </div>

    @if($mozeUredivati)
        <button type="submit" class="btn btn-p btn-full" style="margin-bottom:14px;">Spremi</button>
    @endif
    </fieldset>
</form>

@if($o)
<div class="card">
    <h2>Obračun</h2>
    @foreach($o['dnevnice'] as $d)
        <div class="zbroj"><span>{{ $d['opis'] }} ({{ rtrim(rtrim(number_format($d['broj'], 1, ',', '.'), '0'), ',') }})</span><span>{{ $e($d['ukupno']) }} €</span></div>
    @endforeach
    @if($o['km_iznos'] > 0)<div class="zbroj"><span>Kilometri ({{ number_format($o['km'], 0, ',', '.') }} km)</span><span>{{ $e($o['km_iznos']) }} €</span></div>@endif
    @if($o['troskovi_ukupno'] > 0)<div class="zbroj"><span>Troškovi</span><span>{{ $e($o['troskovi_ukupno']) }} €</span></div>@endif
    @if($o['predujam'] > 0)<div class="zbroj"><span>Predujam</span><span>− {{ $e($o['predujam']) }} €</span></div>@endif
    <div class="zbroj velik"><span>{{ $o['za_isplatu'] < 0 ? 'Za povrat' : 'Za isplatu' }}</span><span>{{ $e(abs($o['za_isplatu'])) }} €</span></div>
    <p class="muted" style="margin-top:6px;">Konačan iznos potvrđuje ured.</p>
</div>
@endif
@endsection
