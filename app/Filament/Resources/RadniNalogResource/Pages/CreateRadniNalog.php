<?php

namespace App\Filament\Resources\RadniNalogResource\Pages;

use App\Filament\Resources\RadniNalogResource;
use App\Models\RadniNalog;
use Filament\Resources\Pages\CreateRecord;

class CreateRadniNalog extends CreateRecord
{
    protected static string $resource = RadniNalogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tvrtkaId = filament()->getTenant()->id;

        return array_merge($data, RadniNalog::generiraBroj($tvrtkaId), ['tvrtka_id' => $tvrtkaId]);
    }

    protected function afterCreate(): void
    {
        if ($this->record->nositelj_id) {
            $this->record->zaposlenici()->syncWithoutDetaching([$this->record->nositelj_id]);
        }
        $this->record->izracunajUkupno();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
