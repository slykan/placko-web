<?php

namespace App\Filament\Resources\RadniNalogResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Filament\Resources\RadniNalogResource;
use App\Models\RadniNalog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRadniNalog extends EditRecord
{
    protected static string $resource = RadniNalogResource::class;

    protected function afterSave(): void
    {
        if ($this->record->nositelj_id) {
            $this->record->zaposlenici()->syncWithoutDetaching([$this->record->nositelj_id]);
        }
        $this->record->izracunajUkupno();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('pdf_zaposlenik')->label('PDF za zaposlenika')->icon('heroicon-o-user')
                    ->action(fn (RadniNalog $record) => response()->streamDownload(
                        fn () => print (RadniNalogResource::generirajPdf($record, false)), 'Radni_nalog_'.$record->broj.'.pdf'
                    )),
                Actions\Action::make('pdf_klijent')->label('PDF za klijenta')->icon('heroicon-o-document-text')
                    ->action(fn (RadniNalog $record) => response()->streamDownload(
                        fn () => print (RadniNalogResource::generirajPdf($record, true)), 'Radni_nalog_'.$record->broj.'_klijent.pdf'
                    )),
            ])->label('PDF')->icon('heroicon-o-arrow-down-tray')->color('gray')->button(),
            Actions\Action::make('otvori_racun')->label(fn (RadniNalog $record) => 'Račun '.$record->racun?->broj)
                ->icon('heroicon-o-banknotes')->color('gray')
                ->visible(fn (RadniNalog $record) => (bool) $record->racun_id)
                ->url(fn (RadniNalog $record) => RacunResource::getUrl('edit', ['record' => $record->racun_id])),
            Actions\DeleteAction::make()->label('Obriši nalog'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
