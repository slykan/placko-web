<?php

namespace App\Filament\Resources\SkladisteResource\Pages;

use App\Filament\Resources\SkladisteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSkladiste extends EditRecord
{
    protected static string $resource = SkladisteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši skladište'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
