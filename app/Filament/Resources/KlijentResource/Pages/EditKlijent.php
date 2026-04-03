<?php

namespace App\Filament\Resources\KlijentResource\Pages;

use App\Filament\Resources\KlijentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKlijent extends EditRecord
{
    protected static string $resource = KlijentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši klijenta'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
