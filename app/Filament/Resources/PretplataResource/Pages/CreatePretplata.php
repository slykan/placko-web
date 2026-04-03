<?php

namespace App\Filament\Resources\PretplataResource\Pages;

use App\Filament\Resources\PretplataResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePretplata extends CreateRecord
{
    protected static string $resource = PretplataResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tvrtka_id'] = filament()->getTenant()->id;
        $this->stavkeData = $data['stavke_data'] ?? [];
        unset($data['stavke_data']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->stavkeData as $stavka) {
            $this->record->stavke()->create([
                'usluga_id'  => $stavka['usluga_id'] ?? null,
                'naziv'      => $stavka['naziv'] ?? null,
                'opis'       => $stavka['opis'] ?? null,
                'kolicina'   => $stavka['kolicina'] ?? 1,
                'cijena'     => $stavka['cijena'] ?? 0,
                'pdv_stopa'  => $stavka['pdv_stopa'] ?? null,
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
