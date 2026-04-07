<?php

namespace App\Filament\Resources\UlazniEracunResource\Pages;

use App\Filament\Resources\UlazniEracunResource;
use App\Models\TvrtkaPostavke;
use App\Services\EracunService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUlazniEracuni extends ListRecords
{
    protected static string $resource = UlazniEracunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('dohvati')
                ->label('Dohvati s FINA-e')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Dohvati primljene eRačune')
                ->modalDescription('Provjeriti FINA servis za nove primljene eRačune?')
                ->action(function () {
                    $postavke = TvrtkaPostavke::where('tvrtka_id', filament()->getTenant()->id)->first();

                    try {
                        $rezultat = EracunService::dohvatiPrimljene($postavke);

                        Notification::make()
                            ->title('eRačuni dohvaćeni')
                            ->body('Pronađeno: ' . $rezultat['ukupno'] . ', novih: ' . $rezultat['novi'])
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Greška pri dohvaćanju eRačuna')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
