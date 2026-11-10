<x-filament-panels::page>

    <form wire:submit="spremiKorisnika">
        {{ $this->korisnikForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">Spremi korisničke podatke</x-filament::button>
        </div>
    </form>

    <form wire:submit="spremiSmtp" class="mt-6">
        {{ $this->smtpForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">Spremi SMTP postavke</x-filament::button>
        </div>
    </form>

    <form wire:submit="spremiEmail" class="mt-6">
        {{ $this->emailForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">Spremi predložak</x-filament::button>
        </div>
    </form>

    <form wire:submit="spremiPretplate" class="mt-6">
        {{ $this->pretplateForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">Spremi postavke pretplata</x-filament::button>
        </div>
    </form>

    <form wire:submit="spremiNumeraciju" class="mt-6">
        {{ $this->numeracijaForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">Spremi numeraciju</x-filament::button>
        </div>
    </form>

    <form wire:submit="spreminiFiskalizaciju" class="mt-6">
        {{ $this->fiskalizacijaForm }}
        <div class="mt-4 flex justify-end gap-3">
            <x-filament::button type="button" wire:click="testirajCertifikat" color="gray" icon="heroicon-o-shield-check">
                Testiraj certifikat
            </x-filament::button>
            <x-filament::button type="submit" color="warning">Spremi fiskalizaciju</x-filament::button>
        </div>
    </form>

    <form wire:submit="spremiEracun" class="mt-6">
        {{ $this->eracunForm }}
        <div class="mt-4 flex justify-end gap-3">
            <x-filament::button type="button" wire:click="testirajEracunCertifikat" color="gray" icon="heroicon-o-shield-check">
                Testiraj certifikat
            </x-filament::button>
            <x-filament::button type="button" wire:click="testirajMiddleware" color="gray" icon="heroicon-o-signal">
                Testiraj middleware
            </x-filament::button>
            <x-filament::button type="submit" color="info">Spremi eRačun postavke</x-filament::button>
        </div>
    </form>

    <form wire:submit="spremiZalihu" class="mt-6">
        {{ $this->zalihaForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">Spremi postavke skladišta</x-filament::button>
        </div>
    </form>

    {{-- Push obavijesti o novostima (preglednik: partials/push.blade.php, Android: NativePush) --}}
    <x-filament::section class="mt-6" icon="heroicon-o-bell-alert">
        <x-slot name="heading">Obavijesti o novostima</x-slot>
        <x-slot name="description">Obavijest na mobitelu ili računalu kad u plačko.app stigne nešto novo.</x-slot>

        @php($pushUkljuceno = auth()->user()->push_obavijesti)
        <div class="space-y-4 text-sm text-gray-700 dark:text-gray-300"
             x-data="{ stanje: 'ucitavam', poruka: '', radim: false }"
             x-init="stanje = window.plackoPush ? window.plackoPush.stanje() : 'nepodrzano'">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p>
                    @if ($pushUkljuceno)
                        Obavijesti su <strong class="text-success-600 dark:text-success-400">uključene</strong> za tvoj račun.
                    @else
                        Obavijesti su <strong>isključene</strong> na svim uređajima.
                    @endif
                </p>
                <x-filament::button wire:click="prebaciPushObavijesti" :color="$pushUkljuceno ? 'gray' : 'primary'" size="sm">
                    {{ $pushUkljuceno ? 'Isključi obavijesti' : 'Uključi obavijesti' }}
                </x-filament::button>
            </div>

            @if ($pushUkljuceno)
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                    <p x-show="stanje === 'aplikacija'" x-cloak>U Android aplikaciji obavijesti stižu same. Ako ih ne vidiš, dopusti obavijesti za Plačko u postavkama mobitela.</p>
                    <p x-show="stanje === 'nije-ukljuceno'" x-cloak>Na ovom uređaju obavijesti još nisu uključene.</p>
                    <p x-show="stanje === 'ukljuceno'" x-cloak>Ovaj uređaj prima obavijesti.</p>
                    <p x-show="stanje === 'blokirano'" x-cloak>Preglednik je blokirao obavijesti za placko.app. Dopusti ih u postavkama stranice (ikona lokota pokraj adrese) pa osvježi stranicu.</p>
                    <p x-show="stanje === 'ios-instaliraj'" x-cloak>Na iPhoneu obavijesti rade kad plačko.app dodaš na početni zaslon (Dijeli → Dodaj na početni zaslon) i otvoriš ga odande.</p>
                    <p x-show="stanje === 'nepodrzano'" x-cloak>Ovaj preglednik ne podržava obavijesti.</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-filament::button x-show="stanje === 'nije-ukljuceno'" x-cloak size="sm" icon="heroicon-o-bell"
                            x-on:click="radim = true; window.plackoPush.ukljuci().then((ok) => { stanje = window.plackoPush.stanje(); poruka = ok ? 'Obavijesti su uključene na ovom uređaju.' : 'Obavijesti nisu uključene.'; }).catch(() => poruka = 'Uključivanje nije uspjelo.').finally(() => radim = false)"
                            x-bind:disabled="radim">
                            Uključi na ovom uređaju
                        </x-filament::button>
                        <x-filament::button x-show="stanje === 'ukljuceno' || stanje === 'aplikacija'" x-cloak size="sm" color="gray" icon="heroicon-o-paper-airplane"
                            x-on:click="radim = true; window.plackoPush.proba().then((n) => poruka = n ? 'Probna obavijest poslana (uređaja: ' + n + ').' : 'Nijedan uređaj nije primio obavijest.').catch(() => poruka = 'Slanje nije uspjelo.').finally(() => radim = false)"
                            x-bind:disabled="radim">
                            Pošalji probnu obavijest
                        </x-filament::button>
                    </div>
                    <p x-show="poruka" x-text="poruka" x-cloak class="mt-2 font-medium"></p>
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- Opasna zona: trajno brisanje računa (zahtjev Google Playa i GDPR) --}}
    <x-filament::section class="mt-10 !ring-danger-600/40 dark:!ring-danger-500/40" icon="heroicon-o-exclamation-triangle" icon-color="danger">
        <x-slot name="heading">
            <span class="text-danger-600 dark:text-danger-400">Opasna zona</span>
        </x-slot>
        <x-slot name="description">Trajno brisanje korisničkog računa i svih podataka.</x-slot>

        <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <p>Brisanjem računa trajno se brišu tvoj korisnički račun i sve tvrtke kojima si jedini korisnik, sa svim računima, ponudama, klijentima, uslugama, skladištem, radnim nalozima, pretplatama, certifikatima i arhiviranim PDF-ovima. Tvrtke koje dijeliš s drugim korisnicima ostaju njima, a ti se samo uklanjaš iz njih.</p>
            <p><strong>Prije brisanja preuzmi izdane račune i izvještaje.</strong> Knjigovodstvene isprave, pa tako i izdani računi, po zakonu se čuvaju 11 godina, a nakon brisanja ih više ne možemo vratiti.</p>
        </div>

        <div class="mt-4">
            {{ $this->obrisiRacunAction }}
        </div>
    </x-filament::section>

</x-filament-panels::page>
