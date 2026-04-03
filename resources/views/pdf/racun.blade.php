<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #222; }

    .page { padding: 18mm 15mm 15mm 15mm; }

    /* HEADER */
    .header { display: table; width: 100%; margin-bottom: 8mm; }
    .header-left { display: table-cell; vertical-align: top; width: 65%; }
    .header-right { display: table-cell; vertical-align: top; text-align: right; }
    .header-left .company-name { font-size: 11pt; font-weight: bold; margin-bottom: 2mm; }
    .header-left p { line-height: 1.5; }
    .logo { max-height: 22mm; max-width: 45mm; }

    /* TITLE */
    .title { text-align: center; font-size: 18pt; font-weight: bold; margin: 6mm 0; letter-spacing: 1px; }
    hr { border: none; border-top: 1px solid #ccc; margin: 4mm 0; }

    /* INFO BLOCK */
    .info-block { display: table; width: 100%; margin-bottom: 6mm; }
    .info-left { display: table-cell; width: 50%; vertical-align: top; padding-right: 5mm; }
    .info-right { display: table-cell; width: 50%; vertical-align: top; padding-left: 5mm; }
    .info-block h3 { font-size: 10pt; font-weight: bold; margin-bottom: 2mm; }
    .info-block p { line-height: 1.6; }

    /* TABLE */
    .stavke-title { font-size: 10pt; font-weight: bold; margin-bottom: 2mm; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
    table th { background: #f0f0f0; border: 1px solid #ccc; padding: 2mm 2mm; text-align: left; font-size: 8.5pt; }
    table td { border: 1px solid #ddd; padding: 2mm 2mm; font-size: 8.5pt; vertical-align: top; }
    table td.num { text-align: right; }
    table th.num { text-align: right; }

    /* SUMMARY + BARCODE */
    .summary-block { display: table; width: 100%; margin-bottom: 5mm; }
    .summary-left { display: table-cell; width: 55%; vertical-align: top; }
    .summary-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }
    .summary-line { display: table; width: 50%; margin-bottom: 1mm; }
    .summary-label { display: table-cell; width: 50%; }
    .summary-value { display: table-cell; text-align: right; }
    .summary-total { font-weight: bold; font-size: 11pt; border-top: 1px solid #333; padding-top: 1mm; margin-top: 1mm; }
    .barcode img { max-width: 55mm; }

    /* NAPOMENA */
    .napomena { font-size: 8.5pt; margin-bottom: 5mm; }
    .napomena strong { font-weight: bold; }

    /* FOOTER TOP */
    .footer-info { font-size: 8.5pt; line-height: 1.7; margin-bottom: 5mm; }

    /* FOOTER BOTTOM */
    .footer-bottom { border-top: 1px solid #ccc; padding-top: 2mm; font-size: 7.5pt; color: #555; text-align: center; line-height: 1.6; }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $tvrtka->naziv }}</div>
            <div style="display:table; width:100%;">
                <div style="display:table-cell; width:50%; vertical-align:top; padding-right:3mm;">
                    <p>
                        {{ $tvrtka->adresa }}<br>
                        {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}<br>
                        OIB: {{ $tvrtka->oib }}<br>
                        IBAN: {{ $tvrtka->iban }}<br>
                    </p>
                </div>
                <div style="display:table-cell; width:50%; vertical-align:top;">
                    <p>
                        @if($tvrtka->swift)SWIFT: {{ $tvrtka->swift }}<br>@endif
                        @if($tvrtka->banka)Banka: {{ $tvrtka->banka }}<br>@endif
                        @if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}<br>@endif
                        @if($tvrtka->email)Email: {{ $tvrtka->email }}<br>@endif
                        @if($tvrtka->web_mjesto)Web: {{ $tvrtka->web_mjesto }}@endif
                    </p>
                </div>
            </div>
        </div>
        <div class="header-right">
            @if($tvrtka->logo)
                <img class="logo" src="{{ storage_path('app/public/' . $tvrtka->logo) }}">
            @endif
        </div>
    </div>

    <div class="title">RAČUN br.: {{ $racun->broj }}</div>
    <hr>

    {{-- INFO --}}
    <div class="info-block">
        <div class="info-left">
            <h3>Kome se izdaje račun:</h3>
            <p>
                <strong>{{ $klijent->naziv }}</strong><br>
                @if($klijent->adresa){{ $klijent->adresa }}<br>@endif
                @if($klijent->po_broj || $klijent->mjesto){{ $klijent->po_broj }} {{ $klijent->mjesto }}<br>@endif
                @if($klijent->oib)OIB: {{ $klijent->oib }}@endif
            </p>
        </div>
        <div class="info-right">
            <h3>Podaci o računu:</h3>
            <p>
                Mjesto izdavanja: {{ $racun->mjesto_izdavanja }}<br>
                Datum izdavanja: {{ $racun->datum_izdavanja->format('d.m.Y.') }}<br>
                @if($racun->vrijeme_izdavanja)Vrijeme izdavanja: {{ \Carbon\Carbon::parse($racun->vrijeme_izdavanja)->format('H:i') }}<br>@endif
                @if($racun->datum_dospijeca)Datum dospijeća: {{ $racun->datum_dospijeca->format('d.m.Y.') }}<br>@endif
                @if($racun->datum_isporuke)Datum isporuke: {{ $racun->datum_isporuke->format('d.m.Y.') }}@endif
            </p>
        </div>
    </div>
    <hr>

    {{-- STAVKE --}}
    <div class="stavke-title">Stavke računa</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">RB</th>
                <th style="width:35%">Opis</th>
                <th style="width:7%" class="num">JM</th>
                <th style="width:9%" class="num">Količina</th>
                <th style="width:13%" class="num">Cijena</th>
                <th style="width:10%" class="num">Rabat</th>
                <th style="width:8%" class="num">PDV</th>
                <th style="width:13%" class="num">Iznos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($racun->stavke as $i => $stavka)
            @php
                $bruto      = (float)$stavka->cijena * (float)$stavka->kolicina;
                $rabatIznos = $bruto * ((float)($stavka->rabat_posto ?? 0) / 100);
                $neto       = $bruto - $rabatIznos;
                $pdvIznos   = $neto * ((float)($stavka->pdv_stopa ?? 0) / 100);
                $iznos      = $neto + $pdvIznos;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $stavka->naziv }}@if($stavka->opis)<br><small>{{ $stavka->opis }}</small>@endif</td>
                <td class="num">{{ $stavka->jedinica_mjere ?? 'kom' }}</td>
                <td class="num">{{ number_format((float)$stavka->kolicina, 2, ',', '.') }}</td>
                <td class="num">{{ number_format((float)$stavka->cijena, 2, ',', '.') }} €</td>
                <td class="num">{{ number_format((float)($stavka->rabat_posto ?? 0), 2, ',', '.') }} %</td>
                <td class="num">{{ $stavka->pdv_stopa !== null ? number_format((float)$stavka->pdv_stopa, 2, ',', '.') . ' %' : '0,00 %' }}</td>
                <td class="num">{{ number_format($iznos, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SUMMARY + BARCODE --}}
    <div class="summary-block">
        <div class="summary-left">
            <table style="width:auto; border:none;">
                <tr>
                    <td style="border:none; padding: 1mm 3mm 1mm 0; width: 80px;">Osnovica:</td>
                    <td style="border:none; padding: 1mm 0; text-align:right;">{{ number_format((float)$racun->ukupno_osnovica, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td style="border:none; padding: 1mm 3mm 1mm 0;">Rabat:</td>
                    <td style="border:none; padding: 1mm 0; text-align:right;">{{ number_format((float)$racun->ukupno_rabat, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td style="border:none; padding: 1mm 3mm 1mm 0;">PDV:</td>
                    <td style="border:none; padding: 1mm 0; text-align:right;">{{ number_format((float)$racun->ukupno_pdv, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td style="border:none; padding: 2mm 3mm 1mm 0; font-weight:bold; font-size:11pt; border-top: 1px solid #333;"><strong>Ukupno:</strong></td>
                    <td style="border:none; padding: 2mm 0; text-align:right; font-weight:bold; font-size:11pt; border-top: 1px solid #333;"><strong>{{ number_format((float)$racun->ukupno, 2, ',', '.') }} €</strong></td>
                </tr>
            </table>
        </div>
        <div class="summary-right">
            @if($barkodBase64)
                <img src="{{ $barkodBase64 }}" style="max-width:55mm; max-height:22mm;">
            @endif
        </div>
    </div>

    {{-- NAPOMENA --}}
    @if($racun->napomena)
    <div class="napomena">
        <strong>Napomena:</strong> {{ $racun->napomena }}
    </div>
    @endif

    <hr>

    {{-- FOOTER INFO + FISKALIZACIJA --}}
    <div style="display:table; width:100%; margin-bottom:4mm; font-size:8.5pt;">
        <div style="display:table-cell; width:50%; vertical-align:top; padding-right:4mm;">
            Račun ispostavio: {{ $tvrtka->vlasnik }}<br>
            Oznaka operatera: {{ $tvrtka->oznaka_operatera }}<br>
            Način plaćanja: {{ $racun->nacin_placanja }}<br>
            Poziv na broj: {{ $racun->broj }}
        </div>
        <div style="display:table-cell; width:50%; vertical-align:top;">
            @if($racun->zki || $racun->jir)
            <div style="padding:2mm 3mm; border:1px solid #ccc; background:#f9f9f9; font-size:7.5pt;">
                <strong>Fiskalizacija:</strong><br>
                @if($racun->zki)ZKI: {{ $racun->zki }}<br>@endif
                @if($racun->jir)JIR: {{ $racun->jir }}@endif
            </div>
            @endif
        </div>
    </div>

    {{-- FOOTER BOTTOM --}}
    <div class="footer-bottom">
        {{ $tvrtka->naziv }}, {{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}
        @if($tvrtka->oib) | OIB: {{ $tvrtka->oib }}@endif
        @if($tvrtka->iban) | IBAN: {{ $tvrtka->iban }}@endif
        @if($tvrtka->swift) | SWIFT: {{ $tvrtka->swift }}@endif
        @if($tvrtka->banka) | {{ $tvrtka->banka }}@endif
        <br>
        @if($tvrtka->kontakt_broj)Tel: {{ $tvrtka->kontakt_broj }}@endif
        @if($tvrtka->email) | Email: {{ $tvrtka->email }}@endif
        @if($tvrtka->web_mjesto) | Web: {{ $tvrtka->web_mjesto }}@endif
    </div>

</div>
</body>
</html>
