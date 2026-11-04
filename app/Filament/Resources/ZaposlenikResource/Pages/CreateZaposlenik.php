<?php

namespace App\Filament\Resources\ZaposlenikResource\Pages;

use App\Filament\Resources\ZaposlenikResource;
use Filament\Resources\Pages\CreateRecord;

class CreateZaposlenik extends CreateRecord
{
    protected static string $resource = ZaposlenikResource::class;

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
