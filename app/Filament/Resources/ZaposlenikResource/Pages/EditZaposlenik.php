<?php

namespace App\Filament\Resources\ZaposlenikResource\Pages;

use App\Filament\Resources\ZaposlenikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZaposlenik extends EditRecord
{
    protected static string $resource = ZaposlenikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('novi_link')
                ->label('Novi link')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Napraviti novi osobni link?')
                ->modalDescription('Stari link „Moji nalozi” i linkovi iz već poslanih mailova prestaju raditi. Novi link pošalji zaposleniku.')
                ->action(function () {
                    $this->record->noviPortalToken();
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
            Actions\DeleteAction::make()->label('Obriši zaposlenika'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
