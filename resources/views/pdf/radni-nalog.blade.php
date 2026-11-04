<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
<style>
@page{margin:10mm 0 8mm}*{margin:0;padding:0;box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;font-size:9pt;color:#222}.page{padding:4mm 15mm 0}.header,.info,.summary,.potpisi{display:table;width:100%}.header{margin-bottom:5mm}.left,.right{display:table-cell;vertical-align:top;width:50%}.right{text-align:right}.company{font-size:11pt;font-weight:bold;margin-bottom:1mm}.logo{max-height:20mm;max-width:45mm}.title{text-align:center;font-size:17pt;font-weight:bold;margin:4mm 0 1mm;letter-spacing:1px}.subtitle{text-align:center;font-size:8.5pt;color:#555;margin-bottom:3mm}hr{border:0;border-top:1px solid #ccc;margin:3mm 0}.info{margin-bottom:3mm}.info .left{padding-right:5mm}.info .right{padding-left:5mm;text-align:left}h3{font-size:10pt;font-weight:bold;margin:3mm 0 1.5mm}.info p{line-height:1.4}.box{border:1px solid #ccc;padding:2.5mm;line-height:1.45;margin-bottom:3mm;min-height:12mm}.box.big{min-height:22mm}table{width:100%;border-collapse:collapse;margin-bottom:3mm}th{background:#f0f0f0;border:1px solid #ccc;padding:1.8mm;text-align:left;font-size:8.5pt}td{border:1px solid #ddd;padding:1.8mm;font-size:8.5pt;vertical-align:top}td.prazno{height:7mm}.num{text-align:right}.summary .left{width:55%}.summary-table{width:auto;border:0}.summary-table td{border:0;padding:1mm 3mm 1mm 0}.total td{font-weight:bold;font-size:11pt;border-top:1px solid #333;padding-top:2mm}.potpisi{margin-top:6mm;page-break-inside:avoid}.potpisi .left,.potpisi .right{text-align:center}.crta{border-top:1px solid #333;width:65mm;margin:10mm auto 1mm;font-size:8pt;padding-top:1mm}.footer-bottom{border-top:1px solid #ccc;padding-top:2mm;margin-top:6mm;font-size:7.5pt;color:#555;text-align:center;line-height:1.6}
</style>
</head>
<body><div class="page">
<div class="header">
    <div class="left">
        <div class="company">{{ $tvrtka->naziv }}</div>
        <p>{{ $tvrtka->adresa }}<br>{{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}<br>
        OIB: {{ $tvrtka->oib }}<br>
        @if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}<br>@endif
        @if($tvrtka->email)Email: {{ $tvrtka->email }}@endif</p>
    </div>
    <div class="right">@if($tvrtka->logo)<img class="logo" src="{{ storage_path('app/public/'.$tvrtka->logo) }}">@endif</div>
</div>
<div class="title">RADNI NALOG br.: {{ $nalog->broj }}</div>
<div class="subtitle">{{ $zaKlijenta ? 'Primjerak za klijenta' : 'Nalog za izvršitelja' }}</div><hr>

<div class="info">
    <div class="left"><h3>Klijent:</h3><p>
        @if($klijent)
            <strong>{{ $klijent->naziv }}</strong><br>
            @if($klijent->adresa){{ $klijent->adresa }}<br>@endif
            @if($klijent->po_broj || $klijent->mjesto){{ $klijent->po_broj }} {{ $klijent->mjesto }}<br>@endif
            @if($zaKlijenta && $klijent->oib)OIB: {{ $klijent->oib }}<br>@endif
            @if(! $zaKlijenta && $klijent->kontakt_broj)Tel: <strong>{{ $klijent->kontakt_broj }}</strong><br>@endif
            @if(! $zaKlijenta && $klijent->email)Email: {{ $klijent->email }}@endif
        @else
            Interni posao
        @endif
    </p></div>
    <div class="right"><h3>Podaci o nalogu:</h3><p>
        Datum otvaranja: {{ $nalog->datum_otvaranja->format('d.m.Y.') }}<br>
        @if($nalog->rok)Rok izvršenja: <strong>{{ $nalog->rok->format('d.m.Y.') }}</strong><br>@endif
        @if($nalog->zavrsen_at)Završen: {{ $nalog->zavrsen_at->format('d.m.Y.') }}<br>@endif
        @if($nalog->predmet)Predmet: <strong>{{ $nalog->predmet }}</strong><br>@endif
        @if($nalog->lokacija)Lokacija: {{ $nalog->lokacija }}<br>@endif
        @if($nalog->zaposlenici->isNotEmpty())
            Izvršitelji: {{ $nalog->zaposlenici->map(fn ($z) => $z->ime_prezime.($z->id === $nalog->nositelj_id ? ' (nositelj)' : ''))->implode(', ') }}
        @endif
    </p></div>
</div>

<h3>Opis posla</h3>
<div class="box">{!! nl2br(e($nalog->opis_posla)) !!}</div>

@if(! $zaKlijenta && $nalog->napomena)
<h3>Napomena</h3>
<div class="box">{!! nl2br(e($nalog->napomena)) !!}</div>
@endif

<h3>Izvršeni radovi</h3>
<div class="box {{ $nalog->izvrseni_radovi ? '' : 'big' }}">{!! nl2br(e($nalog->izvrseni_radovi)) !!}</div>

<h3>Evidencija rada</h3>
<table><thead><tr><th style="width:15%">Datum</th><th style="width:25%">Izvršitelj</th><th>Opis</th><th class="num" style="width:10%">Sati</th>
@if($zaKlijenta)<th class="num" style="width:13%">Cijena sata</th><th class="num" style="width:13%">Iznos</th>@endif</tr></thead>
<tbody>
@foreach($nalog->radovi as $r)
<tr><td>{{ $r->datum->format('d.m.Y.') }}</td><td>{{ $r->zaposlenik?->ime_prezime }}</td><td>{{ $r->opis }}</td>
<td class="num">{{ number_format((float)$r->sati,2,',','.') }}</td>
@if($zaKlijenta)<td class="num">{{ number_format((float)$r->cijena_sata,2,',','.') }} €</td><td class="num">{{ number_format((float)$r->sati*(float)$r->cijena_sata,2,',','.') }} €</td>@endif</tr>
@endforeach
@if(! $zaKlijenta)
@for($i = 0; $i < max(2, 4 - $nalog->radovi->count()); $i++)
<tr><td class="prazno"></td><td></td><td></td><td></td></tr>
@endfor
@endif
@if($zaKlijenta && $nalog->radovi->isNotEmpty())
<tr><td colspan="3"><strong>Ukupno rad</strong></td><td class="num"><strong>{{ number_format((float)$nalog->ukupno_sati,2,',','.') }}</strong></td><td></td><td class="num"><strong>{{ number_format((float)$nalog->ukupno_rad,2,',','.') }} €</strong></td></tr>
@endif
</tbody></table>

@if($nalog->stavke->isNotEmpty() || ! $zaKlijenta)
<h3>{{ $zaKlijenta ? 'Materijal i usluge' : 'Materijal' }}</h3>
<table><thead><tr><th style="width:5%">RB</th><th>Naziv</th><th class="num">JM</th><th class="num">Količina</th>
@if($zaKlijenta)<th class="num">Cijena</th><th class="num">Rabat</th><th class="num">PDV</th><th class="num">Iznos</th>@endif</tr></thead>
<tbody>
@foreach($nalog->stavke as $i => $stavka)
<tr><td>{{ $i+1 }}</td><td>{{ $stavka->naziv }}@if($stavka->opis)<br><small>{{ $stavka->opis }}</small>@endif</td>
<td class="num">{{ $stavka->jedinica_mjere ?? 'kom' }}</td><td class="num">{{ number_format((float)$stavka->kolicina,2,',','.') }}</td>
@if($zaKlijenta)<td class="num">{{ number_format((float)$stavka->cijena,2,',','.') }} €</td><td class="num">{{ number_format((float)$stavka->rabat_posto,2,',','.') }} %</td>
<td class="num">{{ number_format((float)($stavka->pdv_stopa??0),2,',','.') }} %</td><td class="num">{{ number_format((float)$stavka->ukupno,2,',','.') }} €</td>@endif</tr>
@endforeach
@if(! $zaKlijenta)
@for($i = 0; $i < max(1, 3 - $nalog->stavke->count()); $i++)
<tr><td class="prazno"></td><td></td><td></td><td></td></tr>
@endfor
@endif
</tbody></table>
@endif

@if($zaKlijenta)
<div class="summary"><div class="left"><table class="summary-table">
<tr><td>Rad ({{ number_format((float)$nalog->ukupno_sati,2,',','.') }} h, bez PDV-a):</td><td class="num">{{ number_format((float)$nalog->ukupno_rad,2,',','.') }} €</td></tr>
<tr><td>Materijal i usluge:</td><td class="num">{{ number_format((float)$nalog->ukupno_materijal,2,',','.') }} €</td></tr>
</table><p style="font-size:7.5pt;color:#555;margin-top:1mm">Radni nalog nije račun. Konačan iznos s PDV-om iskazuje se na računu{{ $nalog->racun ? ' br. '.$nalog->racun->broj : '' }}.</p></div><div class="right"></div></div>
@endif

<div class="potpisi">
    <div class="left"><div class="crta">{{ $zaKlijenta ? 'Radove izvršio' : 'Izvršitelj' }}</div></div>
    <div class="right"><div class="crta">{{ $zaKlijenta ? 'Radove preuzeo (klijent)' : 'Potpis klijenta' }}</div></div>
</div>

<div class="footer-bottom">{{ $tvrtka->naziv }}, {{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}
@if($tvrtka->oib) | OIB: {{ $tvrtka->oib }}@endif
@if($tvrtka->kontakt_broj) | Tel: {{ $tvrtka->kontakt_broj }}@endif @if($tvrtka->email) | Email: {{ $tvrtka->email }}@endif
</div></div></body></html>
