<?php

namespace App\Filament\Resources\KlijentResource\Pages;

use App\Filament\Resources\KlijentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKlijent extends CreateRecord
{
    protected static string $resource = KlijentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tvrtka_id'] = filament()->getTenant()->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
