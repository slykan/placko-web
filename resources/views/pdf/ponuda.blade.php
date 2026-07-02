<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;font-size:9pt;color:#222}.page{padding:18mm 15mm 15mm}.header,.info,.summary,.footer-info{display:table;width:100%}.header{margin-bottom:8mm}.left,.right{display:table-cell;vertical-align:top;width:50%}.right{text-align:right}.company{font-size:11pt;font-weight:bold;margin-bottom:1mm}.logo{max-height:22mm;max-width:45mm}.title{text-align:center;font-size:18pt;font-weight:bold;margin:6mm 0;letter-spacing:1px}hr{border:0;border-top:1px solid #ccc;margin:4mm 0}.info{margin-bottom:6mm}.info .left{padding-right:5mm}.info .right{padding-left:5mm;text-align:left}.info h3,.stavke-title{font-size:10pt;font-weight:bold;margin-bottom:2mm}.info p{line-height:1.35}table{width:100%;border-collapse:collapse;margin-bottom:5mm}th{background:#f0f0f0;border:1px solid #ccc;padding:2mm;text-align:left;font-size:8.5pt}td{border:1px solid #ddd;padding:2mm;font-size:8.5pt;vertical-align:top}.num{text-align:right}.summary .left{width:55%}.summary-table{width:auto;border:0}.summary-table td{border:0;padding:1mm 3mm 1mm 0}.total td{font-weight:bold;font-size:11pt;border-top:1px solid #333;padding-top:2mm}.napomena{font-size:8.5pt;margin-bottom:5mm}.footer-info{font-size:8.5pt;margin-bottom:4mm}.footer-bottom{border-top:1px solid #ccc;padding-top:2mm;font-size:7.5pt;color:#555;text-align:center;line-height:1.6}
</style>
</head>
<body><div class="page">
<div class="header">
    <div class="left">
        <div class="company">{{ $tvrtka->naziv }}</div>
        <p>{{ $tvrtka->adresa }}<br>{{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}<br>
        OIB: {{ $tvrtka->oib }}<br>IBAN: {{ $tvrtka->iban }}<br>
        @if($tvrtka->swift)SWIFT: {{ $tvrtka->swift }}<br>@endif
        @if($tvrtka->banka)Banka: {{ $tvrtka->banka }}<br>@endif
        @if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}<br>@endif
        @if($tvrtka->email)Email: {{ $tvrtka->email }}@endif</p>
    </div>
    <div class="right">@if($tvrtka->logo)<img class="logo" src="{{ storage_path('app/public/'.$tvrtka->logo) }}">@endif</div>
</div>
<div class="title">PONUDA br.: {{ $ponuda->broj }}</div><hr>
<div class="info">
    <div class="left"><h3>Kome se izdaje ponuda:</h3><p><strong>{{ $klijent->naziv }}</strong><br>
        @if($klijent->adresa){{ $klijent->adresa }}<br>@endif
        @if($klijent->po_broj || $klijent->mjesto){{ $klijent->po_broj }} {{ $klijent->mjesto }}<br>@endif
        @if($klijent->oib)OIB: {{ $klijent->oib }}@endif</p>
    </div>
    <div class="right"><h3>Podaci o ponudi:</h3><p>
        Mjesto izdavanja: {{ $ponuda->mjesto_izdavanja }}<br>
        Datum izdavanja: {{ $ponuda->datum_izdavanja->format('d.m.Y.') }}<br>
        @if($ponuda->vrijeme_izdavanja)Vrijeme izdavanja: {{ \Carbon\Carbon::parse($ponuda->vrijeme_izdavanja)->format('H:i') }}<br>@endif
        Valjanost ponude: {{ $ponuda->valjanost_dana }} dana<br>
        Rok ispostave: {{ $ponuda->rok_ispostave }}
    </p></div>
</div><hr>
<div class="stavke-title">Stavke ponude</div>
<table><thead><tr><th style="width:5%">RB</th><th style="width:35%">Opis</th><th class="num">JM</th><th class="num">Količina</th><th class="num">Cijena</th><th class="num">Rabat</th><th class="num">PDV</th><th class="num">Iznos</th></tr></thead>
<tbody>@foreach($ponuda->stavke as $i => $stavka)
@php
$bruto=(float)$stavka->cijena*(float)$stavka->kolicina;
$neto=$bruto*(1-(float)($stavka->rabat_posto??0)/100);
$iznos=$neto*(1+(float)($stavka->pdv_stopa??0)/100);
@endphp
<tr><td>{{ $i+1 }}</td><td>{{ $stavka->naziv }}@if($stavka->opis)<br><small>{{ $stavka->opis }}</small>@endif</td>
<td class="num">{{ $stavka->jedinica_mjere ?? 'kom' }}</td><td class="num">{{ number_format((float)$stavka->kolicina,2,',','.') }}</td>
<td class="num">{{ number_format((float)$stavka->cijena,2,',','.') }} €</td><td class="num">{{ number_format((float)$stavka->rabat_posto,2,',','.') }} %</td>
<td class="num">{{ number_format((float)($stavka->pdv_stopa??0),2,',','.') }} %</td><td class="num">{{ number_format($iznos,2,',','.') }} €</td></tr>
@endforeach</tbody></table>
<div class="summary"><div class="left"><table class="summary-table">
<tr><td>Osnovica:</td><td class="num">{{ number_format((float)$ponuda->ukupno_osnovica,2,',','.') }} €</td></tr>
<tr><td>Rabat:</td><td class="num">{{ number_format((float)$ponuda->ukupno_rabat,2,',','.') }} €</td></tr>
<tr><td>PDV:</td><td class="num">{{ number_format((float)$ponuda->ukupno_pdv,2,',','.') }} €</td></tr>
<tr class="total"><td>Ukupno:</td><td class="num">{{ number_format((float)$ponuda->ukupno,2,',','.') }} €</td></tr>
</table></div><div class="right"></div></div>
@if($ponuda->napomena)<div class="napomena"><strong>Napomena:</strong> {{ $ponuda->napomena }}</div>@endif
<hr><div class="footer-info"><div class="left">Ponudu sastavio: {{ $tvrtka->vlasnik }}</div></div>
<div class="footer-bottom">{{ $tvrtka->naziv }}, {{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}
@if($tvrtka->oib) | OIB: {{ $tvrtka->oib }}@endif @if($tvrtka->iban) | IBAN: {{ $tvrtka->iban }}@endif<br>
@if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}@endif @if($tvrtka->email) | Email: {{ $tvrtka->email }}@endif
</div></div></body></html>
