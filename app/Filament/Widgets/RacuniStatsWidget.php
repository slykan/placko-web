<?php

namespace App\Filament\Widgets;

use App\Models\Racun;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RacuniStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tvrtkaId = filament()->getTenant()?->id;

        $ukupno    = Racun::where('tvrtka_id', $tvrtkaId)->count();
        $placeno   = Racun::where('tvrtka_id', $tvrtkaId)->whereNotNull('placen_at')->count();
        $neplaceno = Racun::where('tvrtka_id', $tvrtkaId)->whereNull('placen_at')->count();

        $iznosUkupno    = Racun::where('tvrtka_id', $tvrtkaId)->sum('ukupno');
        $iznosPlaceno   = Racun::where('tvrtka_id', $tvrtkaId)->whereNotNull('placen_at')->sum('ukupno');
        $iznosNeplaceno = Racun::where('tvrtka_id', $tvrtkaId)->whereNull('placen_at')->sum('ukupno');

        return [
            Stat::make('Izdani računi', $ukupno)
                ->description('Ukupno: ' . number_format($iznosUkupno, 2, ',', '.') . ' €')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Plaćeno', $placeno)
                ->description(number_format($iznosPlaceno, 2, ',', '.') . ' €')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Neplaćeno', $neplaceno)
                ->description(number_format($iznosNeplaceno, 2, ',', '.') . ' €')
                ->descriptionIcon('heroicon-o-clock')
                ->color($neplaceno > 0 ? 'danger' : 'gray'),
        ];
    }
}
