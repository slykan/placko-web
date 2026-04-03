<?php

namespace App\Filament\Resources\KlijentResource\Pages;

use App\Filament\Resources\KlijentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKlijenti extends ListRecords
{
    protected static string $resource = KlijentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Dodaj klijenta'),
        ];
    }
}
