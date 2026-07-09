<?php

namespace App\Filament\Resources\DobavljacResource\Pages;

use App\Filament\Resources\DobavljacResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDobavljaci extends ListRecords
{
    protected static string $resource = DobavljacResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Novi dobavljač'),
        ];
    }
}
