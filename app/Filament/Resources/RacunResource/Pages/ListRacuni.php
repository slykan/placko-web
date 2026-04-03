<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\Racun;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRacuni extends ListRecords
{
    protected static string $resource = RacunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Nova faktura'),
        ];
    }
}
