@extends('layouts.blog', [
    'title' => 'Provjera OIB-a i IBAN-a online',
    'description' => 'Besplatna provjera ispravnosti OIB-a i IBAN-a (kontrolna znamenka). Provjeri podatke kupca prije izdavanja računa ili uplatnice.',
    'nav' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="/alati" class="article-back">← Svi alati</a>
    <span class="page-eyebrow">Besplatni alat</span>
    <h1 class="page-title">Provjera OIB-a i IBAN-a</h1>
    <p class="page-sub">Provjeri je li broj ispravno upisan prije nego ga staviš na račun ili uplatnicu. Provjera se radi u tvom pregledniku.</p>

    <div class="tool">
        <div class="row">
            <div>
                <label for="oib">OIB</label>
                <input id="oib" inputmode="numeric" maxlength="11" placeholder="11 znamenki" autocomplete="off">
                <div id="oibRez" class="hint" style="font-size:.95rem;margin-top:10px;"></div>
            </div>
            <div>
                <label for="iban">IBAN</label>
                <input id="iban" placeholder="HR12 3456 7890 1234 5678 9" autocomplete="off" style="text-transform:uppercase;">
                <div id="ibanRez" class="hint" style="font-size:.95rem;margin-top:10px;"></div>
            </div>
        </div>
    </div>

    <div class="tool-text">
        <h2>Što provjera znači</h2>
        <p>
            I OIB i IBAN imaju <strong>kontrolnu znamenku</strong> izračunatu iz ostalih znamenki. Ako se pri
            prepisivanju zamijene ili pogrešno upišu znamenke, kontrolni izračun gotovo uvijek „pukne" — pa
            ovakva provjera uhvati velik dio tipfelera.
        </p>
        <ul>
            <li><strong>OIB</strong> ima 11 znamenki; zadnja je kontrolna, izračunata po normi ISO 7064 (MOD 11,10).</li>
            <li><strong>IBAN</strong> se provjerava po normi ISO 13616 (MOD 97). Hrvatski IBAN počinje s <code>HR</code> i ima 21 znak: HR + 2 kontrolne znamenke + 7 znamenki banke + 10 znamenki računa.</li>
        </ul>
        <div class="callout" style="background:var(--pl);border-radius:12px;padding:18px 22px;color:var(--pd);">
            Ispravna kontrolna znamenka znači da je broj <strong>valjano složen</strong>, ne i da pripada baš toj
            osobi ili tvrtki. Za potvrdu vlasnika koristi službene registre (npr. sudski ili obrtni registar).
        </div>
        <h2>Gdje se koristi</h2>
        <p>
            OIB kupca obvezan je na računu kad je kupac poduzetnik, a IBAN primatelja na svakoj uplatnici. Gotovu
            uplatnicu s barkodom možeš napraviti u alatu <a href="/alati/hub3-uplatnica" style="color:var(--p);font-weight:700;">HUB-3 uplatnica</a>.
        </p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    function oibOk(oib) {
        if (!/^\d{11}$/.test(oib)) return false;
        var a = 10;
        for (var i = 0; i < 10; i++) {
            a = (a + +oib[i]) % 10;
            if (a === 0) a = 10;
            a = (a * 2) % 11;
        }
        var k = 11 - a;
        if (k === 10) k = 0;
        return k === +oib[10];
    }
    function ibanOk(iban) {
        if (!/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/.test(iban)) return false;
        if (iban.slice(0, 2) === 'HR' && iban.length !== 21) return false;
        var s = (iban.slice(4) + iban.slice(0, 4)).replace(/[A-Z]/g, function (c) { return c.charCodeAt(0) - 55; });
        var r = 0;
        for (var i = 0; i < s.length; i++) r = (r * 10 + +s[i]) % 97;
        return r === 1;
    }
    function show(el, val, ok, okTxt, badTxt) {
        el.innerHTML = !val ? '' : ok ? '<span class="ok">✓ ' + okTxt + '</span>' : '<span class="bad">✗ ' + badTxt + '</span>';
    }
    var oib = document.getElementById('oib'), iban = document.getElementById('iban');
    oib.addEventListener('input', function () {
        var v = oib.value.replace(/\D/g, '');
        show(document.getElementById('oibRez'), v, oibOk(v), 'OIB je ispravan (kontrolna znamenka odgovara).',
            v.length !== 11 ? 'OIB mora imati točno 11 znamenki (upisano ' + v.length + ').' : 'Kontrolna znamenka ne odgovara — OIB je pogrešno upisan.');
    });
    iban.addEventListener('input', function () {
        var v = iban.value.toUpperCase().replace(/\s+/g, '');
        var poruka = v.slice(0, 2) === 'HR' && v.length !== 21 ? 'Hrvatski IBAN ima 21 znak (upisano ' + v.length + ').' : 'IBAN nije ispravan — provjeri znamenke.';
        show(document.getElementById('ibanRez'), v, ibanOk(v), 'IBAN je ispravan' + (v.slice(0, 2) === 'HR' ? ' (vodeći broj banke: ' + v.slice(4, 11) + ').' : '.'), poruka);
    });
})();
</script>
@endpush
