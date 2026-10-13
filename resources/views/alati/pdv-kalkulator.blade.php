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
    <p class="page-sub">Dodaj PDV na iznos ili ga izdvoji iz ukupnog iznosa — po hrvatskim stopama ili po svojoj.</p>

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
