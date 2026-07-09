<?php

namespace App\Filament\Resources\PrimkaResource\Pages;

use App\Filament\Resources\PrimkaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrimke extends ListRecords
{
    protected static string $resource = PrimkaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('+ Nova primka')];
    }
}
