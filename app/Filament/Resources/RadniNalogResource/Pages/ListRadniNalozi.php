<?php

namespace App\Filament\Resources\RadniNalogResource\Pages;

use App\Filament\Resources\RadniNalogResource;
use App\Models\Ponuda;
use App\Models\RadniNalog;
use App\Models\RadniNalogStavka;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListRadniNalozi extends ListRecords
{
    protected static string $resource = RadniNalogResource::class;

    public function getTabs(): array
    {
        return [
            'otvoreni' => Tab::make('Otvoreni')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['otvoren', 'u_tijeku'])),
            'za_naplatu' => Tab::make('Za naplatu')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'zavrsen')->whereNull('racun_id')),
            'svi' => Tab::make('Svi'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Novi radni nalog'),
            Actions\Action::make('iz_ponude')
                ->label('Nalog iz ponude')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->form([
                    Select::make('ponuda_id')
                        ->label('Ponuda')
                        ->options(fn () => Ponuda::query()
                            ->with('klijent')
                            ->where('tvrtka_id', filament()->getTenant()->id)
                            ->latest('datum_izdavanja')
                            ->get()
                            ->mapWithKeys(fn (Ponuda $ponuda) => [
                                $ponuda->id => $ponuda->broj.' — '.$ponuda->klijent->naziv.($ponuda->prihvacena_at ? ' ✓' : ''),
                            ]))
                        ->searchable()
                        ->required(),
                ])
                ->modalHeading('Otvori radni nalog iz ponude')
                ->modalDescription('Klijent i stavke ponude kopiraju se na nalog kao materijal i usluge. Izvršitelje i opis posla dodaješ na sljedećem koraku.')
                ->modalSubmitActionLabel('Otvori nalog')
                ->action(function (array $data): void {
                    $tvrtka = filament()->getTenant();

                    $nalog = DB::transaction(function () use ($data, $tvrtka): RadniNalog {
                        $ponuda = Ponuda::query()
                            ->with(['stavke', 'klijent'])
                            ->where('tvrtka_id', $tvrtka->id)
                            ->findOrFail($data['ponuda_id']);

                        $nalog = RadniNalog::create([
                            ...RadniNalog::generiraBroj($tvrtka->id),
                            'tvrtka_id' => $tvrtka->id,
                            'klijent_id' => $ponuda->klijent_id,
                            'ponuda_id' => $ponuda->id,
                            'status' => 'otvoren',
                            'datum_otvaranja' => today(),
                            'lokacija' => trim($ponuda->klijent->adresa.', '.trim($ponuda->klijent->po_broj.' '.$ponuda->klijent->mjesto), ', ') ?: null,
                            'opis_posla' => 'Prema ponudi '.$ponuda->broj.($ponuda->rok_ispostave ? ' (rok ispostave: '.$ponuda->rok_ispostave.')' : '').'.',
                        ]);

                        foreach ($ponuda->stavke as $stavka) {
                            RadniNalogStavka::create([
                                ...$stavka->only(['usluga_id', 'naziv', 'opis', 'jedinica_mjere', 'kolicina', 'cijena', 'rabat_posto', 'pdv_stopa', 'ukupno', 'redni_broj']),
                                'radni_nalog_id' => $nalog->id,
                            ]);
                        }

                        $nalog->izracunajUkupno();

                        return $nalog;
                    });

                    Notification::make()
                        ->title('Radni nalog '.$nalog->broj.' otvoren je iz ponude')
                        ->success()
                        ->send();

                    $this->redirect(RadniNalogResource::getUrl('edit', ['record' => $nalog]));
                }),
        ];
    }
}
