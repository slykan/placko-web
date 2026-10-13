@extends('layouts.blog', [
    'title' => 'Kalkulator paušalnog obrta 2026. — porez, doprinosi i razred',
    'description' => 'Besplatni kalkulator paušalnog obrta za 2026.: upiši godišnji promet i vidi porezni razred, paušalni porez, doprinose (290,98 € mjesečno) i koliko ti ostaje.',
    'nav' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="/alati" class="article-back">← Svi alati</a>
    <span class="page-eyebrow">Besplatni alat</span>
    <h1 class="page-title">Kalkulator paušalnog obrta 2026.</h1>
    <p class="page-sub">Upiši očekivani ili ostvareni godišnji promet i odmah vidi razred, porez, doprinose i koliko ti ostaje.</p>

    <div class="tool">
        <div class="row">
            <div>
                <label for="primici">Ukupni primici (promet) u godini, €</label>
                <input id="primici" type="number" min="0" step="100" value="20000" inputmode="decimal">
                <div class="hint">Sve što je naplaćeno u godini, gotovinom i na račun.</div>
            </div>
            <div>
                <label for="troskovi">Tvoji stvarni troškovi u godini, € (neobavezno)</label>
                <input id="troskovi" type="number" min="0" step="100" value="0" inputmode="decimal">
                <div class="hint">Materijal, oprema, najam… — samo za izračun „ostaje ti".</div>
            </div>
            <div>
                <label for="status">Obrt mi je</label>
                <select id="status">
                    <option value="jedina">jedina djelatnost</option>
                    <option value="dopunska">uz posao ili mirovinu</option>
                </select>
            </div>
        </div>

        <div class="result" id="rez"></div>

        <div class="table-wrap" style="margin-top:20px;">
            <table>
                <thead><tr><th>Razred</th><th>Primici (€)</th><th class="num">Porez god. (€)</th><th class="num">Porez tromj. (€)</th></tr></thead>
                <tbody id="tab"></tbody>
            </table>
        </div>
        <p class="hint">Iznosi za 2026. Porez = 12 % paušalnog dohotka razreda. Doprinosi = 290,98 € mjesečno (osnovica 797,20 €).</p>
    </div>

    <div class="tool-text">
        <h2>Kako kalkulator računa</h2>
        <p>
            Paušalni porez ne ovisi o stvarnoj dobiti nego o <strong>razredu</strong> u koji upadaš prema ukupnim
            primicima. Svakom razredu pripada fiksni godišnji paušalni dohodak, a porez je 12 % tog dohotka.
            Doprinosi su za paušaliste kojima je obrt jedina djelatnost fiksni: <strong>290,98 € mjesečno</strong> u 2026.
            (MIO I. stup 119,58 €, MIO II. stup 39,86 €, zdravstveno 131,54 €).
        </p>
        <p>
            „Ostaje ti" = primici − porez − doprinosi − tvoji troškovi. To je okvirni iznos prije eventualnih
            ostalih davanja (npr. komorski doprinos, turistička članarina).
        </p>
        <h2>Na što paziti</h2>
        <ul>
            <li>Granica paušalnog oporezivanja i ulaska u PDV je <strong>60.000 €</strong> primitaka godišnje.</li>
            <li>Porez se plaća tromjesečno (do 31.3., 30.6., 30.9. i 31.12.), doprinosi do 15. u mjesecu za prethodni mjesec.</li>
            <li>Za 2027. najavljene su promjene za 6. i 7. razred — vidi
                <a href="{{ route('novosti.show', 'pausalni-obrt-2026-porez-doprinosi-rokovi') }}" style="color:var(--p);font-weight:700;">vodič za paušalni obrt 2026.</a></li>
        </ul>
        <p class="sources">Izračun je informativan. Za konačne obveze mjerodavna su rješenja Porezne uprave.</p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    // [gornja granica primitaka, godišnji paušalni dohodak]
    var RAZREDI = [[11300, 1695], [15300, 2286], [19900, 2985], [30600, 4590], [40000, 6000], [50000, 7500], [60000, 9000]];
    var DOPRINOSI_MJ = 290.98, STOPA = 0.12;
    var f = new Intl.NumberFormat('hr-HR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var e = function (n) { return f.format(n) + ' €'; };
    var $ = function (id) { return document.getElementById(id); };

    function izracun() {
        var p = Math.max(0, parseFloat($('primici').value) || 0);
        var t = Math.max(0, parseFloat($('troskovi').value) || 0);
        var dop = $('status').value === 'jedina' ? DOPRINOSI_MJ * 12 : 0;
        var idx = RAZREDI.findIndex(function (r) { return p <= r[0]; });
        var rows = '';
        RAZREDI.forEach(function (r, i) {
            var od = i === 0 ? 0 : RAZREDI[i - 1][0] + 0.01;
            var porez = r[1] * STOPA;
            rows += '<tr' + (i === idx ? ' style="background:var(--pl);font-weight:700"' : '') + '><td>' + (i + 1) + '.</td><td>' +
                (i === 0 ? 'do ' : f.format(od) + ' – ') + f.format(r[0]) + '</td><td class="num">' + f.format(porez) + '</td><td class="num">' + f.format(porez / 4) + '</td></tr>';
        });
        $('tab').innerHTML = rows;

        if (idx === -1) {
            $('rez').innerHTML = '<div class="lbl">Primici iznad 60.000 €</div><div class="big">Nije paušalni obrt</div>' +
                '<p style="margin-top:8px;color:var(--pd)">Iznad 60.000 € primitaka obrtnik izlazi iz paušalnog oporezivanja i ulazi u sustav PDV-a.</p>';
            return;
        }
        var porez = RAZREDI[idx][1] * STOPA;
        var ukupno = porez + dop;
        var ostaje = p - ukupno - t;
        $('rez').innerHTML =
            '<div class="lbl">' + (idx + 1) + '. razred · ostaje ti godišnje</div><div class="big">' + e(ostaje) + '</div>' +
            '<div class="lbl" style="margin-bottom:14px">≈ ' + e(ostaje / 12) + ' mjesečno</div>' +
            '<div class="stats">' +
            '<div><div class="lbl">Porez godišnje</div><strong>' + e(porez) + '</strong><div class="hint">' + e(porez / 4) + ' tromjesečno</div></div>' +
            '<div><div class="lbl">Doprinosi godišnje</div><strong>' + e(dop) + '</strong><div class="hint">' + (dop ? e(DOPRINOSI_MJ) + ' mjesečno' : 'ne računaju se') + '</div></div>' +
            '<div><div class="lbl">Ukupno državi</div><strong>' + e(ukupno) + '</strong><div class="hint">' + (p ? f.format(ukupno / p * 100) + ' % prometa' : '') + '</div></div>' +
            '</div>';
    }
    ['primici', 'troskovi', 'status'].forEach(function (id) { $(id).addEventListener('input', izracun); });
    izracun();
})();
</script>
@endpush
