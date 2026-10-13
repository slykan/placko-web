<p>
    Novi Zakon o fiskalizaciji (NN 89/2025), poznat kao <strong>Fiskalizacija 2.0</strong>, mijenja način na
    koji obrtnici izdaju i primaju račune. Za paušaliste promjene dolaze u dva koraka: dio vrijedi od
    <strong>1. siječnja 2026.</strong>, a dio od <strong>1. siječnja 2027.</strong> Evo što je što.
</p>

<h2>Pregled po godinama</h2>
<div class="table-wrap">
<table>
    <tr><th>Obveza</th><th>Od kada</th></tr>
    <tr><td>Fiskalizacija svih računa izdanih građanima (krajnja potrošnja), <strong>bez obzira na način plaćanja</strong> — i kad kupac plati na transakcijski račun</td><td>1.1.2026.</td></tr>
    <tr><td><strong>Zaprimanje</strong> eRačuna od drugih poduzetnika (dobavljača)</td><td>1.1.2026.</td></tr>
    <tr><td><strong>Izdavanje</strong> eRačuna tvrtkama i obrtnicima (B2B), i za one koji nisu u sustavu PDV-a</td><td>1.1.2027.</td></tr>
</table>
</div>

<h2>1. Fiskalizacija računa građanima — od 2026.</h2>
<p>
    Do 2026. paušalist je fiskalizirao samo račune naplaćene gotovinom ili karticom. Od 1. siječnja 2026.
    fiskalizira se <strong>svaki račun izdan građaninu</strong>, uključujući one koje kupac plati uplatom na
    žiro račun. Na takvom računu moraju biti <strong>ZKI</strong> (zaštitni kod izdavatelja) i <strong>JIR</strong>
    (jedinstveni identifikator računa) koji dolazi iz sustava Porezne uprave.
</p>
<p>Za fiskalizaciju trebaš:</p>
<ul>
    <li>FINA aplikacijski certifikat za fiskalizaciju (.p12 datoteka),</li>
    <li>prijavljen poslovni prostor u ePoreznoj (oznaka prostora, npr. „1"),</li>
    <li>program za izdavanje računa koji podržava fiskalizaciju (u plačko.app: <strong>Postavke → Fiskalizacija</strong>, gdje učitaš certifikat i upišeš oznaku poslovnog prostora).</li>
</ul>

<h2>2. Zaprimanje eRačuna — od 2026.</h2>
<p>
    Dobavljači koji su u sustavu PDV-a (npr. telekom, HEP, veletrgovci) od 2026. tvrtkama i obrtnicima
    šalju <strong>eRačune</strong> — strukturirane XML datoteke prema normi EN 16931 — umjesto običnog PDF-a.
    Paušalist ih mora moći zaprimiti.
</p>
<ul>
    <li>Najjednostavnije rješenje je besplatna aplikacija Porezne uprave <strong>MIKROeRAČUN</strong> u sustavu
        ePorezna, namijenjena onima koji nisu u sustavu PDV-a. Prijava ide preko NIAS-a, digitalni certifikat nije potreban.</li>
    <li>Druga opcija je informacijski posrednik (komercijalni servis za razmjenu eRačuna).</li>
</ul>

<h2>3. Izdavanje eRačuna — od 2027.</h2>
<p>
    Od 1. siječnja 2027. paušalist mora <strong>izdavati eRačune</strong> kad izdaje račun drugoj tvrtki ili obrtu
    (B2B). Računi građanima i dalje su „obični" fiskalizirani računi.
</p>
<p>
    eRačun nije PDF poslan mailom, nego XML u formatu <strong>UBL 2.1</strong> (PEPPOL BIS Billing 3.0, EN 16931)
    koji se šalje kroz sustav razmjene. plačko.app već danas generira valjani UBL 2.1 XML za svaki račun,
    a izravno slanje kroz sustav razmjene pripremamo prije početka obveze. Porezna uprava najavila je i
    nadogradnju aplikacije MIKROeRAČUN za izdavanje eRačuna.
</p>

<div class="callout">
    <strong>Što napraviti sada (2026.):</strong>
    <ol style="margin:10px 0 0 20px;">
        <li>provjeri da fiskaliziraš sve račune građanima, i one plaćene na račun,</li>
        <li>osiguraj zaprimanje eRačuna (MIKROeRAČUN ili posrednik),</li>
        <li>vodi klijente (OIB, adresu) u programu za račune — trebat će ti za eRačune od 2027.</li>
    </ol>
</div>

<h2>Česta pitanja</h2>
<h3>Moram li fiskalizirati račun tvrtki koja plaća na žiro račun?</h3>
<p>
    Obveza fiskalizacije iz 2026. odnosi se na račune u krajnjoj potrošnji (građanima). Za račune
    tvrtkama i obrtnicima od 2027. vrijede pravila o eRačunu.
</p>
<h3>Je li PDF računa poslan mailom eRačun?</h3>
<p>Nije. eRačun je strukturirana XML datoteka koja se šalje kroz sustav razmjene eRačuna.</p>
<h3>Treba li mi poseban program?</h3>
<p>
    Za fiskalizaciju trebaš program koji to podržava. Za zaprimanje eRačuna dovoljna je besplatna
    aplikacija MIKROeRAČUN.
</p>

<p class="sources">
    Izvori: Zakon o fiskalizaciji (NN 89/2025);
    <a href="https://porezna-uprava.gov.hr/hr/mikroeracun/8190" rel="nofollow noopener" target="_blank">Porezna uprava — MIKROeRAČUN</a>;
    <a href="https://porezna-uprava.gov.hr/hr/nadogradnja-mikroeracuna-besplatno-i-jednostavno-rjesenje-za-izdavanje-eracuna/8911" rel="nofollow noopener" target="_blank">Porezna uprava — nadogradnja MIKROeRAČUNA</a>;
    <a href="https://www.rrif.hr/obveza_fiskalizacije_racuna_prema_novom_zakonu_o_f-4155-misljenje/" rel="nofollow noopener" target="_blank">RRiF — fiskalizacija za paušaliste</a>.
    Stanje na dan 13. listopada 2026.
</p>
