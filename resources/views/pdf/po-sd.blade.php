<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9pt; color:#222; }
    .page { padding:15mm; }
    h1 { font-size:14pt; font-weight:bold; margin-bottom:1mm; }
    h2 { font-size:10pt; font-weight:bold; margin:5mm 0 2mm; border-bottom:1px solid #ccc; padding-bottom:1mm; }
    .subtitle { font-size:9pt; color:#555; margin-bottom:6mm; }
    table { width:100%; border-collapse:collapse; margin-bottom:4mm; }
    td, th { padding:2mm 3mm; font-size:8.5pt; }
    .obracun td:first-child { color:#555; width:65%; }
    .obracun td:last-child { text-align:right; font-weight:bold; }
    .obracun tr.total td { border-top:2px solid #333; font-size:10pt; color:#1d4ed8; }
    .obracun tr.minus td:last-child { color:#dc2626; }
    th { background:#f0f0f0; border:1px solid #ccc; text-align:right; }
    th:first-child, th:nth-child(2), th:nth-child(3) { text-align:left; }
    .mj td { border:1px solid #e5e7eb; }
    .mj td.num { text-align:right; }
    .mj tfoot td { font-weight:bold; border-top:2px solid #333; }
    .footer { margin-top:8mm; font-size:7.5pt; color:#888; border-top:1px solid #ddd; padding-top:2mm; }
    .note { font-size:7.5pt; color:#888; margin-top:3mm; }
    .header-box { display:table; width:100%; margin-bottom:6mm; }
    .header-left { display:table-cell; vertical-align:top; }
    .header-right { display:table-cell; text-align:right; vertical-align:top; font-size:8pt; color:#555; }
</style>
</head>
<body>
<div class="page">
    <div class="header-box">
        <div class="header-left">
            <h1>OBRAZAC PO-SD</h1>
            <div class="subtitle">Godišnja prijava paušalnog dohotka od samostalne djelatnosti — {{ $godina }}. godina</div>
        </div>
        <div class="header-right">
            {{ $tvrtka->naziv }}<br>
            OIB: {{ $tvrtka->oib }}<br>
            {{ $tvrtka->vlasnik }}<br>
            {{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}
        </div>
    </div>

    <h2>Obračun poreza na dohodak</h2>
    <table class="obracun">
        <tr>
            <td>I. Ukupni primitci</td>
            <td>{{ number_format($ukupniPrimitci, 2, ',', '.') }} €</td>
        </tr>
        <tr class="minus">
            <td>II. Paušalni izdatci (30%, max 12.750 €)</td>
            <td>− {{ number_format($pausalni_izdatci, 2, ',', '.') }} €</td>
        </tr>
        <tr>
            <td>III. Dohodak (I − II)</td>
            <td>{{ number_format($dohodak, 2, ',', '.') }} €</td>
        </tr>
        <tr class="minus">
            <td>IV. Osobni odbitak (godišnji 6.720 €)</td>
            <td>− {{ number_format(6720, 2, ',', '.') }} €</td>
        </tr>
        <tr>
            <td>V. Porezna osnovica (III − IV)</td>
            <td>{{ number_format($poreznaOsnovica, 2, ',', '.') }} €</td>
        </tr>
        <tr class="total">
            <td>VI. Porez na dohodak (20% / 30%)</td>
            <td>{{ number_format($porezNaDohodak, 2, ',', '.') }} €</td>
        </tr>
    </table>

    <p class="note">* Prirez se obračunava prema stopi Vaše općine/grada. * Stope: 20% do 50.400 € godišnje, 30% iznad toga.</p>

    <h2>Primitci po mjesecu</h2>
    @php
        $mjeseci = ['','Siječanj','Veljača','Ožujak','Travanj','Svibanj','Lipanj',
                    'Srpanj','Kolovoz','Rujan','Listopad','Studeni','Prosinac'];
    @endphp
    <table class="mj">
        <thead>
            <tr>
                <th style="text-align:left; width:40%">Mjesec</th>
                <th>Broj računa</th>
                <th>Iznos (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($racuniPoMjesecu as $m => $p)
            <tr>
                <td>{{ $mjeseci[$m] }}</td>
                <td class="num">{{ $p['broj'] }}</td>
                <td class="num">{{ number_format($p['iznos'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>UKUPNO</td>
                <td class="num">{{ $brojRacuna }}</td>
                <td class="num">{{ number_format($ukupniPrimitci, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generirano iz MojObrt &bull; {{ now()->format('d.m.Y. H:i') }} &bull;
        Ovaj dokument je informativnog karaktera — za predaju koristite ePorezna sustav (https://eporezna.porezna-uprava.hr)
    </div>
</div>
</body>
</html>
