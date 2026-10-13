@extends('layouts.blog', [
    'title' => 'HUB-3 uplatnica s 2D barkodom — besplatni generator',
    'description' => 'Izradi HUB-3 uplatnicu s PDF417 barkodom koji se skenira mobilnim bankarstvom. Besplatno, bez registracije — ispis ili preuzimanje PNG barkoda.',
    'nav' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="/alati" class="article-back no-print">← Svi alati</a>
    <span class="page-eyebrow no-print">Besplatni alat</span>
    <h1 class="page-title no-print">HUB-3 uplatnica s barkodom</h1>
    <p class="page-sub no-print">Popuni podatke i dobiješ 2D barkod koji platitelj skenira u mobilnom bankarstvu — bez prepisivanja IBAN-a i poziva na broj.</p>

    <div class="tool">
        <div class="no-print">
            <h3 style="font-size:1rem;font-weight:800;margin-bottom:12px;color:var(--pd);">Primatelj (tko prima novac)</h3>
            <div class="row">
                <div><label for="pIme">Naziv / ime i prezime</label><input id="pIme" maxlength="25" placeholder="Obrt Primjer"></div>
                <div><label for="pAdr">Ulica i broj</label><input id="pAdr" maxlength="25" placeholder="Ilica 1"></div>
                <div><label for="pMj">Poštanski broj i mjesto</label><input id="pMj" maxlength="27" placeholder="10000 Zagreb"></div>
            </div>
            <div class="row">
                <div><label for="iban">IBAN primatelja</label><input id="iban" maxlength="34" placeholder="HR1210010051863000160" style="text-transform:uppercase;"><div id="ibanHint" class="hint"></div></div>
                <div><label for="iznos">Iznos, €</label><input id="iznos" type="number" min="0" step="0.01" placeholder="0,00" inputmode="decimal"></div>
            </div>
            <div class="row">
                <div><label for="model">Model</label>
                    <select id="model">
                        <option>HR00</option><option selected>HR01</option><option>HR02</option><option>HR03</option><option>HR05</option><option>HR99</option>
                    </select>
                    <div class="hint">HR99 = bez poziva na broj.</div>
                </div>
                <div><label for="poziv">Poziv na broj</label><input id="poziv" maxlength="22" placeholder="npr. 12-1-2026"></div>
                <div><label for="sifra">Šifra namjene (neobavezno)</label><input id="sifra" maxlength="4" placeholder="npr. OTHR" style="text-transform:uppercase;"></div>
            </div>
            <div class="row">
                <div style="grid-column:1/-1"><label for="opis">Opis plaćanja</label><input id="opis" maxlength="35" placeholder="Uplata prema računu 12-1-2026"></div>
            </div>

            <h3 style="font-size:1rem;font-weight:800;margin:20px 0 12px;color:var(--pd);">Platitelj (neobavezno)</h3>
            <div class="row">
                <div><label for="uIme">Ime i prezime / naziv</label><input id="uIme" maxlength="30"></div>
                <div><label for="uAdr">Ulica i broj</label><input id="uAdr" maxlength="27"></div>
                <div><label for="uMj">Poštanski broj i mjesto</label><input id="uMj" maxlength="27"></div>
            </div>
        </div>

        <div class="result" style="background:var(--white);border:1.5px dashed var(--border);text-align:center;">
            <canvas id="kod" style="max-width:100%;margin:0 auto;display:block;"></canvas>
            <div id="kodInfo" class="hint" style="margin-top:12px;font-size:.9rem;"></div>
            <div class="no-print" style="margin-top:16px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <button type="button" class="btn-tool" id="png">Preuzmi PNG</button>
                <button type="button" class="btn-tool secondary" onclick="window.print()">Ispiši</button>
                <button type="button" class="btn-tool secondary" id="primjer">Popuni primjerom</button>
            </div>
        </div>
        <p class="hint no-print" style="margin-top:12px;">Podaci se ne šalju nigdje — barkod se crta u tvom pregledniku. Dijakritički znakovi zamjenjuju se (č→c, š→s…) radi kompatibilnosti s bankama.</p>
    </div>

    <div class="tool-text no-print">
        <h2>Što je HUB-3 barkod?</h2>
        <p>
            HUB-3 je standard Hrvatske udruge banaka za uplatnice. Uz klasičnu uplatnicu definira i <strong>2D barkod
            (PDF417)</strong> u kojem su zapisani svi podaci za plaćanje: IBAN, iznos, model i poziv na broj, opis i
            podaci o primatelju. Platitelj ga skenira u aplikaciji mobilnog bankarstva i nalog se popuni sam.
        </p>
        <h2>Kako popuniti</h2>
        <ul>
            <li><strong>IBAN</strong> — hrvatski IBAN ima 21 znak i počinje s HR. Alat provjerava kontrolne znamenke.</li>
            <li><strong>Model i poziv na broj</strong> — za uplate po računu često se koristi HR01 ili HR00 s brojem računa; ako poziv na broj ne treba, odaberi HR99.</li>
            <li><strong>Opis</strong> — do 35 znakova, npr. „Uplata prema računu 12-1-2026".</li>
            <li><strong>Šifra namjene</strong> — neobavezna četveroslovna oznaka (npr. OTHR za ostala plaćanja).</li>
        </ul>
        <h2>Barkod na svakom računu, automatski</h2>
        <p>
            Ako izdaješ račune u plačko.app, HUB-3 barkod se automatski generira na PDF-u svakog računa iz
            podataka tvoje tvrtke i kupca — ništa ne trebaš upisivati dvaput. Više o tome što još mora pisati na računu:
            <a href="{{ route('novosti.show', 'sto-mora-pisati-na-racunu-pausalni-obrt') }}" style="color:var(--p);font-weight:700;">obvezni elementi računa</a>.
        </p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bwip-js@4.5.1/dist/bwip-js-min.js"></script>
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var MAP = { 'č': 'c', 'ć': 'c', 'š': 's', 'ž': 'z', 'đ': 'd', 'Č': 'C', 'Ć': 'C', 'Š': 'S', 'Ž': 'Z', 'Đ': 'D' };
    var ascii = function (s, n) { return s.replace(/[čćšžđČĆŠŽĐ]/g, function (c) { return MAP[c]; }).replace(/[^\x20-\x7E]/g, '').trim().slice(0, n); };

    function ibanOk(iban) {
        if (!/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/.test(iban) || (iban.slice(0, 2) === 'HR' && iban.length !== 21)) return false;
        var s = (iban.slice(4) + iban.slice(0, 4)).replace(/[A-Z]/g, function (c) { return c.charCodeAt(0) - 55; }), r = 0;
        for (var i = 0; i < s.length; i++) r = (r * 10 + +s[i]) % 97;
        return r === 1;
    }

    function hub3() {
        var iznos = Math.round((parseFloat($('iznos').value) || 0) * 100);
        var iban = $('iban').value.toUpperCase().replace(/\s+/g, '');
        return [
            'HRVHUB30', 'EUR', String(iznos).padStart(15, '0'),
            ascii($('uIme').value, 30), ascii($('uAdr').value, 27), ascii($('uMj').value, 27),
            ascii($('pIme').value, 25), ascii($('pAdr').value, 25), ascii($('pMj').value, 27),
            iban, $('model').value, ascii($('poziv').value, 22), ascii($('sifra').value.toUpperCase(), 4), ascii($('opis').value, 35),
        ].join('\n') + '\n';
    }

    function crtaj() {
        var iban = $('iban').value.toUpperCase().replace(/\s+/g, '');
        $('ibanHint').innerHTML = !iban ? '' : ibanOk(iban) ? '<span class="ok">✓ IBAN je ispravan</span>' : '<span class="bad">✗ IBAN nije ispravan</span>';
        var spreman = ibanOk(iban) && $('pIme').value.trim() !== '';
        var c = $('kod');
        if (!spreman || !window.bwipjs) {
            c.width = 0; c.height = 0;
            $('kodInfo').textContent = window.bwipjs ? 'Upiši barem naziv primatelja i ispravan IBAN — barkod će se pojaviti ovdje.' : 'Učitavam generator barkoda…';
            $('png').disabled = true;
            return;
        }
        try {
            bwipjs.toCanvas(c, { bcid: 'pdf417', text: hub3(), columns: 9, eclevel: 4, scale: 2, rowmult: 3, padding: 10, backgroundcolor: 'FFFFFF' });
            var iznos = parseFloat($('iznos').value) || 0;
            $('kodInfo').textContent = $('pIme').value.trim() + ' · ' + iban + ' · ' + new Intl.NumberFormat('hr-HR', { minimumFractionDigits: 2 }).format(iznos) + ' €';
            $('png').disabled = false;
        } catch (e) {
            $('kodInfo').textContent = 'Barkod nije moguće izraditi: ' + e;
        }
    }

    document.querySelectorAll('.tool input, .tool select').forEach(function (el) { el.addEventListener('input', crtaj); });
    $('png').addEventListener('click', function () {
        var a = document.createElement('a');
        a.download = 'hub3-barkod.png';
        a.href = $('kod').toDataURL('image/png');
        a.click();
    });
    function primjer() {
        var v = { pIme: 'Obrt Primjer', pAdr: 'Ilica 1', pMj: '10000 Zagreb', iban: 'HR1210010051863000160', iznos: '125.50',
                  poziv: '12-1-2026', opis: 'Uplata prema racunu 12-1-2026', uIme: 'Ivana Horvat', uAdr: 'Vukovarska 5', uMj: '31000 Osijek' };
        Object.keys(v).forEach(function (k) { $(k).value = v[k]; });
        crtaj();
    }
    $('primjer').addEventListener('click', primjer);
    var start = function () { if (/[?&]primjer\b/.test(location.search)) primjer(); else crtaj(); };
    if (window.bwipjs) start(); else window.addEventListener('load', start);
})();
</script>
@endpush
