<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\Ponuda;
use App\Models\Racun;
use App\Models\RacunStavka;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListRacuni extends ListRecords
{
    protected static string $resource = RacunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Nova faktura'),
            Actions\Action::make('iz_ponude')
                ->label('Račun iz ponude')
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
                                $ponuda->id => $ponuda->broj.' — '.$ponuda->klijent->naziv,
                            ]))
                        ->searchable()
                        ->required(),
                ])
                ->modalHeading('Izradi račun iz ponude')
                ->modalDescription('Klijent, stavke, količine, cijene, rabati i PDV kopirat će se u novi račun.')
                ->modalSubmitActionLabel('Izradi račun')
                ->action(function (array $data): void {
                    $tvrtka = filament()->getTenant();

                    $racun = DB::transaction(function () use ($data, $tvrtka): Racun {
                        $ponuda = Ponuda::query()
                            ->with('stavke')
                            ->where('tvrtka_id', $tvrtka->id)
                            ->findOrFail($data['ponuda_id']);
                        $broj = Racun::generiraBroj($tvrtka->id);

                        $racun = Racun::create([
                            'tvrtka_id' => $tvrtka->id,
                            'klijent_id' => $ponuda->klijent_id,
                            'broj' => $broj['broj'],
                            'redni_broj' => $broj['redni_broj'],
                            'godina' => $broj['godina'],
                            'datum_izdavanja' => today(),
                            'vrijeme_izdavanja' => now()->format('H:i'),
                            'datum_dospijeca' => today(),
                            'datum_isporuke' => today(),
                            'mjesto_izdavanja' => $ponuda->mjesto_izdavanja ?? $tvrtka->mjesto,
                            'nacin_placanja' => 'transakcijski',
                            'napomena' => $ponuda->napomena,
                            'status' => 'final',
                        ]);

                        foreach ($ponuda->stavke as $stavka) {
                            RacunStavka::create([
                                'racun_id' => $racun->id,
                                'usluga_id' => $stavka->usluga_id,
                                'naziv' => $stavka->naziv,
                                'opis' => $stavka->opis,
                                'jedinica_mjere' => $stavka->jedinica_mjere,
                                'kolicina' => $stavka->kolicina,
                                'cijena' => $stavka->cijena,
                                'rabat_posto' => $stavka->rabat_posto,
                                'pdv_stopa' => $stavka->pdv_stopa,
                                'ukupno' => $stavka->ukupno,
                                'redni_broj' => $stavka->redni_broj,
                            ]);
                        }

                        $racun->load('stavke');
                        $racun->izracunajUkupno();

                        return $racun;
                    });

                    Notification::make()
                        ->title('Račun '.$racun->broj.' izrađen je iz ponude')
                        ->success()
                        ->send();

                    $this->redirect(RacunResource::getUrl('edit', ['record' => $racun]));
                }),
        ];
    }
}
