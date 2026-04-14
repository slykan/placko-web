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

</x-filament-panels::page>
