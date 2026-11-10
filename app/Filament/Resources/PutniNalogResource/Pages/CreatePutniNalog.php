<?php

namespace App\Filament\Resources\PutniNalogResource\Pages;

use App\Filament\Resources\PutniNalogResource;
use App\Models\PutniNalog;
use Filament\Resources\Pages\CreateRecord;

class CreatePutniNalog extends CreateRecord
{
    protected static string $resource = PutniNalogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tvrtkaId = filament()->getTenant()->id;

        return array_merge($data, PutniNalog::generiraBroj($tvrtkaId), ['tvrtka_id' => $tvrtkaId]);
    }

    protected function afterCreate(): void
    {
        $this->record->izracunaj();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
