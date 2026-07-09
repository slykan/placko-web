<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SkladisteResource\Pages;
use App\Models\Skladiste;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SkladisteResource extends Resource
{
    protected static ?string $model = Skladiste::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Skladišta';

    protected static ?string $modelLabel = 'Skladište';

    protected static ?string $pluralModelLabel = 'Skladišta';

    protected static ?string $slug = 'skladista';

    protected static ?int $navigationSort = 6;

    protected static ?string $tenantRelationshipName = 'skladista';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('naziv')
                ->label('Naziv skladišta')
                ->required()
                ->columnSpanFull(),

            TextInput::make('adresa')
                ->label('Adresa')
                ->columnSpanFull(),

            Toggle::make('zadano')
                ->label('Zadano skladište')
                ->helperText('Računi i ponude automatski oduzimaju zalihu iz zadanog skladišta.')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('naziv')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('adresa')
                    ->label('Adresa')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('zadano')
                    ->label('Zadano')
                    ->boolean(),
            ])
            ->defaultSort('naziv')
            ->actions([
                Tables\Actions\EditAction::make()->label('Uredi'),
                Tables\Actions\DeleteAction::make()->label('Obriši'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Obriši označena'),
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
            'index' => Pages\ListSkladista::route('/'),
            'create' => Pages\CreateSkladiste::route('/create'),
            'edit' => Pages\EditSkladiste::route('/{record}/edit'),
        ];
    }
}
