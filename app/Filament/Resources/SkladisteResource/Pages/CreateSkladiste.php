<?php

namespace App\Filament\Resources\SkladisteResource\Pages;

use App\Filament\Resources\SkladisteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSkladiste extends CreateRecord
{
    protected static string $resource = SkladisteResource::class;

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
