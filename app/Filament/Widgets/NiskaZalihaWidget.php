<?php

namespace App\Filament\Widgets;

use App\Models\Usluga;
use App\Models\Zaliha;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Proizvodi iz modula Skladište čija je količina pala na ili ispod minimalne zalihe.
 * Prikazuje se samo tvrtkama koje prate zalihe (barem jedan proizvod s praćenjem i minimalnom zalihom).
 */
class NiskaZalihaWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $tvrtkaId = filament()->getTenant()?->id;

        return $tvrtkaId && Usluga::where('tvrtka_id', $tvrtkaId)
            ->where('prati_zalihu', true)
            ->whereNotNull('minimalna_zaliha')
            ->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Niska zaliha')
            ->description('Proizvodi čija je količina na skladištu pala na minimalnu zalihu ili ispod nje.')
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('usluga.naziv')->label('Proizvod'),
                Tables\Columns\TextColumn::make('skladiste.naziv')->label('Skladište'),
                Tables\Columns\TextColumn::make('kolicina')->label('Trenutna količina')
                    ->formatStateUsing(fn (Zaliha $z) => number_format((float) $z->kolicina, 2, ',', '.').' '.($z->usluga->jedinica_mjere ?? 'kom'))
                    ->color('danger'),
                Tables\Columns\TextColumn::make('usluga.minimalna_zaliha')->label('Minimalna zaliha'),
            ])
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateHeading('Sve zalihe su u redu')
            ->emptyStateDescription('Nijedan proizvod nije pao ispod minimalne zalihe.')
            ->paginated([5, 10, 25]);
    }

    protected function getTableQuery(): Builder
    {
        $tvrtkaId = filament()->getTenant()?->id;

        return Zaliha::query()
            ->join('usluge', 'usluge.id', '=', 'zalihe.usluga_id')
            ->where('zalihe.tvrtka_id', $tvrtkaId)
            ->where('usluge.prati_zalihu', true)
            ->whereNotNull('usluge.minimalna_zaliha')
            ->whereColumn('zalihe.kolicina', '<=', 'usluge.minimalna_zaliha')
            ->with(['usluga', 'skladiste'])
            ->select('zalihe.*');
    }
}
