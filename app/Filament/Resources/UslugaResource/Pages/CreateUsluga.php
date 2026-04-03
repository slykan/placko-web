<?php

namespace App\Filament\Resources\UslugaResource\Pages;

use App\Filament\Resources\UslugaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUsluga extends CreateRecord
{
    protected static string $resource = UslugaResource::class;

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
