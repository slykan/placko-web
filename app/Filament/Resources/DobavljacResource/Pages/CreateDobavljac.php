<?php

namespace App\Filament\Resources\DobavljacResource\Pages;

use App\Filament\Resources\DobavljacResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDobavljac extends CreateRecord
{
    protected static string $resource = DobavljacResource::class;

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
