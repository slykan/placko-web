<?php

namespace App\Filament\Resources\UslugaResource\Pages;

use App\Filament\Resources\UslugaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsluge extends ListRecords
{
    protected static string $resource = UslugaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Dodaj uslugu'),
        ];
    }
}
