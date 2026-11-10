<?php

namespace App\Filament\Resources\LokoVoznjaResource\Pages;

use App\Filament\Resources\LokoVoznjaResource;
use App\Models\LokoVoznja;
use App\Models\Zaposlenik;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageLokoVoznje extends ManageRecords
{
    protected static string $resource = LokoVoznjaResource::class;

    protected function getHeaderActions(): array
    {
        $tvrtka = fn () => filament()->getTenant();

        return [
            Actions\CreateAction::make()->label('+ Nova vožnja')
                ->mutateFormDataUsing(fn (array $data) => $data + ['tvrtka_id' => $tvrtka()->id]),

            Actions\Action::make('uvezi')->label('Uvezi iz radnih naloga')->icon('heroicon-o-arrow-down-on-square-stack')->color('gray')
                ->form([
                    Select::make('zaposlenik_id')->label('Zaposlenik')->required()->searchable()
                        ->options(fn () => Zaposlenik::opcije($tvrtka()->id)),
                    Select::make('mjesec')->label('Mjesec')->required()->options(LokoVoznjaResource::mjeseci())
                        ->default(now()->format('Y-m')),
                    TextInput::make('polaziste')->label('Polazište (sjedište, adresa)')
                        ->default(fn () => trim($tvrtka()->adresa.', '.$tvrtka()->mjesto, ', ')),
                    TextInput::make('registracija')->label('Registarska oznaka')
                        ->helperText('Prazno = registracija upisana kod zaposlenika.'),
                ])
                ->modalHeading('Uvoz loko vožnje iz radnih naloga')
                ->modalDescription('Svaki dolazak zaposlenika na radni nalog u tom mjesecu postaje redak s relacijom i svrhom. Kilometre upisuješ ti (ili ih ispravi kasnije).')
                ->modalSubmitActionLabel('Uvezi')
                ->action(function (array $data) use ($tvrtka) {
                    $n = LokoVoznjaResource::uveziIzRadnihNaloga($tvrtka()->id, (int) $data['zaposlenik_id'], $data['mjesec'], $data['polaziste'] ?? null, $data['registracija'] ?? null);

                    Notification::make()
                        ->title($n ? "Uvezeno vožnji: {$n}" : 'Nema novih dolazaka za uvoz')
                        ->body($n ? 'Upiši prijeđene kilometre (crveno označeni redovi).' : 'Za taj mjesec nema dolazaka na radnim nalozima ili su već uvezeni.')
                        ->{$n ? 'success' : 'warning'}()->send();
                }),

            Actions\Action::make('pdf')->label('Mjesečna evidencija (PDF)')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->form([
                    Select::make('mjesec')->label('Mjesec')->required()->options(LokoVoznjaResource::mjeseci())->default(now()->format('Y-m')),
                    Select::make('vozac')->label('Vozač')->required()->searchable()
                        ->options(fn () => LokoVoznja::where('tvrtka_id', $tvrtka()->id)->distinct()->orderBy('putnik')->pluck('putnik', 'putnik')),
                ])
                ->modalSubmitActionLabel('Preuzmi PDF')
                ->action(function (array $data) use ($tvrtka) {
                    $pdf = LokoVoznjaResource::generirajPdf($tvrtka()->id, $data['mjesec'], null, $data['vozac']);

                    return response()->streamDownload(fn () => print ($pdf),
                        'Loko_voznja_'.$data['mjesec'].'_'.\Illuminate\Support\Str::slug($data['vozac']).'.pdf');
                }),
        ];
    }
}
