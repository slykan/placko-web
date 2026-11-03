<p>
    Do kraja 2026. paušalist tvrtkama i obrtnicima može slati račun kao PDF mailom ili na papiru. Od
    <strong>1. siječnja 2027.</strong> to više nije dovoljno: račun drugom poduzetniku u Hrvatskoj mora biti
    <strong>eRačun</strong>, poslan kroz sustav razmjene eRačuna i fiskaliziran. Ovaj vodič objašnjava
    kome se šalje eRačun, a kome i dalje običan račun, koja su tri načina izdavanja i što pripremiti
    još u prosincu.
</p>

<h2>Kome se od 2027. izdaje eRačun, a kome ne</h2>
<div class="table-wrap">
<table>
    <tr><th>Kupac</th><th>Što izdaješ od 1. 1. 2027.</th></tr>
    <tr><td>Tvrtka ili obrt u Hrvatskoj (B2B)</td><td><strong>eRačun</strong> kroz sustav razmjene, fiskaliziran</td></tr>
    <tr><td>Javni naručitelj — škola, općina, ministarstvo (B2G)</td><td><strong>eRačun</strong> — obveza u javnoj nabavi postoji već od 2019.</td></tr>
    <tr><td>Građanin (krajnja potrošnja)</td><td>običan račun s <strong>JIR-om i ZKI-jem</strong>, kao u 2026. — bez obzira plaća li gotovinom, karticom ili na račun</td></tr>
    <tr><td>Kupac iz inozemstva</td><td>običan račun; obveza eRačuna odnosi se na domaće poduzetnike</td></tr>
</table>
</div>
<p>
    Zato je važno da u programu za račune za svakog klijenta jasno piše je li <strong>tvrtka/obrt ili
    fizička osoba</strong> i da imaš njegov <strong>OIB</strong>. U plačko.app to je prekidač
    <strong>„Građanin (fizička osoba)"</strong> na klijentu: račun fizičkoj osobi fiskalizira se kao račun građaninu,
    a ostali klijenti od 2027. dobivaju eRačun.
</p>

<h2>Što je eRačun, a što nije</h2>
<ul>
    <li>eRačun je <strong>strukturirana XML datoteka</strong> (norma EN 16931, hrvatska specifikacija CIUS-HR) koju program kupca može sam pročitati i proknjižiti.</li>
    <li><strong>PDF poslan mailom nije eRačun</strong> — ni kad je potpisan, ni kad u prilogu ima XML.</li>
    <li>eRačun ne putuje mailom nego kroz <strong>sustav razmjene</strong>: preko informacijskog posrednika ili besplatne aplikacije Porezne uprave MIKROeRAČUN.</li>
    <li>Uz slanje kupcu, podaci o eRačunu automatski idu i Poreznoj upravi — to je „fiskalizacija eRačuna". Zakon o fiskalizaciji traži i da se Poreznoj javi <strong>podatak o naplati</strong> izdanog eRačuna (eIzvještavanje); rokove i način provjeri u vodiču Porezne uprave ili s knjigovođom.</li>
</ul>

<h2>Tri načina izdavanja</h2>
<div class="table-wrap">
<table>
    <tr><th>Način</th><th>Cijena</th><th>Za koga</th></tr>
    <tr>
        <td><strong>MIKROeRAČUN</strong> (ePorezna → Usluge → MIKROeRAČUN)</td>
        <td>besplatno, bez ugovora</td>
        <td>paušalisti s malo B2B računa mjesečno; svaki eRačun upisuješ ručno u aplikaciji</td>
    </tr>
    <tr>
        <td><strong>Informacijski posrednik</strong> (komercijalni servis za razmjenu)</td>
        <td>mjesečna pretplata ili cijena po računu</td>
        <td>tko izdaje puno računa ili želi automatsko slanje iz svog programa</td>
    </tr>
    <tr>
        <td><strong>Program za račune</strong> koji izrađuje eRačun</td>
        <td>ovisno o programu</td>
        <td>tko želi sve na jednom mjestu: klijenti, stavke, knjiga prometa, PO-SD</td>
    </tr>
</table>
</div>
<p>
    Porezna uprava objavila je da je nadograđeni MIKROeRAČUN, uz zaprimanje, omogućio i
    <strong>izdavanje eRačuna</strong>, pa se izdavanje može isprobati i prije 1. siječnja. Prijava ide
    preko NIAS-a (e-osobna, mToken ili token banke), certifikat nije potreban.
