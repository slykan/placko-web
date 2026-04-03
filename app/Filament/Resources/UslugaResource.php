<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UslugaResource\Pages;
use App\Models\Usluga;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UslugaResource extends Resource
{
    protected static ?string $model = Usluga::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Usluge';

    protected static ?string $modelLabel = 'Usluga';

    protected static ?string $pluralModelLabel = 'Usluge';

    protected static ?int $navigationSort = 3;

    protected static ?string $tenantRelationshipName = 'usluge';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('naziv')
                ->label('Naziv usluge')
                ->required()
                ->columnSpanFull(),

            TextInput::make('jedinica_mjere')
                ->label('Jedinica mjere')
                ->placeholder('npr. sat, kom, usluga')
                ->default('kom'),

            Select::make('pdv_stopa')
                ->label('PDV (%)')
                ->options(Usluga::pdvStope())
                ->default(null)
                ->native(false)
                ->helperText('Svaka usluga/proizvod može imati različitu stopu PDV-a'),

            TextInput::make('cijena')
                ->label('Cijena (€)')
                ->numeric()
                ->default(0)
                ->step(0.01)
                ->prefix('€'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('naziv')
                    ->label('Naziv usluge')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jedinica_mjere')
                    ->label('Jed. mjere')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cijena')
                    ->label('Cijena (€)')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pdv_stopa')
                    ->label('PDV (%)')
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : $state . ' %')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cijena_s_pdvom')
                    ->label('Cijena s PDV (€)')
                    ->money('EUR')
                    ->getStateUsing(fn (Usluga $record) => $record->cijena_s_pdvom),
            ])
            ->defaultSort('naziv')
            ->filters([
                Tables\Filters\SelectFilter::make('pdv_stopa')
                    ->label('PDV stopa')
                    ->options([
                        'null_stopa' => 'Bez PDV-a',
                        '0'   => '0 %',
                        '5'   => '5 %',
                        '13'  => '13 %',
                        '25'  => '25 %',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }
                        if ($data['value'] === 'null_stopa') {
                            return $query->whereNull('pdv_stopa');
                        }

                        return $query->where('pdv_stopa', $data['value']);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Uredi'),
                Tables\Actions\DeleteAction::make()->label('Obriši'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsluge::route('/'),
            'create' => Pages\CreateUsluga::route('/create'),
            'edit'   => Pages\EditUsluga::route('/{record}/edit'),
        ];
    }
}
