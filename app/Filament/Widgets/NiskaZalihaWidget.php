<?php

namespace App\Filament\Widgets;

use App\Models\Zaliha;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class NiskaZalihaWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Niska zaliha')
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('usluga.naziv')->label('Proizvod'),
                Tables\Columns\TextColumn::make('skladiste.naziv')->label('Skladište'),
                Tables\Columns\TextColumn::make('kolicina')->label('Trenutna količina')
                    ->formatStateUsing(fn (Zaliha $z) => number_format((float) $z->kolicina, 2, ',', '.').' '.($z->usluga->jedinica_mjere ?? 'kom'))
                    ->color('danger'),
                Tables\Columns\TextColumn::make('usluga.minimalna_zaliha')->label('Minimalna zaliha'),
            ])
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
