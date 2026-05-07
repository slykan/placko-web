<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\Racun;
use App\Models\RacunStavka;
use Filament\Resources\Pages\CreateRecord;

class CreateRacun extends CreateRecord
{
    protected static string $resource = RacunResource::class;

    protected array $pendingStavke = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tvrtkaId = filament()->getTenant()->id;
        $generirani = Racun::generiraBroj($tvrtkaId);

        $data['tvrtka_id']  = $tvrtkaId;
        $data['redni_broj'] = $generirani['redni_broj'];
        $data['godina']     = $generirani['godina'];
        $data['broj']       = $generirani['broj'];
        $data['status']     = $data['status'] ?? 'final';

        $this->pendingStavke = $data['stavke'] ?? [];
        unset($data['stavke']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->pendingStavke as $index => $stavka) {
            RacunStavka::create([
                'racun_id'       => $this->record->id,
                'usluga_id'      => $stavka['usluga_id'] ?? null,
                'naziv'          => $stavka['naziv'] ?? '',
                'opis'           => $stavka['opis'] ?? null,
                'jedinica_mjere' => $stavka['jedinica_mjere'] ?? 'kom',
                'kolicina'       => $stavka['kolicina'] ?? 1,
                'cijena'         => $stavka['cijena'] ?? 0,
                'rabat_posto'    => $stavka['rabat_posto'] ?? 0,
                'pdv_stopa'      => $stavka['pdv_stopa'] ?? null,
                'ukupno'         => $stavka['ukupno'] ?? 0,
                'redni_broj'     => $index + 1,
            ]);
        }

        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
