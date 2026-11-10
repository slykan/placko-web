@extends('layouts.blog', [
    'title' => 'Kontakt',
    'description' => 'Kontakt plačko.app — nadogradnja računa, tehnička podrška i ostala pitanja. Odgovaramo u pravilu unutar jednog radnog dana.',
    'nav' => '',
])

@php
    $teme = \App\Http\Controllers\KontaktController::TEME;
    $korisnik = auth()->user();
    $paketi = config('cjenik.planovi');
    // ?paket=Pro (npr. s gumba u cjeniku) sam odabere temu nadogradnje
    $odabranaTema = old('tema', array_key_exists((string) request('tema'), $teme) ? request('tema')
        : (in_array(request('paket'), array_column($paketi, 'naziv'), true) ? 'nadogradnja' : null));
    $turnstile = config('services.turnstile.key') && ! app()->environment('local', 'testing');
@endphp

@section('content')
    <style>
        .kontakt-stranica { width: min(1120px, calc(100vw - 40px)); position: relative; left: 50%; transform: translateX(-50%); }
        .kontakt { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); gap: 32px; margin-top: 40px; align-items: start; }
        .kontakt .tool { margin-top: 0; }
        .kontakt h2.forma-naslov { font-size: 1.3rem; font-weight: 800; margin-bottom: 6px; }
        .kontakt .forma-uvod { color: var(--muted); font-size: .95rem; margin-bottom: 22px; }
        .teme { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 18px; }
        .tema input { position: absolute; opacity: 0; pointer-events: none; }
        .tool .tema { margin: 0; }
        .tema span {
            display: flex; flex-direction: column; align-items: center; gap: 6px; text-align: center;
            padding: 14px 8px; border: 1.5px solid var(--border); border-radius: 12px; background: var(--bg);
            font-size: .88rem; font-weight: 700; color: var(--muted); cursor: pointer; transition: all .15s; height: 100%;
        }
        .tema span svg { width: 26px; height: 26px; color: var(--p); }
        .tema input:checked + span { border-color: var(--p); background: var(--pl); color: var(--pd); }
        .tema input:focus-visible + span { outline: 2px solid var(--p); outline-offset: 2px; }
        .polje { margin-bottom: 16px; }
        .kontakt textarea { min-height: 150px; resize: vertical; }
        .tool .kvacica { display: flex; font-weight: 400; line-height: 1.5; gap: 10px; align-items: flex-start; font-size: .9rem; color: var(--muted); margin-bottom: 12px; cursor: pointer; }
        .tool .kvacica input { width: 18px !important; height: 18px; margin-top: 2px; flex-shrink: 0; accent-color: var(--p); }
        .kvacica a { color: var(--pd); font-weight: 700; }
        .greska { color: #b42318; font-size: .84rem; font-weight: 600; margin-top: 5px; }
        .zamka { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .poslano { padding: 18px 22px; border-radius: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; margin-bottom: 22px; }
        .poslano strong { display: block; font-size: 1.05rem; margin-bottom: 2px; }
        .info { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 26px; }
        .info + .info { margin-top: 16px; }
        .info-red { display: flex; gap: 14px; align-items: flex-start; }
        .info-red + .info-red { margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--border); }
        .info-ico { width: 40px; height: 40px; border-radius: 12px; background: var(--pl); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--pd); }
        .info-ico svg { width: 21px; height: 21px; }
        .info h3 { font-size: .98rem; font-weight: 800; margin-bottom: 2px; }
        .info p, .info li { color: var(--muted); font-size: .9rem; line-height: 1.6; }
        .info a { color: var(--pd); font-weight: 700; }
        .info ul { margin: 8px 0 0 18px; }
        .info li + li { margin-top: 6px; }
        .paketi-blok { margin: -4px 0 20px; padding: 16px; border-radius: 14px; background: var(--bg); border: 1px dashed rgba(43,169,155,.35); }
        .paketi-blok[hidden] { display: none; }
        .tool .paketi-naslov { margin-bottom: 10px; }
        .paketi { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .tool .paket { margin: 0; }
        .paket input { position: absolute; opacity: 0; pointer-events: none; }
        .paket span {
            display: flex; flex-direction: column; align-items: center; gap: 2px; text-align: center; height: 100%;
            padding: 12px 8px; border: 1.5px solid var(--border); border-radius: 12px; background: var(--white); cursor: pointer; transition: all .15s;
        }
        .paket strong { font-size: .98rem; font-weight: 800; color: var(--text); }
        .paket em { font-style: normal; font-size: .95rem; font-weight: 800; color: var(--pd); }
        .paket small { font-size: .75rem; font-weight: 600; color: var(--light); }
        .paket input:checked + span { border-color: var(--p); background: var(--pl); box-shadow: 0 0 0 3px rgba(43,169,155,.12); }
        .paket input:focus-visible + span { outline: 2px solid var(--p); outline-offset: 2px; }
        @media (max-width: 860px) { .kontakt { grid-template-columns: 1fr; } .kontakt-stranica { width: auto; left: 0; transform: none; } }
        @media (max-width: 480px) { .paketi { grid-template-columns: 1fr; } .paket span { flex-direction: row; justify-content: space-between; padding: 12px 14px; } .teme { grid-template-columns: 1fr; } .tema span { flex-direction: row; justify-content: flex-start; padding: 12px 14px; } }
    </style>

    <div class="kontakt-stranica">
    <span class="page-eyebrow">Kontakt</span>
    <h1 class="page-title">Javi nam se</h1>
    <p class="page-sub">Nadogradnja paketa, zapelo je nešto u aplikaciji ili imaš prijedlog? Odgovaramo u pravilu unutar jednog radnog dana.</p>

    <div class="kontakt">
        <form method="POST" action="{{ route('kontakt.posalji') }}" class="tool" id="obrazac" novalidate>
            @csrf
            <input type="hidden" name="_t" value="{{ encrypt(time()) }}">
            <div class="zamka" aria-hidden="true">
                <label for="web_stranica">Web stranica</label>
                <input type="text" id="web_stranica" name="web_stranica" tabindex="-1" autocomplete="off">
            </div>

            @if (session('kontakt_poslano'))
                <div class="poslano" role="status">
                    <strong>Poruka je poslana ✓</strong>
                    Hvala! Javit ćemo ti se na upisanu e-mail adresu.
                </div>
            @endif

            <h2 class="forma-naslov">Pošalji poruku</h2>
            <p class="forma-uvod">Odaberi temu kako bi poruka odmah stigla na pravo mjesto.</p>

            <div class="teme" role="radiogroup" aria-label="Tema">
                @foreach ($teme as $kljuc => $naziv)
                    <label class="tema">
                        <input type="radio" name="tema" value="{{ $kljuc }}" @checked($odabranaTema === $kljuc) required>
                        <span>@svg('heroicon-o-'.['nadogradnja' => 'star', 'podrska' => 'wrench-screwdriver', 'ostalo' => 'chat-bubble-left-right'][$kljuc]){{ $naziv }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Paketi: samo uz temu "Nadogradnja računa" (cijene iz config/cjenik.php, isto kao na naslovnici) --}}
            @php($odabraniPaket = old('paket', in_array(request('paket'), array_column($paketi, 'naziv'), true) ? request('paket') : null))
            <div class="paketi-blok" id="paketi"@if ($odabranaTema !== 'nadogradnja') hidden @endif>
                <label class="paketi-naslov">Koji paket te zanima?</label>
                <div class="paketi" role="radiogroup" aria-label="Paket">
                    @foreach ($paketi as $paket)
                        <label class="paket">
                            <input type="radio" name="paket" value="{{ $paket['naziv'] }}" @checked($odabraniPaket === $paket['naziv'])>
                            <span>
                                <strong>{{ $paket['naziv'] }}</strong>
                                <em>{{ $paket['cijena_prikaz'] }}<small> / {{ $paket['jedinica'] }}</small></em>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('paket')<p class="greska">{{ $message }}</p>@enderror
            </div>
            @error('tema')<p class="greska" style="margin:-8px 0 14px;">{{ $message }}</p>@enderror

            <div class="row">
                <div>
                    <label for="ime">Ime i prezime</label>
                    <input type="text" id="ime" name="ime" value="{{ old('ime', $korisnik?->name) }}" maxlength="100" autocomplete="name" required>
                    @error('ime')<p class="greska">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $korisnik?->email) }}" maxlength="191" autocomplete="email" required>
                    @error('email')<p class="greska">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="polje">
                <label for="poruka">Poruka</label>
                <textarea id="poruka" name="poruka" maxlength="5000" required placeholder="Kod greške pomaže ako napišeš što si radio/la i u kojem pregledniku ili na kojem mobitelu.">{{ old('poruka') }}</textarea>
                @error('poruka')<p class="greska">{{ $message }}</p>@enderror
            </div>

            <label class="kvacica">
                <input type="checkbox" name="kopija" value="1" @checked(old('kopija', ! $errors->any()))>
                <span>Pošalji mi kopiju poruke na moj e-mail</span>
            </label>
            <label class="kvacica">
                <input type="checkbox" name="privatnost" value="1" @checked(old('privatnost')) required>
                <span>Slažem se da se moje ime, e-mail i poruka koriste samo za odgovor na ovaj upit, prema <a href="/privatnost" target="_blank">pravilima privatnosti</a>.</span>
            </label>
            @error('privatnost')<p class="greska" style="margin:-6px 0 12px;">{{ $message }}</p>@enderror

            @if ($turnstile)
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-language="hr" data-theme="light" style="margin:6px 0 4px;"></div>
            @endif
            @error('turnstile')<p class="greska">{{ $message }}</p>@enderror

            <button type="submit" class="btn-tool" style="margin-top:14px;padding:13px 28px;">Pošalji poruku ➜</button>
        </form>

        <aside>
            <div class="info">
                <div class="info-red">
                    <div class="info-ico">@svg('heroicon-o-envelope')</div>
                    <div>
                        <h3>E-mail</h3>
                        <p><a href="mailto:{{ config('mail.kontakt') }}">{{ config('mail.kontakt') }}</a></p>
                    </div>
                </div>
                <div class="info-red">
                    <div class="info-ico">@svg('heroicon-o-map-pin')</div>
                    <div>
                        <h3>Adresa</h3>
                        <p>On-Click<br>Ilirska 27, 31000 Osijek<br>Hrvatska</p>
                    </div>
                </div>
                <div class="info-red">
                    <div class="info-ico">@svg('heroicon-o-clock')</div>
                    <div>
                        <h3>Odgovor</h3>
                        <p>Radnim danom, u pravilu unutar 24 sata.</p>
                    </div>
                </div>
            </div>

            <div class="info">
                <h3>Prije nego pišeš</h3>
                <ul>
                    <li>Pitanja o porezu i doprinosima: pogledaj <a href="/novosti?kategorija=vodic">vodiče</a>. Za konkretan slučaj obrati se Poreznoj upravi ili knjigovođi.</li>
                    <li>Još nemaš račun? <a href="/admin/register">Isprobaj besplatno</a>, bez kreditne kartice.</li>
                </ul>
            </div>
        </aside>
    </div>

    </div>

    <script>
        // Paketi se pokazuju samo uz temu "Nadogradnja računa"; kod druge teme odabir paketa se poništi.
        (() => {
            const blok = document.getElementById('paketi');
            document.querySelectorAll('input[name="tema"]').forEach((r) => r.addEventListener('change', () => {
                const nadogradnja = r.checked && r.value === 'nadogradnja';
                blok.hidden = ! nadogradnja;
                if (! nadogradnja) blok.querySelectorAll('input').forEach((p) => { p.checked = false; });
            }));
        })();
    </script>

    @if ($turnstile)
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endif
@endsection
