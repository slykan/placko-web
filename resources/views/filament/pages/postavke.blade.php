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
