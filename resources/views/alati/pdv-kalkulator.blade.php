@extends('layouts.blog', [
    'title' => 'PDV kalkulator — iznos s PDV-om i bez PDV-a',
    'description' => 'Besplatni PDV kalkulator: izračunaj osnovicu, PDV i ukupni iznos po stopama 25 %, 13 % i 5 %, iz iznosa bez PDV-a ili s PDV-om.',
    'nav' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="/alati" class="article-back">← Svi alati</a>
    <span class="page-eyebrow">Besplatni alat</span>
    <h1 class="page-title">PDV kalkulator</h1>
    <p class="page-sub">Dodaj PDV na iznos ili ga izdvoji iz ukupnog iznosa — po hrvatskim stopama od 25 %, 13 % i 5 %.</p>

    <div class="tool">
        <div class="row">
            <div>
                <label for="iznos">Iznos, €</label>
                <input id="iznos" type="number" step="0.01" value="100" inputmode="decimal">
            </div>
            <div>
                <label for="smjer">Upisani iznos je</label>
                <select id="smjer">
                    <option value="neto">bez PDV-a (osnovica) — dodaj PDV</option>
                    <option value="bruto">s PDV-om — izdvoji PDV</option>
                </select>
            </div>
            <div>
                <label for="stopa">Stopa PDV-a, %</label>
                <select id="stopa">
                    <option value="25">25 % — opća stopa</option>
                    <option value="13">13 % — snižena stopa</option>
                    <option value="5">5 % — snižena stopa</option>
                    <option value="0">0 %</option>
                </select>
            </div>
        </div>
        <div class="result">
            <div class="stats">
                <div><div class="lbl">Osnovica (bez PDV-a)</div><div class="big" id="o" style="font-size:1.5rem"></div></div>
                <div><div class="lbl">PDV</div><div class="big" id="p" style="font-size:1.5rem"></div></div>
                <div><div class="lbl">Ukupno s PDV-om</div><div class="big" id="u" style="font-size:1.5rem"></div></div>
            </div>
        </div>
    </div>

    <div class="tool-text">
        <h2>Formule</h2>
        <ul>
            <li>Iz iznosa bez PDV-a: <strong>PDV = osnovica × stopa</strong>, ukupno = osnovica + PDV.</li>
            <li>Iz iznosa s PDV-om: <strong>osnovica = ukupno ÷ (1 + stopa)</strong>, PDV = ukupno − osnovica. Npr. kod 25 %: 125 € ÷ 1,25 = 100 € osnovice i 25 € PDV-a.</li>
        </ul>
        <p>Česta greška je izdvajanje PDV-a množenjem (125 € × 25 % = 31,25 €) — to nije točno; točan PDV u 125 € je 25 €.</p>
        <h2>Stope PDV-a u Hrvatskoj</h2>
        <p>
            Opća stopa PDV-a je <strong>25 %</strong>, a snižene su <strong>13 %</strong> i <strong>5 %</strong> — primjenjuju se na
            određene proizvode i usluge propisane Zakonom o PDV-u. Koja stopa vrijedi za tvoju robu ili uslugu
            provjeri u zakonu ili s knjigovođom.
        </p>
        <p>
            Okvirno: sniženu stopu od 13 % imaju npr. usluge smještaja i dio ugostiteljskih usluga, a 5 %
            dio osnovnih namirnica, knjige i lijekovi. Popisi se povremeno mijenjaju izmjenama zakona, pa za
            konkretni artikl uvijek provjeri aktualni tekst Zakona o PDV-u.
        </p>

        <h2>Primjeri izračuna</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Zadano</th><th>Stopa</th><th class="num">Osnovica</th><th class="num">PDV</th><th class="num">Ukupno</th></tr></thead>
                <tbody>
                    <tr><td>800 € bez PDV-a</td><td>25 %</td><td class="num">800,00</td><td class="num">200,00</td><td class="num">1.000,00</td></tr>
                    <tr><td>1.000 € s PDV-om</td><td>25 %</td><td class="num">800,00</td><td class="num">200,00</td><td class="num">1.000,00</td></tr>
                    <tr><td>113 € s PDV-om</td><td>13 %</td><td class="num">100,00</td><td class="num">13,00</td><td class="num">113,00</td></tr>
                    <tr><td>50 € s PDV-om</td><td>5 %</td><td class="num">47,62</td><td class="num">2,38</td><td class="num">50,00</td></tr>
                </tbody>
            </table>
        </div>
        <p>
            U zadnjem primjeru osnovica je 50 ÷ 1,05 = 47,619… i zaokružuje se na 47,62 €, a PDV je razlika do
            ukupnog iznosa (2,38 €). Tako zbroj osnovice i PDV-a uvijek odgovara iznosu koji kupac plaća.
        </p>

        <h2>Zaokruživanje i računi s više stavki</h2>
        <p>
            Iznosi se zaokružuju na dvije decimale (cente). Kod računa s puno stavki redoslijed zaokruživanja
            može dati razliku od centa ili dva: ako PDV računaš po svakoj stavci pa zbrojiš, rezultat se može
            neznatno razlikovati od PDV-a izračunatog na ukupnu osnovicu. Važno je da na računu PDV bude
            iskazan zbirno po svakoj stopi i da se iznosi slažu. Programi za račune to rade automatski.
        </p>

        <h2>Česta pitanja</h2>
        <h3>Koliko je PDV-a u cijeni od 100 €?</h3>
        <p>
            Uz stopu od 25 % u 100 € s PDV-om je 20 € PDV-a (100 ÷ 1,25 = 80 € osnovice). Udio PDV-a u
            bruto cijeni je dakle 20 %, a ne 25 % — stopa se uvijek primjenjuje na osnovicu.
        </p>
        <h3>Kada moram obračunavati PDV?</h3>
        <p>
            Kad si u sustavu PDV-a — obvezno ako ti godišnji promet prijeđe 60.000 €, ili dobrovoljno.
            Više u vodiču <a href="{{ route('novosti.show', 'pausalni-obrt-i-pdv-prag-60000') }}" style="color:var(--p);font-weight:700;">paušalni obrt i PDV — prag od 60.000 €</a>.
        </p>
        <h3>Čemu služi opcija 0 %?</h3>
        <p>
            Neke isporuke su oslobođene PDV-a ili PDV obračunava kupac (prijenos porezne obveze, npr. kod
            usluga tvrtkama iz drugih država EU-a). Tada je na računu osnovica jednaka ukupnom iznosu, a umjesto
            PDV-a navodi se zakonska napomena. Formule su inače iste u svim državama EU-a — razlikuju se samo stope.
        </p>

        <h2>A paušalisti?</h2>
        <p>
            Paušalni obrtnici nisu u sustavu PDV-a i PDV ne obračunavaju — na računu umjesto PDV-a stoji napomena
            o oslobođenju. Više u vodiču
            <a href="{{ route('novosti.show', 'sto-mora-pisati-na-racunu-pausalni-obrt') }}" style="color:var(--p);font-weight:700;">Što mora pisati na računu paušalnog obrta</a>.
        </p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    var f = new Intl.NumberFormat('hr-HR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var $ = function (id) { return document.getElementById(id); };
    var r2 = function (n) { return Math.round(n * 100) / 100; };
    function izracun() {
        var x = parseFloat($('iznos').value) || 0, s = (+$('stopa').value) / 100, o, p, u;
        if ($('smjer').value === 'neto') { o = r2(x); p = r2(o * s); u = r2(o + p); }
        else { u = r2(x); o = r2(u / (1 + s)); p = r2(u - o); }
        $('o').textContent = f.format(o) + ' €'; $('p').textContent = f.format(p) + ' €'; $('u').textContent = f.format(u) + ' €';
    }
    ['iznos', 'smjer', 'stopa'].forEach(function (id) { $(id).addEventListener('input', izracun); });
    izracun();
})();
</script>
@endpush
