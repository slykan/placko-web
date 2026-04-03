<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRacun extends EditRecord
{
    protected static string $resource = RacunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši račun'),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
