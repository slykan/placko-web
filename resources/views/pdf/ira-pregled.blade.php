<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:8pt; color:#222; }
    .page { padding:10mm 12mm; }
    h1 { font-size:13pt; font-weight:bold; margin-bottom:1mm; }
    .subtitle { font-size:8pt; color:#555; margin-bottom:5mm; }
    table { width:100%; border-collapse:collapse; }
    th { background:#f0f0f0; border:1px solid #ccc; padding:1.5mm 2mm; text-align:right; font-size:7.5pt; }
    th.left { text-align:left; }
    td { border:1px solid #e5e7eb; padding:1.5mm 2mm; font-size:7.5pt; vertical-align:top; }
    td.num { text-align:right; }
    tfoot td { font-weight:bold; border-top:2px solid #333; background:#f9fafb; }
    .footer { margin-top:5mm; font-size:7pt; color:#aaa; border-top:1px solid #eee; padding-top:1mm; }
</style>
</head>
<body>
<div class="page">
    <h1>Knjiga IRA — {{ $godina }}.</h1>
    <div class="subtitle">{{ $tvrtka->naziv }} &bull; OIB: {{ $tvrtka->oib }} &bull; {{ $tvrtka->adresa }}, {{ $tvrtka->po_broj }} {{ $tvrtka->mjesto }}</div>

    <table>
        <thead>
            <tr>
                <th class="left" style="width:4%">RB</th>
                <th class="left" style="width:12%">Broj</th>
                <th class="left" style="width:10%">Datum</th>
                <th class="left" style="width:22%">Klijent</th>
                <th style="width:13%">Osnovica (€)</th>
                <th style="width:10%">Rabat (€)</th>
                <th style="width:10%">PDV (€)</th>
                <th style="width:12%">Ukupno (€)</th>
                <th style="width:7%">Plaćeno</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($racuni as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->broj }}</td>
                <td>{{ $r->datum_izdavanja->format('d.m.Y.') }}</td>
                <td>{{ $r->klijent->naziv ?? '—' }}</td>
                <td class="num">{{ number_format((float)$r->ukupno_osnovica, 2, ',', '.') }}</td>
                <td class="num">{{ number_format((float)$r->ukupno_rabat, 2, ',', '.') }}</td>
                <td class="num">{{ number_format((float)$r->ukupno_pdv, 2, ',', '.') }}</td>
                <td class="num">{{ number_format((float)$r->ukupno, 2, ',', '.') }}</td>
                <td style="text-align:center">{{ $r->placen_at ? '✓' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">UKUPNO</td>
                <td class="num">{{ number_format($ukupnoOsnovica, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($ukupnoRabat, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($ukupnoPdv, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($ukupno, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Generirano iz MojObrt &bull; {{ now()->format('d.m.Y. H:i') }}</div>
</div>
</body>
</html>
