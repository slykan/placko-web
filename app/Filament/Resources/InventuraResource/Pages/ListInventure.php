<?php

namespace App\Filament\Resources\InventuraResource\Pages;

use App\Filament\Resources\InventuraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventure extends ListRecords
{
    protected static string $resource = InventuraResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('+ Nova inventura')];
    }
}
