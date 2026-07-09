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
        OIB: {{ $tvrtka->oib }}<br>
        @if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}<br>@endif
        @if($tvrtka->email)Email: {{ $tvrtka->email }}@endif</p>
    </div>
    <div class="right">@if($tvrtka->logo)<img class="logo" src="{{ storage_path('app/public/'.$tvrtka->logo) }}">@endif</div>
</div>
<div class="title">PRIMKA br.: {{ $primka->broj }}</div><hr>
<div class="info">
    <div class="left"><h3>Dobavljač:</h3><p>
        @if($dobavljac)<strong>{{ $dobavljac->naziv }}</strong><br>
        @if($dobavljac->adresa){{ $dobavljac->adresa }}<br>@endif
        @if($dobavljac->mjesto){{ $dobavljac->mjesto }}<br>@endif
        @if($dobavljac->oib)OIB: {{ $dobavljac->oib }}@endif
        @else<em>Bez dobavljača</em>@endif</p>
    </div>
    <div class="right"><h3>Podaci o primci:</h3><p>
        Skladište: {{ $primka->skladiste->naziv }}<br>
        Datum primke: {{ $primka->datum_primke->format('d.m.Y.') }}
    </p></div>
</div><hr>
<div class="stavke-title">Stavke primke</div>
<table><thead><tr><th style="width:8%">RB</th><th style="width:45%">Proizvod</th><th class="num">JM</th><th class="num">Količina</th><th class="num">Nabavna cijena</th><th class="num">Iznos</th></tr></thead>
<tbody>@foreach($primka->stavke as $i => $stavka)
<tr><td>{{ $i+1 }}</td><td>{{ $stavka->usluga->naziv ?? '—' }}</td>
<td class="num">{{ $stavka->usluga->jedinica_mjere ?? 'kom' }}</td><td class="num">{{ number_format((float)$stavka->kolicina,2,',','.') }}</td>
<td class="num">{{ number_format((float)$stavka->nabavna_cijena,2,',','.') }} €</td><td class="num">{{ number_format((float)$stavka->ukupno,2,',','.') }} €</td></tr>
@endforeach</tbody></table>
<div class="summary"><div class="left"><table class="summary-table">
<tr class="total"><td>Ukupno:</td><td class="num">{{ number_format((float)$primka->ukupno,2,',','.') }} €</td></tr>
</table></div><div class="right"></div></div>
@if($primka->napomena)<div class="napomena"><strong>Napomena:</strong> {{ $primka->napomena }}</div>@endif
<hr><div class="footer-bottom">{{ $tvrtka->naziv }}, {{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}
@if($tvrtka->oib) | OIB: {{ $tvrtka->oib }}@endif<br>
@if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}@endif @if($tvrtka->email) | Email: {{ $tvrtka->email }}@endif
</div></div></body></html>
