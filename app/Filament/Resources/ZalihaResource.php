<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZalihaResource\Pages;
use App\Models\Zaliha;
use App\Services\ZalihaService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ZalihaResource extends Resource
{
    protected static ?string $model = Zaliha::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Zalihe';

    protected static ?string $modelLabel = 'Zaliha';

    protected static ?string $pluralModelLabel = 'Zalihe';

    protected static ?string $slug = 'zalihe';

    protected static ?string $navigationGroup = 'Skladište';

    protected static ?int $navigationSort = 9;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('usluga.naziv')->label('Proizvod')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('skladiste.naziv')->label('Skladište')->sortable(),
            Tables\Columns\TextColumn::make('kolicina')->label('Količina')
                ->formatStateUsing(fn (Zaliha $z) => number_format((float) $z->kolicina, 2, ',', '.').' '.($z->usluga->jedinica_mjere ?? 'kom'))
                ->color(fn (Zaliha $z) => $z->usluga->minimalna_zaliha !== null && $z->kolicina <= $z->usluga->minimalna_zaliha ? 'danger' : null)
                ->sortable(),
            Tables\Columns\TextColumn::make('prosjecna_nabavna_cijena')->label('Prosj. nabavna cijena')->money('EUR')->sortable(),
            Tables\Columns\TextColumn::make('vrijednost')->label('Vrijednost')->money('EUR')
                ->getStateUsing(fn (Zaliha $z) => $z->vrijednost),
        ])->defaultSort('kolicina')
            ->actions([
                Tables\Actions\Action::make('korekcija')
                    ->label('Korekcija')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Ručna korekcija količine')
                    ->form(fn (Zaliha $z) => [
                        TextInput::make('nova_kolicina')->label('Nova količina')
                            ->numeric()->required()->step(0.001)
                            ->default($z->kolicina),
                        Textarea::make('napomena')->label('Napomena')->rows(2),
                    ])
                    ->modalHeading(fn (Zaliha $z) => 'Korekcija zalihe — '.$z->usluga->naziv)
                    ->action(function (Zaliha $z, array $data) {
                        $delta = round((float) $data['nova_kolicina'] - (float) $z->kolicina, 3);
                        if (abs($delta) < 0.001) {
                            return;
                        }
                        ZalihaService::zabiljezi($z->usluga, $z->skladiste, 'korekcija', $delta, null, [
                            'napomena' => $data['napomena'] ?? null,
                        ]);
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tvrtka_id', filament()->getTenant()->id)
            ->with(['usluga', 'skladiste']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZalihe::route('/'),
        ];
    }
}
