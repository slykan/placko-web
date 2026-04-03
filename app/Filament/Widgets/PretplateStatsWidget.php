<?php

namespace App\Filament\Widgets;

use App\Models\Pretplata;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PretplateStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $tvrtkaId = filament()->getTenant()?->id;

        $aktivne   = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'aktivna')->count();
        $neaktivne = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'neaktivna')->count();
        $istekle   = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'istekla')->count();
        $uskoro    = Pretplata::where('tvrtka_id', $tvrtkaId)
            ->where('status', 'aktivna')
            ->whereDate('datum_isteka', '<=', now()->addDays(30))
            ->count();

        $iznosAktivne = Pretplata::where('tvrtka_id', $tvrtkaId)->where('status', 'aktivna')->sum('ukupno');

        return [
            Stat::make('Aktivne pretplate', $aktivne)
                ->description(number_format($iznosAktivne, 2, ',', '.') . ' € / period')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('success'),

            Stat::make('Ističu za 30 dana', $uskoro)
                ->description('Potrebna obnova')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($uskoro > 0 ? 'warning' : 'gray'),

            Stat::make('Neaktivne / Istekle', $neaktivne + $istekle)
                ->description("{$neaktivne} neaktivnih · {$istekle} isteklih")
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('gray'),
        ];
    }
}
