<?php

namespace App\Filament\Resources\UlazniEracunResource\Pages;

use App\Filament\Resources\UlazniEracunResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUlazniEracun extends ViewRecord
{
    protected static string $resource = UlazniEracunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Uredi status / napomenu'),
        ];
    }
}
