<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZaposlenikResource\Pages;
use App\Models\Zaposlenik;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ZaposlenikResource extends Resource
{
    protected static ?string $model = Zaposlenik::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Zaposlenici';

    protected static ?string $modelLabel = 'Zaposlenik';

    protected static ?string $pluralModelLabel = 'Zaposlenici';

    protected static ?string $slug = 'zaposlenici';

    protected static ?int $navigationSort = 1;

    protected static ?string $tenantRelationshipName = 'zaposlenici';

    public static function can(string $action, ?Model $record = null): bool
    {
        return RadniNalogResource::dostupno() && parent::can($action, $record);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('ime')
                ->label('Ime')
                ->required()
                ->maxLength(100),

            TextInput::make('prezime')
                ->label('Prezime')
                ->required()
                ->maxLength(150),

            TextInput::make('radno_mjesto')
                ->label('Radno mjesto')
                ->placeholder('npr. serviser, monter, električar'),

            TextInput::make('cijena_sata')
                ->label('Cijena sata rada')
                ->helperText('Predlaže se na radnom nalogu i na računu (bez PDV-a).')
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->prefix('€'),

            TextInput::make('kontakt_broj')
                ->label('Kontakt broj')
                ->tel(),

            TextInput::make('email')
                ->label('Email')
                ->helperText('Na ovu adresu možeš poslati radni nalog.')
                ->email(),

            Toggle::make('aktivan')
                ->label('Aktivan')
                ->helperText('Bivše zaposlenike isključi umjesto brisanja — ostaju zapisani na starim nalozima.')
                ->default(true)
                ->columnSpanFull(),

            Textarea::make('napomena')
                ->label('Napomena')
                ->rows(2)
                ->columnSpanFull(),

            Placeholder::make('portal')
                ->label('Osobni link „Moji nalozi”')
                ->helperText('Zaposlenik na ovom linku vidi svoje naloge, upisuje sate i materijal. Ne treba lozinku — ako link procuri, napravi novi (gumb gore desno).')
                ->content(fn (?Zaposlenik $record) => $record ? new HtmlString(
                    '<input readonly onclick="this.select()" value="'.e($record->portalUrl()).'" '
                    .'style="width:100%;font-size:13px;padding:6px 8px;border:1px solid #d1d5db;border-radius:8px">'
                ) : null)
                ->hiddenOn('create')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withSum(['radovi as sati_ovaj_mjesec' => fn (Builder $q) => $q
                    ->whereBetween('datum', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]),
                ], 'sati')
                ->withCount(['radniNalozi as otvoreni_nalozi' => fn (Builder $q) => $q
                    ->whereIn('status', ['otvoren', 'u_tijeku']),
                ]))
            ->columns([
                Tables\Columns\TextColumn::make('prezime')
                    ->label('Prezime')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ime')
                    ->label('Ime')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('radno_mjesto')
                    ->label('Radno mjesto')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kontakt_broj')
                    ->label('Kontakt broj'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cijena_sata')
                    ->label('Cijena sata')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('otvoreni_nalozi')
                    ->label('Otvoreni nalozi')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('sati_ovaj_mjesec')
                    ->label('Sati ovaj mjesec')
                    ->numeric(2, ',', '.')
                    ->placeholder('0'),

                Tables\Columns\IconColumn::make('aktivan')
                    ->label('Aktivan')
                    ->boolean(),
            ])
            ->defaultSort(fn (Builder $query) => $query->orderBy('prezime')->orderBy('ime'))
            ->filters([
                Tables\Filters\TernaryFilter::make('aktivan')
                    ->label('Aktivan')
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\Action::make('nalozi')
                    ->label('Nalozi')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('gray')
                    ->url(fn (Zaposlenik $z) => RadniNalogResource::getUrl('index', [
                        'tableFilters' => ['zaposlenik' => ['value' => $z->id]],
                    ])),
                Tables\Actions\EditAction::make()->label('Uredi'),
                Tables\Actions\DeleteAction::make()->label('Obriši')
                    ->modalDescription('Zaposlenik će biti uklonjen sa svih naloga, a njegovi upisani sati ostat će bez imena. Ako je samo otišao iz firme, radije ga označi kao neaktivnog.'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZaposlenici::route('/'),
            'create' => Pages\CreateZaposlenik::route('/create'),
            'edit' => Pages\EditZaposlenik::route('/{record}/edit'),
        ];
    }
}
