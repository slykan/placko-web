<p>
    Fiskalizacija zvuči komplicirano, ali u biti znači jedno: kad izdaš račun koji se mora fiskalizirati,
    tvoj program ga u trenutku izdavanja prijavi Poreznoj upravi, a ona ga potvrdi jedinstvenim kodom.
    Ovaj vodič objašnjava JIR i ZKI, što ti treba da počneš i što napraviti kad nešto zapne.
</p>

<h2>Koji se računi fiskaliziraju</h2>
<p>
    Fiskalizacija se od 2013. odnosila na račune naplaćene <strong>gotovinom</strong> — a gotovinom se
    u smislu fiskalizacije smatraju novčanice, kovanice, <strong>kartice</strong>, ček i slični načini
    plaćanja. Paušalisti su u sustavu fiskalizacije od 1. srpnja 2017.
</p>
<p>
    S <strong>Fiskalizacijom 2.0</strong> sustav se proširuje: od 1. siječnja 2026. računi prema
    građanima (B2C) fiskaliziraju se bez obzira na način plaćanja, a računi prema tvrtkama (B2B) idu
    kao eRačuni. Za paušaliste obveza <strong>izdavanja</strong> eRačuna tvrtkama kreće 1. siječnja
    2027., a <strong>zaprimanja</strong> već 1. siječnja 2026. Detaljnije: <a href="{{ route('novosti.show', 'fiskalizacija-2-0-eracun-pausalni-obrt') }}" style="color:var(--p);font-weight:700;">Fiskalizacija 2.0 i eRačun za paušaliste</a>.
</p>

<h2>ZKI i JIR — što je što</h2>
<ul>
    <li><strong>ZKI</strong> (zaštitni kod izdavatelja) izračunava tvoj program iz podataka računa (OIB, datum i vrijeme, broj računa, iznos) i tvog certifikata. Dokazuje da je račun izdao baš tvoj obrt i da nije naknadno mijenjan.</li>
    <li><strong>JIR</strong> (jedinstveni identifikator računa) dodjeljuje Porezna uprava kad zaprimi podatke o računu. To je potvrda da je račun prijavljen.</li>
</ul>
<p>
    Oba koda moraju pisati na računu koji kupac dobije. Kupac može provjeriti račun na stranicama
    Porezne uprave, a na računu je često i QR kod za provjeru.
</p>

<h2>Što ti treba za početak</h2>
<ol>
    <li><strong>FINA aplikacijski certifikat za fiskalizaciju</strong> — zatražiš ga preko FINA-e, a datoteku certifikata i lozinku učitaš u program za račune.</li>
    <li><strong>Program za izdavanje računa</strong> koji podržava fiskalizaciju (npr. plačko.app — na webu i mobitelu).</li>
    <li><strong>Interni akt</strong> kojim odrediš oznake poslovnih prostora i naplatnih uređaja (npr. poslovni prostor „1", uređaj „1" — broj računa tada izgleda <code>12-1-1</code>). Uz to se donosi i odluka o blagajničkom maksimumu, ako naplaćuješ u gotovini.</li>
    <li><strong>Oznaka operatera</strong> — tko je izdao račun (kod paušalista najčešće ti sam, s tvojim OIB-om).</li>
</ol>

<h2>Kad nema interneta ili sustav ne radi</h2>
<p>
    Ako u trenutku izdavanja nema veze s Poreznom upravom, račun se ipak izdaje — sa <strong>ZKI-jem</strong>
    ali bez JIR-a — a program ga mora <strong>naknadno dostaviti</strong> čim veza proradi, najkasnije u
    propisanom roku (u pravilu 48 sati). Dobar program to radi automatski i označi račune koji čekaju.
    Ne izdaji račune „rukom" i bez programa, osim iz propisanog bloka računa za iznimne situacije.
</p>

<h2>Česte pogreške</h2>
<ul>
    <li><strong>Istekao certifikat</strong> — FINA certifikat vrijedi ograničeno vrijeme; postavi podsjetnik za obnovu prije isteka.</li>
    <li><strong>Krivi poslovni prostor ili uređaj</strong> — oznake na računu moraju odgovarati internom aktu.</li>
    <li><strong>Račun bez JIR-a koji nikad nije dostavljen</strong> — provjeravaj popis nefiskaliziranih računa u programu.</li>
    <li><strong>Brisanje računa</strong> — fiskalizirani račun se ne briše; pogreška se ispravlja storno računom.</li>
</ul>

<div class="warn">
    Pravila fiskalizacije se tijekom 2026. i 2027. mijenjaju (Fiskalizacija 2.0). Ovo je opći pregled,
    ne porezni savjet — provjeri aktualne upute Porezne uprave ili se obrati knjigovođi.
</div>

<p class="sources">
    Izvori: Zakon o fiskalizaciji i Pravilnik o fiskalizaciji (zakon.hr, NN),
    <a href="https://www.rrif.hr/obveza_fiskalizacije_u_prometu_gotovinom_od_1_srpn-1421-vijest/" rel="nofollow noopener" target="_blank">RRiF — obveza fiskalizacije od 1. srpnja 2017.</a>,
    <a href="https://mojafirma.hr/clanci/fiskalizacija-2-e-racuni-2026-vodic" rel="nofollow noopener" target="_blank">Fiskalizacija 2.0 i e-računi 2026.</a>
    Stanje na dan 26. listopada 2026.
</p>
