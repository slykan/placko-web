<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
@php
    $e = fn ($v) => number_format((float) $v, 2, ',', '.');
    $ukupnoKm = (float) $voznje->sum('km');
    $naknada = (float) config('putni.km_naknada');
    $registracije = $voznje->pluck('registracija')->filter()->unique()->implode(', ');
@endphp
<style>
@page{margin:9mm 0 8mm}*{margin:0;padding:0;box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;font-size:8.5pt;color:#222}.page{padding:2mm 13mm 0}
.header,.potpisi{display:table;width:100%}.left,.right{display:table-cell;vertical-align:top;width:50%}.right{text-align:right}
.company{font-size:10.5pt;font-weight:bold}.title{text-align:center;font-size:14pt;font-weight:bold;margin:3mm 0 1mm}.subtitle{text-align:center;color:#555;margin-bottom:3mm}
table{width:100%;border-collapse:collapse}th{background:#f0f0f0;border:1px solid #ccc;padding:1.5mm;text-align:left;font-size:8pt}td{border:1px solid #ddd;padding:1.5mm;font-size:8pt;vertical-align:top}
.num{text-align:right}.tot td{font-weight:bold;border-top:1.5px solid #333}.potpisi{margin-top:8mm;page-break-inside:avoid}.potpisi .c{display:table-cell;width:33%;text-align:center;font-size:8pt}.crta{border-top:1px solid #333;width:55mm;margin:10mm auto 1mm;padding-top:1mm}
.napomena{font-size:7.3pt;color:#666;margin-top:3mm}
</style>
</head>
<body><div class="page">
<div class="header">
    <div class="left"><div class="company">{{ $tvrtka->naziv }}</div>{{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }} · OIB: {{ $tvrtka->oib }}</div>
    <div class="right">Vozač: <strong>{{ $vozac }}</strong><br>Reg. oznaka: <strong>{{ $registracije ?: '—' }}</strong></div>
</div>
<div class="title">EVIDENCIJA O KORIŠTENJU PRIVATNOG AUTOMOBILA U SLUŽBENE SVRHE (LOKO VOŽNJA)</div>
<div class="subtitle">za mjesec {{ $mjesec->format('m.Y.') }}</div>

<table>
    <thead><tr><th style="width:4%">RB</th><th style="width:10%">Datum</th><th style="width:6%">Vrijeme</th><th>Relacija</th><th style="width:28%">Svrha</th><th style="width:9%">Reg. oznaka</th><th class="num" style="width:7%">km</th><th class="num" style="width:9%">Iznos €</th></tr></thead>
    <tbody>
    @forelse($voznje as $i => $v)
        <tr><td>{{ $i + 1 }}</td><td>{{ $v->datum->format('d.m.Y.') }}</td><td>{{ $v->vrijeme ? substr($v->vrijeme, 0, 5) : '' }}</td>
            <td>{{ $v->relacija }}</td><td>{{ $v->svrha }}</td><td>{{ $v->registracija }}</td>
            <td class="num">{{ number_format((float) $v->km, 1, ',', '.') }}</td><td class="num">{{ $e($v->iznos()) }}</td></tr>
    @empty
        <tr><td colspan="8" style="text-align:center;color:#777">Nema vožnji u ovom mjesecu.</td></tr>
    @endforelse
    <tr class="tot"><td colspan="6">Ukupno ({{ $e($naknada) }} € po km)</td><td class="num">{{ number_format($ukupnoKm, 1, ',', '.') }}</td><td class="num">{{ $e(round($ukupnoKm * $naknada, 2)) }}</td></tr>
    </tbody>
</table>

<div class="potpisi">
    <div class="c"><div class="crta">Vozač<br>{{ $vozac }}</div></div>
    <div class="c"><div class="crta">Pregledao</div></div>
    <div class="c"><div class="crta">Odobrio za isplatu</div></div>
</div>
<p class="napomena">Naknada za korištenje privatnog automobila u službene svrhe do {{ $e($naknada) }} € po prijeđenom kilometru neoporeziva je prema Pravilniku o porezu na dohodak, uz vjerodostojnu evidenciju (datum, relacija, svrha, km, registracija). Izrađeno u plačko.app.</p>
</div></body>
</html>
