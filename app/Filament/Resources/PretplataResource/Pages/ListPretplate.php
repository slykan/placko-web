<?php

namespace App\Filament\Resources\PretplataResource\Pages;

use App\Filament\Resources\PretplataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPretplate extends ListRecords
{
    protected static string $resource = PretplataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Nova pretplata'),
        ];
    }
}
