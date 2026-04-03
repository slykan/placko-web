<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TvrtkaResource\Pages;
use App\Models\Tvrtka;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TvrtkaResource extends Resource
{
    protected static ?string $model = Tvrtka::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getNavigationLabel(): string
    {
        return filament()->getTenant()?->naziv ?? 'Moj obrt';
    }

    protected static ?string $modelLabel = 'Obrt / Tvrtka';

    protected static ?string $pluralModelLabel = 'Obrts i Tvrtke';

    protected static ?int $navigationSort = 1;

    // Tvrtka je sam tenant model - ne scopamo po tenantу
    protected static bool $isScopedToTenant = false;

    public static function isTenantSubscriptionRequired(\Filament\Panel $panel): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Osnovni podaci')->schema([
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('logos')
                    ->columnSpanFull(),

                TextInput::make('naziv')
                    ->label('Naziv')
                    ->required()
                    ->columnSpanFull(),

                Select::make('vrsta_poslovanja')
                    ->label('Vrsta poslovanja')
                    ->options(Tvrtka::vrstePoslovanja())
                    ->default('pausalni_obrt')
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),

                TextInput::make('vlasnik')
                    ->label('Vlasnik / direktor'),

                TextInput::make('oib')
                    ->label('OIB')
                    ->length(11),

                TextInput::make('nkd')
                    ->label('NKD'),

                TextInput::make('djelatnost')
                    ->label('Djelatnost'),
            ])->columns(2),

            Section::make('Adresa')->schema([
                TextInput::make('adresa')
                    ->label('Adresa'),

                TextInput::make('mjesto')
                    ->label('Mjesto'),

                TextInput::make('po_broj')
                    ->label('Poštanski broj'),
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
                TextInput::make('kontakt_broj')
                    ->label('Kontakt broj')
                    ->tel(),

                TextInput::make('email')
                    ->label('Email')
                    ->email(),

                TextInput::make('web_mjesto')
                    ->label('Web mjesto')
                    ->url(),

                TextInput::make('oznaka_operatera')
                    ->label('Oznaka operatera (npr. AF)'),
            ])->columns(2),

            Section::make('PDV')->schema([
                Toggle::make('u_sustavu_pdv')
                    ->label('U sustavu PDV-a')
                    ->helperText('Ako ste u sustavu PDV-a, PDV će se prikazivati na računima')
                    ->default(false)
                    ->inline(false),
            ]),

            Section::make('Napomena na računu')->schema([
                Textarea::make('napomena')
                    ->label('Napomena (prikazuje se na dnu računa)')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('naziv')
                    ->label('Naziv')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vrsta_poslovanja')
                    ->label('Vrsta')
                    ->formatStateUsing(fn ($state) => Tvrtka::vrstePoslovanja()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('oib')
                    ->label('OIB'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('kontakt_broj')
                    ->label('Telefon'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('users', fn ($q) => $q->where('users.id', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTvrtka::route('/'),
            'edit' => Pages\EditTvrtka::route('/{record}/edit'),
        ];
    }
}
