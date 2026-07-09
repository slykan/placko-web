<?php

namespace App\Filament\Resources\UslugaResource\RelationManagers;

use App\Models\SkladisnaTransakcija;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransakcijeRelationManager extends RelationManager
{
    protected static string $relationship = 'transakcije';

    protected static ?string $title = 'Kartica proizvoda';

    protected static ?string $modelLabel = 'Transakcija';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('datum')->label('Datum')->date('d.m.Y.')->sortable(),
                Tables\Columns\TextColumn::make('tip')->label('Tip')
                    ->badge()
                    ->colors([
                        'success' => 'ulaz',
                        'danger' => 'izlaz',
                        'warning' => 'korekcija',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'ulaz' => 'Ulaz',
                        'izlaz' => 'Izlaz',
                        'korekcija' => 'Korekcija',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('kolicina')->label('Količina')
                    ->formatStateUsing(fn (SkladisnaTransakcija $t) => ($t->kolicina > 0 ? '+' : '').number_format((float) $t->kolicina, 2, ',', '.')),
                Tables\Columns\TextColumn::make('cijena')->label('Cijena')->money('EUR')->placeholder('—'),
                Tables\Columns\TextColumn::make('skladiste.naziv')->label('Skladište'),
                Tables\Columns\TextColumn::make('napomena')->label('Napomena')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('datum', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
