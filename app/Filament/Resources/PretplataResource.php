<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PretplataResource\Pages;
use App\Models\Pretplata;
use App\Models\Usluga;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PretplataResource extends Resource
{
    protected static ?string $model = Pretplata::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Pretplate';

    protected static ?string $modelLabel = 'Pretplata';

    protected static ?string $pluralModelLabel = 'Pretplate';

    protected static ?int $navigationSort = 5;

    protected static ?string $tenantRelationshipName = 'pretplate';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Select::make('klijent_id')
                    ->label('Klijent')
                    ->options(fn () => \App\Models\Klijent::where('tvrtka_id', filament()->getTenant()->id)
                        ->orderBy('naziv')
                        ->pluck('naziv', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Select::make('period')
                    ->label('Period')
                    ->options(Pretplata::periodi())
                    ->default('godisnje')
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $pocetak = $get('datum_pocetka');
                        if (! $pocetak) {
                            return;
                        }
                        $set('datum_isteka', static::izracunajIsteka($pocetak, $get('period')));
                    }),

                DatePicker::make('datum_pocetka')
                    ->label('Datum početka')
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (! $state) {
                            return;
                        }
                        $set('datum_isteka', static::izracunajIsteka($state, $get('period')));
                    }),

                DatePicker::make('datum_isteka')
                    ->label('Datum isteka')
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y.'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktivna'   => 'Aktivna',
                        'neaktivna' => 'Neaktivna',
                        'istekla'   => 'Istekla',
                    ])
                    ->default('aktivna')
                    ->native(false),

                Textarea::make('opis')
                    ->label('Opis (općenito)')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Stavke pretplate')->schema([
                Repeater::make('stavke_data')
                    ->label('')
                    ->schema([
                        // Red 1: Usluga, Naziv, Opis
                        Select::make('usluga_id')
                            ->label('Usluga')
                            ->options(fn () => Usluga::where('tvrtka_id', filament()->getTenant()->id)
                                ->orderBy('naziv')
                                ->pluck('naziv', 'id'))
                            ->searchable()
                            ->placeholder('Pretraži uslugu...')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $usluga = Usluga::find($state);
                                if ($usluga) {
                                    $set('naziv', $usluga->naziv);
                                    $set('cijena', $usluga->cijena);
                                    $set('pdv_stopa', $usluga->pdv_stopa);
                                }
                            })
                            ->columnSpan(4),

                        TextInput::make('naziv')
                            ->label('Naziv')
                            ->columnSpan(4),

                        TextInput::make('opis')
                            ->label('Opis')
                            ->placeholder('npr. Hosting paket / Domena')
                            ->columnSpan(4),

                        // Red 2: Kol, Cijena, PDV
                        TextInput::make('kolicina')
                            ->label('Količina')
                            ->numeric()
                            ->default(1)
                            ->step(0.001)
                            ->columnSpan(2),

                        TextInput::make('cijena')
                            ->label('Cijena (€)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->columnSpan(4),

                        Select::make('pdv_stopa')
                            ->label('PDV %')
                            ->options(Usluga::pdvStope())
                            ->placeholder('PDV')
                            ->default(null)
                            ->native(false)
                            ->columnSpan(4),
                    ])
                    ->columns(12)
                    ->addActionLabel('+ Dodaj stavku')
                    ->defaultItems(1),
            ]),
        ]);
    }

    protected static function izracunajIsteka(string $pocetak, ?string $period): string
    {
        $datum = \Carbon\Carbon::parse($pocetak);

        return match ($period) {
            'mjesecno'     => $datum->addMonth()->subDay()->toDateString(),
            'tromjesecno'  => $datum->addMonths(3)->subDay()->toDateString(),
            'polugodisnje' => $datum->addMonths(6)->subDay()->toDateString(),
            'godisnje'     => $datum->addYear()->subDay()->toDateString(),
            default        => $datum->addYear()->subDay()->toDateString(),
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('klijent.naziv')
                    ->label('Klijent')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stavke_count')
                    ->label('Usluga')
                    ->getStateUsing(function (Pretplata $record): string {
                        $count = $record->stavke->count();
                        if ($count === 1) {
                            $stavka = $record->stavke->first();
                            return $stavka->naziv ?? $stavka->opis ?? '1 stavka';
                        }
                        return "{$count} stavki";
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('period')
                    ->label('Period')
                    ->formatStateUsing(fn ($state) => Pretplata::periodi()[$state] ?? $state)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('datum_pocetka')
                    ->label('Početak')
                    ->date('d.m.Y.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('datum_isteka')
                    ->label('Istječe')
                    ->date('d.m.Y.')
                    ->sortable()
                    ->color(fn (Pretplata $record) => $record->datum_isteka->isPast() ? 'danger' : (
                        $record->datum_isteka->diffInDays(now()) <= 30 ? 'warning' : null
                    ))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ukupno')
                    ->label('Cijena')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'aktivna',
                        'gray'    => 'neaktivna',
                        'danger'  => 'istekla',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->toggleable(),
            ])
            ->defaultSort('datum_isteka')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktivna'   => 'Aktivna',
                        'neaktivna' => 'Neaktivna',
                        'istekla'   => 'Istekla',
                    ]),

                SelectFilter::make('period')
                    ->label('Period')
                    ->options(Pretplata::periodi()),
            ])
            ->actions([
                Tables\Actions\Action::make('renew')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->iconButton()
                    ->tooltip('Obnovi pretplatu')
                    ->requiresConfirmation()
                    ->modalHeading('Obnovi pretplatu')
                    ->modalDescription(fn (Pretplata $record) => "Kreirati novi period od {$record->datum_isteka->addDay()->format('d.m.Y.')} do {$record->sljedeciDatum()->format('d.m.Y.')}?")
                    ->action(function (Pretplata $record) {
                        $record->load('stavke');
                        $nova = $record->renew();
                        $nova->load('stavke');
                        $nova->izracunajUkupno();

                        Notification::make()
                            ->title('Pretplata obnovljena')
                            ->body("Novi period do {$nova->datum_isteka->format('d.m.Y.')}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Uredi'),
                    Tables\Actions\DeleteAction::make()->label('Obriši'),
                ])->iconButton()->icon('heroicon-o-ellipsis-vertical')->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('stavke');
        $tenant = filament()->getTenant();
        if ($tenant) {
            $query->where('tvrtka_id', $tenant->id);
        }
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPretplate::route('/'),
            'create' => Pages\CreatePretplata::route('/create'),
            'edit'   => Pages\EditPretplata::route('/{record}/edit'),
        ];
    }
}
