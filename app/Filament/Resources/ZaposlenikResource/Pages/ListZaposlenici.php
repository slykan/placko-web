<?php

namespace App\Filament\Resources\ZaposlenikResource\Pages;

use App\Filament\Resources\ZaposlenikResource;
use App\Models\RadniNalogRad;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListZaposlenici extends ListRecords
{
    protected static string $resource = ZaposlenikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Novi zaposlenik'),
            Actions\Action::make('izvoz_sati')
                ->label('Izvoz sati (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Select::make('mjesec')
                        ->label('Mjesec')
                        ->options(fn () => collect(range(0, 12))
                            ->mapWithKeys(fn (int $i) => [
                                now()->startOfMonth()->subMonths($i)->format('Y-m') => now()->startOfMonth()->subMonths($i)->format('m/Y'),
                            ])->all())
                        ->default(now()->format('Y-m'))
                        ->required(),
                ])
                ->modalHeading('Izvoz evidencije sati')
                ->modalDescription('Svi sati upisani na radnim nalozima u odabranom mjesecu, po zaposleniku, sa zbrojem.')
                ->modalSubmitActionLabel('Preuzmi CSV')
                ->action(fn (array $data) => $this->izvozSati($data['mjesec'])),
        ];
    }

    protected function izvozSati(string $mjesec)
    {
        $od = Carbon::createFromFormat('Y-m', $mjesec)->startOfMonth();
        $tvrtkaId = filament()->getTenant()->id;

        $radovi = RadniNalogRad::query()
            ->with(['zaposlenik', 'radniNalog.klijent'])
            ->whereHas('radniNalog', fn ($q) => $q->where('tvrtka_id', $tvrtkaId))
            ->whereBetween('datum', [$od->toDateString(), $od->copy()->endOfMonth()->toDateString()])
            ->get()
            ->sortBy(fn (RadniNalogRad $r) => ($r->zaposlenik ? $r->zaposlenik->prezime.' '.$r->zaposlenik->ime : '~').$r->datum->format('Ymd'));

        return response()->streamDownload(function () use ($radovi) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Zaposlenik', 'Datum', 'Vrijeme dolaska', 'Radni nalog', 'Klijent', 'Sati', 'Opis'], ';', '"', '');

            foreach ($radovi->groupBy(fn (RadniNalogRad $r) => $r->zaposlenik?->ime_prezime ?? '(bez zaposlenika)') as $ime => $grupa) {
                foreach ($grupa as $r) {
                    fputcsv($out, [
                        $ime,
                        $r->datum->format('d.m.Y.'),
                        substr((string) $r->vrijeme_dolaska, 0, 5),
                        $r->radniNalog->broj,
                        $r->radniNalog->klijent?->naziv,
                        number_format((float) $r->sati, 2, ',', ''),
                        $r->opis,
                    ], ';', '"', '');
                }
                fputcsv($out, [$ime.' — ukupno', '', '', '', '', number_format((float) $grupa->sum('sati'), 2, ',', ''), ''], ';', '"', '');
            }

            fclose($out);
        }, 'Sati_'.$mjesec.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
