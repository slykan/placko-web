<?php

namespace App\Filament\Resources\DobavljacResource\Pages;

use App\Filament\Resources\DobavljacResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDobavljac extends EditRecord
{
    protected static string $resource = DobavljacResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši dobavljača'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
