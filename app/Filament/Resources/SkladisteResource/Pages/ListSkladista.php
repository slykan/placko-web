<?php

namespace App\Filament\Resources\SkladisteResource\Pages;

use App\Filament\Resources\SkladisteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSkladista extends ListRecords
{
    protected static string $resource = SkladisteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Novo skladište'),
        ];
    }
}
