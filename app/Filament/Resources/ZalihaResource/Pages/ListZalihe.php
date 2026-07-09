<?php

namespace App\Filament\Resources\ZalihaResource\Pages;

use App\Filament\Resources\PrimkaResource;
use App\Filament\Resources\ZalihaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZalihe extends ListRecords
{
    protected static string $resource = ZalihaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('nova_primka')
                ->label('+ Nova primka')
                ->url(fn () => PrimkaResource::getUrl('create')),
        ];
    }
}
