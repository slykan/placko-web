<?php

namespace App\Filament\Resources\PutniNalogResource\Pages;

use App\Filament\Resources\PutniNalogResource;
use App\Models\PutniNalog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPutniNalog extends EditRecord
{
    protected static string $resource = PutniNalogResource::class;

    protected function afterSave(): void
    {
        $this->record->izracunaj();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')->label('PDF putnog naloga')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(fn (PutniNalog $record) => response()->streamDownload(
                    fn () => print (PutniNalogResource::generirajPdf($record)), 'Putni_nalog_'.$record->broj.'.pdf'
                )),
            Actions\DeleteAction::make()->label('Obriši nalog')
                ->before(fn (PutniNalog $record) => $record->troskovi->each->delete()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
