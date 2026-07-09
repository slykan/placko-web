<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DobavljacResource\Pages;
use App\Models\Dobavljac;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DobavljacResource extends Resource
{
    protected static ?string $model = Dobavljac::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Dobavljači';

    protected static ?string $modelLabel = 'Dobavljač';

    protected static ?string $pluralModelLabel = 'Dobavljači';

    protected static ?string $slug = 'dobavljaci';

    protected static ?string $navigationGroup = 'Skladište';

    protected static ?int $navigationSort = 7;

    protected static ?string $tenantRelationshipName = 'dobavljaci';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('naziv')
                ->label('Naziv')
                ->required()
                ->columnSpanFull(),

            TextInput::make('oib')
                ->label('OIB')
                ->length(11),

            TextInput::make('kontakt_osoba')
                ->label('Kontakt osoba'),

            TextInput::make('adresa')
                ->label('Adresa'),

            TextInput::make('mjesto')
                ->label('Mjesto'),

            TextInput::make('email')
                ->label('Email')
                ->email(),

            TextInput::make('kontakt_broj')
                ->label('Kontakt broj'),

            Textarea::make('napomena')
                ->label('Napomena')
                ->rows(2)
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

                Tables\Columns\TextColumn::make('oib')
                    ->label('OIB')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('kontakt_osoba')
                    ->label('Kontakt osoba'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),

                Tables\Columns\TextColumn::make('kontakt_broj')
                    ->label('Kontakt broj'),
            ])
            ->defaultSort('naziv')
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
            'index' => Pages\ListDobavljaci::route('/'),
            'create' => Pages\CreateDobavljac::route('/create'),
            'edit' => Pages\EditDobavljac::route('/{record}/edit'),
        ];
    }
}
