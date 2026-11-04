<x-filament-panels::page>

<div style="max-width: 860px;">
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1.75rem 2rem; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="background:#d1f5f0; border-radius:0.5rem; padding:0.5rem;">
                <x-heroicon-o-wrench-screwdriver style="width:1.5rem; height:1.5rem; color:#1f8a7e;" />
            </div>
            <h2 style="font-size:1.15rem; font-weight:700; margin:0;">Radni nalozi i zaposlenici su u paketu Pro</h2>
        </div>

        <p style="color:#374151; margin-bottom:1rem; line-height:1.7;">
            Otvori nalog za klijenta, dodijeli ga zaposlenicima i pretvori ga u račun jednim klikom — bez papira i prepisivanja.
        </p>

        <ul style="color:#374151; line-height:2; margin:0 0 1.25rem; padding-left:1.25rem;">
            <li><strong>Zaposlenici</strong> s cijenom sata i mjesečnim pregledom sati (izvoz u CSV)</li>
            <li>Nalog stiže zaposleniku <strong>e-mailom</strong> — prihvati ga jednim klikom i otvori na mobitelu, bez lozinke</li>
            <li>Zaposlenik sam upisuje <strong>dolazak, sate, materijal i izvršene radove</strong>, a klijent se <strong>potpisuje na ekranu</strong></li>
            <li>Kad je posao gotov, dobiješ obavijest — <strong>račun iz naloga</strong> složi sate i materijal sam, a zaliha se skine</li>
            <li>Nalog iz ponude, PDF za zaposlenika i za klijenta</li>
        </ul>

        <div style="background:#f9fafb; border-radius:0.5rem; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#374151; line-height:1.7;">
            Vaš paket: <strong>{{ auth()->user()->paket()->label() }}</strong>.
            Paket <strong>Pro</strong> (4,99 €/mj) uključuje radne naloge, zaposlenike, više tvrtki i podršku e-mailom.
        </div>

        <a href="mailto:placko@placko.app?subject={{ rawurlencode('Nadogradnja na Pro (radni nalozi) — '.auth()->user()->email) }}"
           style="display:inline-block; background:#2ba99b; color:#fff; font-weight:700; padding:0.7rem 1.4rem; border-radius:0.5rem; text-decoration:none;">
            Zatraži nadogradnju na Pro
        </a>
    </div>
</div>

</x-filament-panels::page>
