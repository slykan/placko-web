<?php

namespace App\Filament\Resources\PutniNalogResource\Pages;

use App\Filament\Resources\PutniNalogResource;
use App\Models\PutniNalog;
use App\Models\RadniNalog;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPutniNalozi extends ListRecords
{
    protected static string $resource = PutniNalogResource::class;

    public function getTabs(): array
    {
        return [
            'otvoreni' => Tab::make('Otvoreni')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'otvoren')),
            'za_isplatu' => Tab::make('Za isplatu')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'obracunat')),
            'svi' => Tab::make('Svi'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Novi putni nalog'),
            Actions\Action::make('iz_radnog')->label('Iz radnog naloga')->icon('heroicon-o-wrench-screwdriver')->color('gray')
                ->form([
                    Select::make('radni_nalog_id')->label('Radni nalog')->required()->searchable()
                        ->options(fn () => RadniNalog::where('tvrtka_id', filament()->getTenant()->id)
                            ->latest('datum_otvaranja')->limit(200)->get()
                            ->mapWithKeys(fn (RadniNalog $n) => [$n->id => $n->broj.($n->lokacija ? ' — '.$n->lokacija : '')])),
                ])
                ->modalHeading('Putni nalog iz radnog naloga')
                ->modalDescription('Putnik (nositelj posla), odredište i svrha preuzimaju se s radnog naloga.')
                ->modalSubmitActionLabel('Otvori putni nalog')
                ->action(function (array $data) {
                    $tvrtka = filament()->getTenant();
                    $rn = RadniNalog::with('nositelj', 'klijent')->where('tvrtka_id', $tvrtka->id)->findOrFail($data['radni_nalog_id']);
                    $z = $rn->nositelj;

                    $nalog = PutniNalog::create([
                        ...PutniNalog::generiraBroj($tvrtka->id),
                        'tvrtka_id' => $tvrtka->id,
                        'radni_nalog_id' => $rn->id,
                        'zaposlenik_id' => $z?->id,
                        'putnik' => $z?->ime_prezime ?? auth()->user()->name,
                        'radno_mjesto' => $z?->radno_mjesto,
                        'registracija' => $z?->registracija,
                        'vozilo' => $z?->vozilo,
                        'datum_naloga' => today(),
                        'nalogodavac' => auth()->user()->name,
                        'odrediste' => $rn->lokacija ?: ($rn->klijent?->mjesto ?? '—'),
                        'svrha' => 'Radni nalog '.$rn->broj.($rn->klijent ? ' ('.$rn->klijent->naziv.')' : '').($rn->predmet ? ' — '.$rn->predmet : ''),
                    ]);
                    $nalog->izracunaj();

                    $this->redirect(PutniNalogResource::getUrl('edit', ['record' => $nalog]));
                }),
        ];
    }
}
