<?php

namespace App\Filament\Resources\PonudaResource\Pages;

use App\Filament\Resources\PonudaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPonude extends ListRecords
{
    protected static string $resource = PonudaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('+ Nova ponuda')];
    }
}