</p>

<h2>Kako to radi u plačko.app</h2>
<ul>
    <li>Za svaki izdani račun plačko.app izrađuje eRačun u formatu <strong>CIUS-HR</strong> (UBL 2.1) — na popisu računa klikni ikonu <strong>Preuzmi eRačun XML</strong> i datoteka se preuzme (npr. za predaju kroz posrednika).</li>
    <li>Kad u <strong>Postavkama</strong> aktiviraš eRačun servis, račun se iz popisa šalje izravno, a stupac pokazuje kada je poslan i ID poruke.</li>
    <li>Ulazni eRačuni dobavljača dohvaćaju se u izbornik <strong>Ulazni eRačuni (URA)</strong>.</li>
    <li>Knjiga prometa i PO-SD i dalje se računaju iz naplaćenih računa — bez obzira jesi li račun poslao kao eRačun ili kao običan račun građaninu.</li>
</ul>

<div class="callout">
    <strong>Kontrolna lista za prosinac 2026.:</strong>
    <ol style="margin:10px 0 0 20px;">
        <li>Provjeri da svi klijenti imaju <strong>OIB i adresu</strong> i da su fizičke osobe označene kao fizičke osobe.</li>
        <li>Uđi u <strong>ePoreznu</strong> i otvori MIKROeRAČUN — tu već zaprimaš eRačune dobavljača.</li>
        <li>Odluči kojim ćeš putem izdavati: MIKROeRAČUN, posrednik ili program.</li>
        <li>Izdaj jedan <strong>probni eRačun</strong> prije Nove godine (npr. sebi ili partneru koji pristane), da u siječnju ne učiš pod pritiskom.</li>
        <li>Prvi račun tvrtki u siječnju 2027. pošalji <strong>samo kao eRačun</strong> — PDF uz njega smiješ poslati kao kopiju, ali on više nije račun.</li>
    </ol>
</div>

<h2>Česta pitanja</h2>
<h3>Moram li sada, u 2026., izdavati eRačune?</h3>
<p>
    Ne. Paušalist izvan sustava PDV-a u 2026. mora eRačune samo <strong>zaprimati</strong>. Izdavanje je
    obvezno od 1. siječnja 2027. (iznimka su računi javnim naručiteljima, gdje je eRačun obvezan od ranije).
</p>
<h3>Što ako kupac nije prijavljen u sustav eRačuna?</h3>
<p>
    Od 2026. svi poduzetnici u Hrvatskoj moraju moći zaprimati eRačune, pa bi svaka tvrtka i obrt trebali
    biti dostupni u sustavu prema svom OIB-u. Ako slanje ne prođe, javi kupcu — problem je obično u
    njegovoj prijavi kod posrednika.
</p>
<h3>Mijenja li se nešto za račune građanima?</h3>
<p>
    Ne. Račun građaninu i dalje je običan račun s JIR-om i ZKI-jem, a fiskalizira se bez obzira na način
    plaćanja, kao i u 2026.
</p>
<h3>Mijenja li eRačun knjigu prometa ili PO-SD?</h3>
<p>
    Ne. U knjigu prometa i dalje se upisuju <strong>naplaćeni</strong> iznosi, a PO-SD se predaje do
    15. siječnja kao i dosad.
</p>

<p class="sources">
    Izvori: Zakon o fiskalizaciji (NN 89/2025);
    <a href="https://porezna-uprava.gov.hr/hr/vodic-kroz-fiskalizaciju-2-0/8149" rel="nofollow noopener" target="_blank">Porezna uprava — Vodič kroz Fiskalizaciju 2.0</a>;
    <a href="https://porezna-uprava.gov.hr/hr/nadogradnja-mikroeracuna-besplatno-i-jednostavno-rjesenje-za-izdavanje-eracuna/8911" rel="nofollow noopener" target="_blank">Porezna uprava — nadogradnja MIKROeRAČUNA</a>;
    <a href="https://www.rrif.hr/rokovi_za_prilagodbu_fiskalizaciji_2_0-4175-misljenje/" rel="nofollow noopener" target="_blank">RRiF — rokovi za prilagodbu Fiskalizaciji 2.0</a>.
    Stanje na dan 3. studenoga 2026. Ovo je informativni sažetak, a ne porezni savjet — za svoj slučaj
    provjeri s knjigovođom ili Poreznom upravom.
</p>
