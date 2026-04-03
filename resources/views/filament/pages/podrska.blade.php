<x-filament-panels::page>

<div style="max-width: 860px;">

    {{-- FISKALIZACIJA --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1.5rem 2rem; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="background:#dcfce7; border-radius:0.5rem; padding:0.5rem;">
                <x-heroicon-o-document-check style="width:1.5rem; height:1.5rem; color:#16a34a;" />
            </div>
            <h2 style="font-size:1.1rem; font-weight:700; margin:0;">Certifikat za fiskalizaciju (FINA)</h2>
        </div>

        <p style="color:#374151; margin-bottom:1rem; line-height:1.7;">
            Za fiskalizaciju računa (gotovina i kartica) potreban je FINA certifikat — digitalni potpis koji potvrđuje autentičnost vaših računa prema Poreznoj upravi.
        </p>

        <div style="background:#f9fafb; border-radius:0.5rem; padding:1rem 1.25rem; margin-bottom:1rem;">
            <p style="font-weight:600; margin-bottom:0.5rem;">Što vam treba:</p>
            <ul style="color:#374151; line-height:2; margin:0; padding-left:1.25rem;">
                <li>Osobni dolazak u FINA ured (s osobnom iskaznicom)</li>
                <li>OIB obrta/tvrtke</li>
                <li>Popunjen zahtjev za poslovni certifikat</li>
                <li>Jednokratna naknada (trenutno ~100 EUR + PDV, vrijedi 5 godina)</li>
            </ul>
        </div>

        <div style="background:#fefce8; border:1px solid #fde68a; border-radius:0.5rem; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.875rem; color:#854d0e;">
            <strong>Format certifikata:</strong> FINA certifikat dolazi u <strong>PKCS#12 (.p12)</strong> formatu zajedno s lozinkom.
            U Postavke → Fiskalizacija uploadajte <code style="background:#fef08a; padding:0.1rem 0.3rem; border-radius:0.25rem;">.p12</code> datoteku
            i unesite lozinku koju ste dobili od FINE. Nemojte mijenjati naziv datoteke.
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
            <a href="https://www.fina.hr/poslovni-certifikat" target="_blank"
               style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem;
                      background:#16a34a; color:#fff; border-radius:0.5rem; text-decoration:none; font-size:0.875rem; font-weight:500;">
                <x-heroicon-o-arrow-top-right-on-square style="width:1rem; height:1rem;" />
                FINA — Poslovni certifikat
            </a>
            <a href="https://www.fina.hr/lokacije" target="_blank"
               style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem;
                      background:#fff; color:#374151; border:1px solid #d1d5db; border-radius:0.5rem; text-decoration:none; font-size:0.875rem; font-weight:500;">
                <x-heroicon-o-map-pin style="width:1rem; height:1rem;" />
                Pronađi FINA ured
            </a>
        </div>
    </div>

    {{-- ERACUN --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1.5rem 2rem; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="background:#dbeafe; border-radius:0.5rem; padding:0.5rem;">
                <x-heroicon-o-envelope style="width:1.5rem; height:1.5rem; color:#2563eb;" />
            </div>
            <h2 style="font-size:1.1rem; font-weight:700; margin:0;">Certifikat za eRačun (FINA)</h2>
        </div>

        <p style="color:#374151; margin-bottom:1rem; line-height:1.7;">
            Za slanje eRačuna (UBL 2.1 format) državnim tijelima i velikim tvrtkama potreban je pristup sustavu eRačun koji FINA administrira.
            Isti poslovni certifikat može se koristiti i za eRačun.
        </p>

        <div style="background:#f9fafb; border-radius:0.5rem; padding:1rem 1.25rem; margin-bottom:1rem;">
            <p style="font-weight:600; margin-bottom:0.5rem;">Koraci za aktivaciju eRačuna:</p>
            <ol style="color:#374151; line-height:2; margin:0; padding-left:1.25rem;">
                <li>Nabavite FINA poslovni certifikat (isti kao za fiskalizaciju)</li>
                <li>Registrirajte se na FINA eRačun portal</li>
                <li>Aktivirajte pristup sustavu za razmjenu eRačuna</li>
                <li>Unesite podatke u Placko → Postavke → eRačun</li>
            </ol>
        </div>

        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:0.5rem; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.875rem; color:#1e40af;">
            <strong>Napomena:</strong> Slanje eRačuna državnim tijelima obvezno je od 1. siječnja 2019. za sve koji ispostavljaju račune proračunskim korisnicima.
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
            <a href="https://www.fina.hr/eracun" target="_blank"
               style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem;
                      background:#2563eb; color:#fff; border-radius:0.5rem; text-decoration:none; font-size:0.875rem; font-weight:500;">
                <x-heroicon-o-arrow-top-right-on-square style="width:1rem; height:1rem;" />
                FINA — eRačun portal
            </a>
            <a href="https://www.fina.hr/documents/52452/75497/Upute_eRacun.pdf" target="_blank"
               style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem;
                      background:#fff; color:#374151; border:1px solid #d1d5db; border-radius:0.5rem; text-decoration:none; font-size:0.875rem; font-weight:500;">
                <x-heroicon-o-document-text style="width:1rem; height:1rem;" />
                Upute (PDF)
            </a>
        </div>
    </div>

    {{-- KONTAKT --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1.5rem 2rem;">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="background:#fef9c3; border-radius:0.5rem; padding:0.5rem;">
                <x-heroicon-o-chat-bubble-left-ellipsis style="width:1.5rem; height:1.5rem; color:#ca8a04;" />
            </div>
            <h2 style="font-size:1.1rem; font-weight:700; margin:0;">Podrška Placko</h2>
        </div>

        <p style="color:#374151; margin-bottom:1rem; line-height:1.7;">
            Imate pitanje ili problem s aplikacijom? Slobodno nas kontaktirajte — odgovaramo u roku 24 sata radnim danom.
        </p>

        <a href="mailto:podrska@placko.app"
           style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem;
                  background:#2ba99b; color:#fff; border-radius:0.5rem; text-decoration:none; font-size:0.875rem; font-weight:500;">
            <x-heroicon-o-envelope style="width:1rem; height:1rem;" />
            podrska@placko.app
        </a>
    </div>

</div>

</x-filament-panels::page>
