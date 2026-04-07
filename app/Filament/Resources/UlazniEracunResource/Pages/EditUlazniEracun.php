<?php

namespace App\Filament\Resources\UlazniEracunResource\Pages;

use App\Filament\Resources\UlazniEracunResource;
use Filament\Resources\Pages\EditRecord;

class EditUlazniEracun extends EditRecord
{
    protected static string $resource = UlazniEracunResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
