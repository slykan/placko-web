<?php

namespace App\Filament\Resources\ZaposlenikResource\Pages;

use App\Filament\Resources\ZaposlenikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZaposlenik extends EditRecord
{
    protected static string $resource = ZaposlenikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši zaposlenika'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
