<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KlijentResource\Pages;
use App\Models\Klijent;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KlijentResource extends Resource
{
    protected static ?string $model = Klijent::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Klijenti';

    protected static ?string $modelLabel = 'Klijent';

    protected static ?string $pluralModelLabel = 'Klijenti';

    protected static ?int $navigationSort = 2;

    protected static ?string $tenantRelationshipName = 'klijenti';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Osnovni podaci')->schema([
                TextInput::make('naziv')
                    ->label('Naziv')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('vlasnik')
                    ->label('Vlasnik'),

                TextInput::make('oib')
                    ->label('OIB')
                    ->length(11),

                TextInput::make('djelatnost')
                    ->label('Djelatnost')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Adresa')->schema([
                TextInput::make('adresa')
                    ->label('Adresa'),

                TextInput::make('mjesto')
                    ->label('Mjesto'),

                TextInput::make('po_broj')
                    ->label('PO broj'),
            ])->columns(3),

            Section::make('Bankovni podaci')->schema([
                TextInput::make('iban')
                    ->label('IBAN'),

                TextInput::make('swift')
                    ->label('SWIFT'),

                TextInput::make('banka')
                    ->label('Banka'),
            ])->columns(3),

            Section::make('Kontakt')->schema([
                TextInput::make('email')
                    ->label('Email')
                    ->email(),

                TextInput::make('kontakt_broj')
                    ->label('Kontakt broj')
                    ->tel(),

                TextInput::make('web_mjesto')
                    ->label('Web mjesto')
                    ->url(),
            ])->columns(3),
        ]);
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
                    ->searchable(),

                Tables\Columns\TextColumn::make('mjesto')
                    ->label('Mjesto')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('po_broj')
                    ->label('PO broj'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email kopiran'),

                Tables\Columns\TextColumn::make('kontakt_broj')
                    ->label('Telefon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('iban')
                    ->label('IBAN')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('naziv')
            ->filters([
                Filter::make('ima_iban')
                    ->label('Ima IBAN')
                    ->query(fn (Builder $query) => $query->whereNotNull('iban')->where('iban', '!=', '')),

                Filter::make('ima_email')
                    ->label('Ima email')
                    ->query(fn (Builder $query) => $query->whereNotNull('email')->where('email', '!=', '')),

                Tables\Filters\SelectFilter::make('mjesto')
                    ->label('Mjesto')
                    ->options(
                        fn () => Klijent::query()
                            ->whereNotNull('mjesto')
                            ->distinct()
                            ->orderBy('mjesto')
                            ->pluck('mjesto', 'mjesto')
                            ->toArray()
                    ),
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
            'index' => Pages\ListKlijenti::route('/'),
            'create' => Pages\CreateKlijent::route('/create'),
            'edit' => Pages\EditKlijent::route('/{record}/edit'),
        ];
    }
}
