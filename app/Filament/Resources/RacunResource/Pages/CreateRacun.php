<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\Racun;
use Filament\Resources\Pages\CreateRecord;

class CreateRacun extends CreateRecord
{
    protected static string $resource = RacunResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tvrtkaId = filament()->getTenant()->id;
        $generirani = Racun::generiraBroj($tvrtkaId);

        $data['tvrtka_id']   = $tvrtkaId;
        $data['redni_broj']  = $generirani['redni_broj'];
        $data['godina']      = $generirani['godina'];
        $data['broj']        = $generirani['broj'];
        $data['status']      = $data['status'] ?? 'final';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
