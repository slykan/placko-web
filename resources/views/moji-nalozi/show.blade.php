@extends('moji-nalozi.layout')

@section('title', 'Nalog '.$nalog->broj)

@section('nav')
    <a href="{{ route('moji-nalozi.index', $zaposlenik->portal_token) }}">← Moji nalozi</a>
@endsection

@section('content')
@php
    $token = $zaposlenik->portal_token;
    $statusi = \App\Models\RadniNalog::statusi();
    $mojiRadovi = old('radovi', $nalog->radovi->where('zaposlenik_id', $zaposlenik->id)
        ->map(fn ($r) => ['id' => $r->id, 'dolazak' => trim($r->datum->toDateString().' '.substr((string) $r->vrijeme_dolaska, 0, 5)), 'sati' => (float) $r->sati, 'opis' => $r->opis])->values()->all());
    $tudiRadovi = $nalog->radovi->where('zaposlenik_id', '!=', $zaposlenik->id);
    $stavke = old('stavke', $nalog->stavke
        ->map(fn ($s) => ['id' => $s->id, 'naziv' => $s->naziv, 'kolicina' => (float) $s->kolicina, 'jedinica_mjere' => $s->jedinica_mjere])->values()->all());
    $klijent = $nalog->klijent;
    $adresa = $nalog->lokacija ?: ($klijent ? trim($klijent->adresa.', '.$klijent->mjesto, ', ') : null);
@endphp

@if(! $prihvacen && $nalog->jeOtvoren())
    <div class="card prihvati {{ request()->boolean('prihvati') ? 'istakni' : '' }}">
        <p style="margin-bottom:10px;font-weight:600;">Dodijeljen ti je novi nalog. Potvrdi da ga preuzimaš.</p>
        <form method="post" action="{{ route('moji-nalozi.prihvati', [$token, $nalog->id]) }}">
            @csrf
            <button class="btn btn-p btn-full" type="submit">✓ Prihvaćam nalog</button>
        </form>
    </div>
@endif

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;">
        <h1>{{ $nalog->broj }}</h1>
        <span class="badge b-{{ $nalog->status }}">{{ $statusi[$nalog->status] ?? $nalog->status }}</span>
    </div>
    <dl>
        <dt>Klijent</dt><dd>{{ $klijent?->naziv ?? 'Interni posao' }}</dd>
        @if($klijent?->kontakt_broj)<dt>Telefon</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $klijent->kontakt_broj) }}">{{ $klijent->kontakt_broj }}</a></dd>@endif
        @if($adresa)<dt>Lokacija</dt><dd><a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($adresa) }}" target="_blank" rel="noopener">{{ $adresa }}</a></dd>@endif
        @if($nalog->predmet)<dt>Predmet</dt><dd>{{ $nalog->predmet }}</dd>@endif
        @if($nalog->rok)<dt>Rok</dt><dd class="{{ $nalog->jeOtvoren() && $nalog->rok->isPast() && ! $nalog->rok->isToday() ? 'late' : '' }}">{{ $nalog->rok->format('d.m.Y.') }}</dd>@endif
        <dt>Izvršitelji</dt><dd>{{ $nalog->zaposlenici->map(fn ($z) => $z->ime_prezime.($z->id === $nalog->nositelj_id ? ' (nositelj)' : ''))->implode(', ') }}</dd>
    </dl>
    <h2 style="margin-top:14px;">Opis posla</h2>
    <div class="box">{{ $nalog->opis_posla }}</div>
    @if($nalog->napomena)
        <h2 style="margin-top:14px;">Napomena ureda</h2>
        <div class="box">{{ $nalog->napomena }}</div>
    @endif
</div>

