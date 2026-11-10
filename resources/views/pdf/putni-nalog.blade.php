<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
@php
    $e = fn ($v) => number_format((float) $v, 2, ',', '.');
    $dt = fn ($d) => $d ? $d->format('d.m.Y. H:i') : '_______________';
    $prijevoz = \App\Models\PutniNalog::prijevozi()[$nalog->prijevoz] ?? $nalog->prijevoz;
    $vrsteTroska = \App\Models\PutniNalog::vrsteTroskova();
@endphp
<style>
@page{margin:10mm 0 8mm}*{margin:0;padding:0;box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;font-size:8.8pt;color:#222}.page{padding:3mm 15mm 0}
.header,.dva,.potpisi{display:table;width:100%}.left,.right{display:table-cell;vertical-align:top;width:50%}.right{text-align:right}
.company{font-size:11pt;font-weight:bold;margin-bottom:1mm}.logo{max-height:18mm;max-width:45mm}
.title{text-align:center;font-size:15pt;font-weight:bold;margin:2mm 0 1mm;letter-spacing:1px}.subtitle{text-align:center;font-size:8.5pt;color:#555;margin-bottom:3mm}
hr{border:0;border-top:1px solid #ccc;margin:3mm 0}h3{font-size:9.5pt;font-weight:bold;margin:2.5mm 0 1mm;text-transform:uppercase;letter-spacing:.3px;color:#1f8a7e}
table{width:100%;border-collapse:collapse;margin-bottom:2.5mm}th{background:#f0f0f0;border:1px solid #ccc;padding:1.6mm;text-align:left;font-size:8.3pt}td{border:1px solid #ddd;padding:1.6mm;font-size:8.3pt;vertical-align:top}
.polja td{border:0;border-bottom:1px solid #e5e5e5;padding:1mm 1mm}.polja td.k{color:#555;width:30%}.polja.cetiri td.k{width:19%}.polja.cetiri td{width:31%}.num{text-align:right}
.box{border:1px solid #ccc;padding:2mm;line-height:1.4;min-height:10mm}.tot td{font-weight:bold}.isplata td{font-weight:bold;font-size:10.5pt;border-top:1.5px solid #333}
.potpisi{margin-top:3mm;page-break-inside:avoid}.potpisi .c{display:table-cell;width:33%;text-align:center;font-size:8pt}.crta{border-top:1px solid #333;width:52mm;margin:9mm auto 1mm;padding-top:1mm}
.napomena{font-size:7.3pt;color:#666;margin-top:4mm;line-height:1.5}
</style>
</head>
<body><div class="page">
<div class="header">
    <div class="left">
        <div class="company">{{ $tvrtka->naziv }}</div>
        <p>{{ $tvrtka->adresa }}<br>{{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}<br>OIB: {{ $tvrtka->oib }}</p>
    </div>
    <div class="right">@if($tvrtka->logo)<img class="logo" src="{{ storage_path('app/public/'.$tvrtka->logo) }}">@endif</div>
</div>

<div class="title">PUTNI NALOG br. {{ $nalog->broj }}</div>
<div class="subtitle">{{ $nalog->vrsta === 'inozemni' ? 'Službeno putovanje u inozemstvo' : 'Službeno putovanje u tuzemstvu' }} · izdan {{ $tvrtka->mjesto ? $tvrtka->mjesto.', ' : '' }}{{ $nalog->datum_naloga->format('d.m.Y.') }}</div>
<hr>

<h3>Određujem da</h3>
<table class="polja">
    <tr><td class="k">Ime i prezime</td><td><strong>{{ $nalog->putnik }}</strong></td></tr>
    @if($nalog->radno_mjesto)<tr><td class="k">Radno mjesto / funkcija</td><td>{{ $nalog->radno_mjesto }}</td></tr>@endif
    <tr><td class="k">Otputuje dana</td><td>{{ $dt($nalog->polazak_at) }}</td></tr>
    <tr><td class="k">Odredište</td><td><strong>{{ $nalog->odrediste }}</strong>@if($nalog->vrsta === 'inozemni') — {{ $nalog->drzava }}@endif</td></tr>
    <tr><td class="k">Svrha putovanja</td><td>{{ $nalog->svrha }}</td></tr>
    <tr><td class="k">Prijevozno sredstvo</td><td>{{ $prijevoz }}@if($nalog->registracija), reg. oznaka <strong>{{ $nalog->registracija }}</strong>@endif @if($nalog->vozilo)({{ $nalog->vozilo }})@endif</td></tr>
    @if($nalog->radniNalog)<tr><td class="k">Radni nalog</td><td>{{ $nalog->radniNalog->broj }}</td></tr>@endif
    <tr><td class="k">Odobreni predujam</td><td>{{ $e($nalog->predujam) }} €</td></tr>
</table>
<div class="potpisi" style="margin-top:0"><div class="c"></div><div class="c"></div>
    <div class="c"><div class="crta" style="margin-top:6mm">Nalogodavac{{ $nalog->nalogodavac ? ': '.$nalog->nalogodavac : '' }}</div></div></div>

<h3>Obračun putnih troškova</h3>
<table class="polja cetiri">
    <tr><td class="k">Polazak</td><td>{{ $dt($nalog->polazak_at) }}</td><td class="k">Povratak</td><td>{{ $dt($nalog->povratak_at) }}</td></tr>
    @if($nalog->vrsta === 'inozemni')
        <tr><td class="k">Granica — odlazak</td><td>{{ $dt($nalog->granica_odlazak_at) }}</td><td class="k">Granica — povratak</td><td>{{ $dt($nalog->granica_povratak_at) }}</td></tr>
    @endif
    @if($nalog->jeAutomobil())
        <tr><td class="k">Brojilo na polasku</td><td>{{ $nalog->km_pocetno !== null ? number_format($nalog->km_pocetno, 0, ',', '.').' km' : '—' }}</td>
            <td class="k">Brojilo na povratku</td><td>{{ $nalog->km_zavrsno !== null ? number_format($nalog->km_zavrsno, 0, ',', '.').' km' : '—' }}</td></tr>
    @endif
</table>

<table>
    <thead><tr><th>Opis</th><th class="num" style="width:13%">Sati / km</th><th class="num" style="width:13%">Količina</th><th class="num" style="width:17%">Iznos</th><th class="num" style="width:15%">Ukupno €</th></tr></thead>
    <tbody>
    @foreach($o['dnevnice'] as $d)
        <tr><td>{{ $d['opis'] }}@if($d['valuta'] !== 'EUR')<br><span style="color:#666">{{ $e($d['u_valuti']) }} {{ $d['valuta'] }}, tečaj HNB {{ $o['tecaj'] ? number_format($o['tecaj'], 4, ',', '.') : '—' }}</span>@endif</td>
            <td class="num">{{ $e($d['sati']) }} h</td><td class="num">{{ rtrim(rtrim(number_format($d['broj'], 1, ',', '.'), '0'), ',') }}</td>
            <td class="num">{{ $e($d['jedinicni']) }} {{ $d['valuta'] }}</td><td class="num">{{ $e($d['ukupno']) }}</td></tr>
    @endforeach
    @if($nalog->jeAutomobil())
        <tr><td>Naknada za korištenje {{ $nalog->prijevoz === 'privatni_auto' ? 'privatnog automobila' : 'službenog automobila (bez naknade)' }}, {{ $nalog->registracija }}</td>
            <td class="num">{{ number_format($o['km'], 1, ',', '.') }} km</td><td></td>
            <td class="num">{{ $nalog->prijevoz === 'privatni_auto' ? $e($o['km_naknada']).' €/km' : '—' }}</td><td class="num">{{ $e($o['km_iznos']) }}</td></tr>
    @endif
    @foreach($nalog->troskovi as $t)
        <tr><td>{{ $vrsteTroska[$t->vrsta] ?? $t->vrsta }}{{ $t->opis ? ' — '.$t->opis : '' }}@if($t->privitak) <span style="color:#666">(račun u prilogu)</span>@endif</td>
            <td></td><td class="num">1</td><td class="num">{{ $e($t->iznos) }} €</td><td class="num">{{ $e($t->iznos) }}</td></tr>
    @endforeach
    <tr class="tot"><td colspan="4">Ukupno</td><td class="num">{{ $e($o['ukupno']) }}</td></tr>
    <tr><td colspan="4">Primljeni predujam</td><td class="num">{{ $e($o['predujam']) }}</td></tr>
    <tr class="isplata"><td colspan="4">{{ $o['za_isplatu'] < 0 ? 'Za povrat' : 'Za isplatu' }}</td><td class="num">{{ $e(abs($o['za_isplatu'])) }} €</td></tr>
    </tbody>
</table>
@if($o['obroci'] > 0)<p style="font-size:7.8pt;color:#555">Dnevnice umanjene za {{ $o['obroci'] * 30 }} % zbog osiguranih obroka.</p>@endif

<h3>Izvješće s puta</h3>
<div class="box">{!! nl2br(e($nalog->izvjesce)) !!}</div>

<div class="potpisi">
    <div class="c"><div class="crta">Podnositelj obračuna<br>{{ $nalog->putnik }}</div></div>
    <div class="c"><div class="crta">Obračun pregledao (likvidator)</div></div>
    <div class="c"><div class="crta">Odobrio za isplatu</div></div>
</div>
<p style="text-align:center;font-size:8pt;margin-top:2mm">{{ $tvrtka->mjesto ?: '__________' }}, {{ ($nalog->povratak_at ?? $nalog->datum_naloga)->format('d.m.Y.') }}</p>

<p class="napomena">Prilozi: {{ $nalog->troskovi->whereNotNull('privitak')->count() }} račun(a). Obračun prema neoporezivim iznosima Pravilnika o porezu na dohodak
(dnevnica u tuzemstvu {{ $e(config('putni.dnevnica_tuzemna')) }} €, naknada {{ $e(config('putni.km_naknada')) }} €/km)@if($nalog->vrsta === 'inozemni'), inozemne dnevnice prema Odluci Vlade RH (NN 16/2024)@endif.
Izrađeno u plačko.app.</p>
</div></body>
</html>
