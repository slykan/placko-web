<p>
    Paušalist nije u sustavu PDV-a, ali njegov račun i dalje mora sadržavati niz obveznih podataka. Račun
    bez nekog od njih može stvoriti probleme kod nadzora, a klijentu-tvrtki ga knjigovođa može vratiti.
    Ovo je kontrolna lista koju možeš usporediti sa svojim računom.
</p>

<h2>Obvezni elementi računa</h2>
<ol>
    <li><strong>Podaci o izdavatelju</strong> — naziv obrta (može i skraćeni), adresa sjedišta i <strong>OIB</strong>.</li>
    <li><strong>Broj računa</strong> — sastoji se od rednog broja, oznake poslovnog prostora i oznake naplatnog uređaja, npr. <code>12-1-1</code>. Redni broj ide slijedno unutar godine.</li>
    <li><strong>Datum izdavanja</strong>, a za fiskalizirane račune i <strong>vrijeme</strong> izdavanja.</li>
    <li><strong>Datum isporuke</strong> robe ili obavljene usluge.</li>
    <li><strong>Mjesto</strong> izdavanja.</li>
    <li><strong>Podaci o kupcu</strong> — naziv, adresa i OIB. OIB kupca obvezan je kad je kupac poduzetnik (tvrtka, obrt); za građane nije.</li>
    <li><strong>Opis</strong> isporučene robe ili usluge, <strong>količina</strong>, jedinična cijena i eventualni <strong>rabat</strong>.</li>
    <li><strong>Ukupan iznos</strong> za plaćanje.</li>
    <li><strong>Napomena o PDV-u</strong> — da obveznik nije u sustavu PDV-a i da PDV nije obračunat (vidi ispod).</li>
    <li><strong>Način plaćanja</strong> (gotovina, kartica, transakcijski račun…).</li>
    <li>Za fiskalizirane račune: <strong>oznaka operatera</strong> te <strong>ZKI</strong> i <strong>JIR</strong>.</li>
</ol>

<h2>Napomena o PDV-u</h2>
<p>Paušalist ne smije iskazati PDV na računu, ali mora navesti zašto ga nema. Uobičajena formulacija je:</p>
<div class="callout">
    „Obveznik nije u sustavu PDV-a, PDV nije obračunat temeljem čl. 90. st. 1. Zakona o PDV-u."
</div>
<p>
    U praksi se mogu naći i starije formulacije s pozivom na čl. 90. st. 2. — ako nisi siguran koju
    koristiti, provjeri sa svojim knjigovođom.
</p>

<h2>Što je poželjno, iako nije obvezno</h2>
<ul>
    <li><strong>IBAN</strong> i <strong>poziv na broj</strong> — da klijent zna kamo i kako platiti.</li>
    <li><strong>Rok plaćanja</strong> ili datum dospijeća.</li>
    <li><strong>HUB-3 barkod</strong> — klijent ga skenira mobilnim bankarstvom i plaća bez prepisivanja. Barkod možeš besplatno izraditi našim <a href="/alati/hub3-uplatnica" style="color:var(--p);font-weight:700;">generatorom HUB-3 uplatnice</a>.</li>
    <li>Kontakt (telefon, e-mail) i logo obrta.</li>
</ul>

<h2>Fiskalizacija i eRačun</h2>
<p>
    Od 2026. fiskaliziraju se svi računi izdani građanima, bez obzira na način plaćanja, a od 2027.
    paušalisti tvrtkama i obrtima izdaju eRačune. Detaljno u vodiču
    <a href="{{ route('novosti.show', 'fiskalizacija-2-0-eracun-pausalni-obrt') }}" style="color:var(--p);font-weight:700;">Fiskalizacija 2.0 i eRačun za paušaliste</a>.
</p>

<h2>Česte greške</h2>
<ul>
    <li><strong>Preskočen ili ponovljen broj računa</strong> — najčešće nakon prelaska iz drugog programa. U plačko.app to rješava <a href="{{ route('novosti.show', 'pocetni-broj-racuna') }}" style="color:var(--p);font-weight:700;">postavka početnog broja računa</a>.</li>
    <li><strong>Iskazan PDV</strong> iako obrtnik nije u sustavu PDV-a.</li>
    <li><strong>Nedostaje OIB kupca</strong> na računu tvrtki.</li>
    <li><strong>Nedostaje vrijeme izdavanja</strong> na fiskaliziranom računu.</li>
</ul>

<p>
    U plačko.app većina toga ide automatski: podatke obrta i kupca aplikacija povlači iz postavki i
    popisa klijenata, broj računa slaže sama, a HUB-3 barkod, način plaćanja, oznaku operatera te (uz
    uključenu fiskalizaciju) ZKI i JIR dodaje na PDF. Napomenu o PDV-u upišeš jednom kao zadanu napomenu
    tvrtke i ona se predlaže na svakom novom računu.
</p>

<p class="sources">
    Izvori:
    <a href="https://fiskalopedija.hr/baza-znanja/izrada-racuna" rel="nofollow noopener" target="_blank">Fiskalopedija — izrada računa u paušalnom obrtu</a>;
    <a href="https://porezna-uprava.gov.hr/en/obrtnici-pausalisti/4564" rel="nofollow noopener" target="_blank">Porezna uprava — vodič za obrtnike paušaliste</a>;
    Zakon o PDV-u (čl. 90.), Zakon o fiskalizaciji (NN 89/2025). Stanje na dan 13. listopada 2026.
</p>