@if($mozeUredivati)
<form method="post" action="{{ route('moji-nalozi.spremi', [$token, $nalog->id]) }}" id="nalog-forma">
    @csrf

    <div class="card">
        <h2>Moji sati</h2>
        <div id="radovi">
            @foreach($mojiRadovi as $i => $r)
                <div class="redak rad">
                    <input type="hidden" name="radovi[{{ $i }}][id]" value="{{ $r['id'] ?? '' }}">
                    <div><label>Datum i vrijeme dolaska</label><input type="text" class="dolazak" name="radovi[{{ $i }}][dolazak]" value="{{ $r['dolazak'] ?? '' }}" required></div>
                    <div><label>Sati</label><input type="number" name="radovi[{{ $i }}][sati]" value="{{ $r['sati'] ?? '' }}" step="0.25" min="0.25" max="24" inputmode="decimal" required></div>
                    <div class="puna"><label>Što je napravljeno</label><input type="text" name="radovi[{{ $i }}][opis]" value="{{ $r['opis'] ?? '' }}" maxlength="255"></div>
                    <button type="button" class="makni" aria-label="Ukloni" onclick="this.closest('.redak').remove()">×</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-s" onclick="dodaj('radovi', 'tpl-rad')">+ Dodaj sate</button>

        @if($tudiRadovi->isNotEmpty())
            <p class="muted" style="margin-top:14px;margin-bottom:4px;">Upisali drugi:</p>
            <ul class="ostali">
                @foreach($tudiRadovi as $r)
                    <li>{{ $r->dolazak() }} · {{ $r->zaposlenik?->ime_prezime ?? '—' }} · <strong>{{ number_format((float) $r->sati, 2, ',', '.') }} h</strong>@if($r->opis) · {{ $r->opis }}@endif</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="card">
        <h2>Materijal</h2>
        <div id="stavke">
            @foreach($stavke as $i => $s)
                <div class="redak mat">
                    <input type="hidden" name="stavke[{{ $i }}][id]" value="{{ $s['id'] ?? '' }}">
                    <div class="puna"><label>Naziv</label><input type="text" name="stavke[{{ $i }}][naziv]" value="{{ $s['naziv'] ?? '' }}" list="usluge" maxlength="255" required></div>
                    <div><label>Količina</label><input type="number" name="stavke[{{ $i }}][kolicina]" value="{{ $s['kolicina'] ?? 1 }}" step="0.001" min="0.001" inputmode="decimal" required></div>
                    <div class="jm"><label>JM</label><input type="text" name="stavke[{{ $i }}][jedinica_mjere]" value="{{ $s['jedinica_mjere'] ?? 'kom' }}" maxlength="20"></div>
                    <button type="button" class="makni" aria-label="Ukloni" onclick="this.closest('.redak').remove()">×</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-s" onclick="dodaj('stavke', 'tpl-mat')">+ Dodaj materijal</button>
        <datalist id="usluge">
            @foreach($usluge as $u)<option value="{{ $u->naziv }}">@endforeach
        </datalist>
    </div>

    <div class="card">
        <h2>Izvršeni radovi</h2>
        <textarea name="izvrseni_radovi" maxlength="5000" placeholder="Što je napravljeno, što je zamijenjeno, preporuke klijentu…">{{ old('izvrseni_radovi', $nalog->izvrseni_radovi) }}</textarea>
    </div>

    <div class="card">
        <h2>Potpis klijenta</h2>
        @if($nalog->potpis_klijenta)
            <div id="postojeci-potpis">
                <img class="potpis-img" src="{{ $nalog->potpis_klijenta }}" alt="Potpis klijenta">
                <p class="muted" style="margin:6px 0 10px;">Potpisano {{ $nalog->potpisano_at?->format('d.m.Y. H:i') }}</p>
                <button type="button" class="btn btn-s" onclick="document.getElementById('postojeci-potpis').style.display='none';document.getElementById('novi-potpis').style.display='block';potpisInit()">Novi potpis</button>
            </div>
        @endif
        <div id="novi-potpis" style="{{ $nalog->potpis_klijenta ? 'display:none' : '' }}">
            <canvas id="potpis"></canvas>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:6px 0 10px;">
                <span class="muted">Klijent se potpisuje prstom.</span>
                <button type="button" class="btn btn-s" style="padding:6px 12px;" onclick="potpisObrisi()">Obriši</button>
            </div>
        </div>
        <label for="potpisao">Ime i prezime klijenta</label>
        <input type="text" id="potpisao" name="potpisao" value="{{ old('potpisao', $nalog->potpisao) }}" maxlength="255">
        <input type="hidden" name="potpis" id="potpis-data">
    </div>

    <div class="card">
        @if($nalog->status === 'zavrsen')
            <p class="check">✓ Nalog je označen kao završen. Izmjene još možeš spremiti dok ured ne izda račun.</p>
        @else
            <label class="check"><input type="checkbox" name="zavrsen" value="1" @checked(old('zavrsen'))> Posao je završen — javi uredu</label>
        @endif
    </div>

    <button class="btn btn-p btn-full" type="submit">Spremi nalog</button>
</form>

<template id="tpl-rad">
    <div class="redak rad">
        <input type="hidden" name="radovi[__i__][id]" value="">
        <div><label>Datum i vrijeme dolaska</label><input type="text" class="dolazak" name="radovi[__i__][dolazak]" value="{{ now()->format('Y-m-d H:i') }}" required></div>
        <div><label>Sati</label><input type="number" name="radovi[__i__][sati]" step="0.25" min="0.25" max="24" inputmode="decimal" required></div>
        <div class="puna"><label>Što je napravljeno</label><input type="text" name="radovi[__i__][opis]" maxlength="255"></div>
        <button type="button" class="makni" aria-label="Ukloni" onclick="this.closest('.redak').remove()">×</button>
    </div>
