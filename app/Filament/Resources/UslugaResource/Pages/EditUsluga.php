<?php

namespace App\Filament\Resources\UslugaResource\Pages;

use App\Filament\Resources\UslugaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsluga extends EditRecord
{
    protected static string $resource = UslugaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši uslugu'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
