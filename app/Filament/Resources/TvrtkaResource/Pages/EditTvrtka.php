<?php

namespace App\Filament\Resources\TvrtkaResource\Pages;

use App\Filament\Resources\TvrtkaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTvrtka extends EditRecord
{
    protected static string $resource = TvrtkaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