</template>
<template id="tpl-mat">
    <div class="redak mat">
        <input type="hidden" name="stavke[__i__][id]" value="">
        <div class="puna"><label>Naziv</label><input type="text" name="stavke[__i__][naziv]" list="usluge" maxlength="255" required></div>
        <div><label>Količina</label><input type="number" name="stavke[__i__][kolicina]" value="1" step="0.001" min="0.001" inputmode="decimal" required></div>
        <div class="jm"><label>JM</label><input type="text" name="stavke[__i__][jedinica_mjere]" value="kom" maxlength="20"></div>
        <button type="button" class="makni" aria-label="Ukloni" onclick="this.closest('.redak').remove()">×</button>
    </div>
</template>
@else
    <div class="card">
        <h2>Izvršeni radovi</h2>
        <div class="box">{{ $nalog->izvrseni_radovi ?: '—' }}</div>
        <h2 style="margin-top:14px;">Sati</h2>
        <ul class="ostali">
            @forelse($nalog->radovi as $r)
                <li>{{ $r->dolazak() }} · {{ $r->zaposlenik?->ime_prezime ?? '—' }} · <strong>{{ number_format((float) $r->sati, 2, ',', '.') }} h</strong>@if($r->opis) · {{ $r->opis }}@endif</li>
            @empty
                <li class="muted">Nema upisanih sati.</li>
            @endforelse
        </ul>
        <h2 style="margin-top:14px;">Materijal</h2>
        <ul class="ostali">
            @forelse($nalog->stavke as $s)
                <li>{{ $s->naziv }} · {{ number_format((float) $s->kolicina, 2, ',', '.') }} {{ $s->jedinica_mjere }}</li>
            @empty
                <li class="muted">Bez materijala.</li>
            @endforelse
        </ul>
        <p class="muted" style="margin-top:12px;">Nalog je zaključen i više se ne može mijenjati.</p>
    </div>
@endif
@endsection

@push('head')
<link rel="stylesheet" href="/vendor/flatpickr/flatpickr.min.css">
<style>
    .flatpickr-calendar { font-size: 15px; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #2ba99b; border-color: #2ba99b; }
    .flatpickr-time input { font-size: 18px; }
    input.dolazak[readonly] { background: #fff; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="/vendor/flatpickr/flatpickr.min.js"></script>
<script src="/vendor/flatpickr/hr.js"></script>
<script>
    // Kalendar na hrvatskom: prikaz 04.11.2026. 08:30 (24 h), a na server ide 2026-11-04 08:30.
    function kalendar(el) {
        flatpickr(el, {
            locale: 'hr',
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 5,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'd.m.Y. H:i',
            disableMobile: true,
        });
    }
    document.querySelectorAll('#radovi input.dolazak').forEach(kalendar);

    let brojac = 1000;
    function dodaj(kontejner, predlozak) {
        const html = document.getElementById(predlozak).innerHTML.replaceAll('__i__', brojac++);
        document.getElementById(kontejner).insertAdjacentHTML('beforeend', html);
        const novi = document.getElementById(kontejner).lastElementChild;
        novi.querySelectorAll('input.dolazak').forEach(kalendar);
        novi.querySelector('input[type=number], input[type=text]:not([type=hidden]):not(.dolazak)')?.focus();
    }

    let platno, ctx, crta = false, nacrtano = false;
    function potpisInit() {
        platno = document.getElementById('potpis');
        if (! platno || platno.offsetWidth === 0) return;
        const r = window.devicePixelRatio || 1;
        platno.width = platno.offsetWidth * r;
        platno.height = platno.offsetHeight * r;
        ctx = platno.getContext('2d');
        ctx.scale(r, r);
        ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#162433';
        nacrtano = false;
        if (platno.dataset.spreman) return;
        platno.dataset.spreman = '1';
        const tocka = e => { const b = platno.getBoundingClientRect(); return [e.clientX - b.left, e.clientY - b.top]; };
        platno.addEventListener('pointerdown', e => { crta = true; platno.setPointerCapture(e.pointerId); ctx.beginPath(); ctx.moveTo(...tocka(e)); });
        platno.addEventListener('pointermove', e => { if (! crta) return; ctx.lineTo(...tocka(e)); ctx.stroke(); nacrtano = true; });
        ['pointerup', 'pointercancel'].forEach(t => platno.addEventListener(t, () => { crta = false; }));
    }
    function potpisObrisi() {
        if (! ctx) return;
        ctx.clearRect(0, 0, platno.width, platno.height);
        nacrtano = false;
    }
    document.getElementById('nalog-forma')?.addEventListener('submit', () => {
        if (nacrtano) document.getElementById('potpis-data').value = platno.toDataURL('image/png');
    });
    window.addEventListener('load', potpisInit);
</script>
@endpush
