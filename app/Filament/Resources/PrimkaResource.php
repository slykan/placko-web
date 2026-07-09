<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrimkaResource\Pages;
use App\Models\Dobavljac;
use App\Models\Primka;
use App\Models\Skladiste;
use App\Models\Usluga;
use App\Services\ZalihaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrimkaResource extends Resource
{
    protected static ?string $model = Primka::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Primke';

    protected static ?string $modelLabel = 'Primka';

    protected static ?string $pluralModelLabel = 'Primke';

    protected static ?string $slug = 'primke';

    protected static ?int $navigationSort = 8;

    protected static ?string $tenantRelationshipName = 'primke';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Select::make('dobavljac_id')->label('Dobavljač')
                    ->options(fn () => Dobavljac::where('tvrtka_id', filament()->getTenant()->id)
                        ->orderBy('naziv')->pluck('naziv', 'id'))
                    ->searchable()->nullable()->placeholder('Bez dobavljača'),
                Select::make('skladiste_id')->label('Skladište')
                    ->options(fn () => Skladiste::where('tvrtka_id', filament()->getTenant()->id)
                        ->orderBy('naziv')->pluck('naziv', 'id'))
                    ->default(fn () => ZalihaService::zadanoSkladiste(filament()->getTenant()->id)->id)
                    ->required()->native(false)
                    ->disabled(fn (string $context) => $context === 'edit')
                    ->dehydrated(),
                TextInput::make('broj')->label('Broj primke')->disabled()->dehydrated(),
                DatePicker::make('datum_primke')->label('Datum primke')
                    ->default(today())->required()->native(false)->displayFormat('d.m.Y.'),
                Textarea::make('napomena')->label('Napomena')->rows(2)->columnSpanFull(),
            ])->columns(3),

            Section::make('Stavke')->schema([
                Repeater::make('stavke')->schema([
                    Hidden::make('id'),
                    Select::make('usluga_id')->label('Proizvod')
                        ->options(fn () => Usluga::where('tvrtka_id', filament()->getTenant()->id)
                            ->where('prati_zalihu', true)->orderBy('naziv')->pluck('naziv', 'id'))
                        ->searchable()->required()
                        ->helperText('Samo proizvodi s uključenim praćenjem zalihe')
                        ->columnSpan(5),
                    TextInput::make('kolicina')->label('Količina')->numeric()->required()->default(1)
                        ->step(0.001)->minValue(0.001)->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajSve($get, $set))->columnSpan(2),
                    TextInput::make('nabavna_cijena')->label('Nabavna cijena')->numeric()->required()->default(0)
                        ->step(0.01)->prefix('€')->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajSve($get, $set))->columnSpan(2),
                    TextInput::make('ukupno')->label('Ukupno €')->numeric()->disabled()->dehydrated()
                        ->prefix('€')->columnSpan(3),
                ])->columns(12)->addActionLabel('+ Dodaj stavku')->reorderable()->cloneable()
                    ->defaultItems(0)->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::postaviUkupno($get('stavke') ?? [], $set)),
            ]),

            Section::make('Ukupno')->schema([
                TextInput::make('ukupno')->label('UKUPNO')->disabled()->dehydrated()->prefix('€'),
            ]),
        ]);
    }

    protected static function izracunajSve(Get $get, Set $set): void
    {
        $set('ukupno', round((float) ($get('kolicina') ?? 0) * (float) ($get('nabavna_cijena') ?? 0), 2));
        static::postaviUkupno($get('../../stavke') ?? [], $set, '../../');
    }

    protected static function postaviUkupno(array $stavke, Set $set, string $prefix = ''): void
    {
        $ukupno = 0;
        foreach ($stavke as $stavka) {
            $ukupno += (float) ($stavka['kolicina'] ?? 0) * (float) ($stavka['nabavna_cijena'] ?? 0);
        }
        $set($prefix.'ukupno', round($ukupno, 2));
    }

    public static function filtrirajPrazneStavke(array $stavke): array
    {
        return array_values(array_filter($stavke, fn ($s) => filled($s['usluga_id'] ?? null)));
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('broj')->label('Broj')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('datum_primke')->label('Datum')->date('d.m.Y.')->sortable(),
            Tables\Columns\TextColumn::make('dobavljac.naziv')->label('Dobavljač')->placeholder('—')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('skladiste.naziv')->label('Skladište')->sortable(),
            Tables\Columns\TextColumn::make('ukupno')->label('Ukupno')->money('EUR')->sortable(),
        ])->defaultSort('datum_primke', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('godina')->label('Godina')
                    ->options(fn () => Primka::where('tvrtka_id', filament()->getTenant()->id)
                        ->distinct()->orderByDesc('godina')->pluck('godina', 'godina')),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')->label('Preuzmi PDF')
                    ->icon('heroicon-o-arrow-down-tray')->color('gray')->iconButton()->tooltip('Preuzmi PDF')
                    ->action(fn (Primka $primka) => response()->streamDownload(
                        fn () => print (static::generirajPdf($primka)), 'Primka_'.$primka->broj.'.pdf'
                    )),
                Tables\Actions\EditAction::make()->label('Uredi')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('Obriši')->iconButton(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
            ])]);
    }

    public static function generirajPdf(Primka $primka): string
    {
        $primka->load(['stavke.usluga', 'tvrtka', 'dobavljac', 'skladiste']);

        return Pdf::loadView('pdf.primka', [
            'primka' => $primka, 'tvrtka' => $primka->tvrtka, 'dobavljac' => $primka->dobavljac,
        ])->setPaper('a4')->output();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrimke::route('/'),
            'create' => Pages\CreatePrimka::route('/create'),
            'edit' => Pages\EditPrimka::route('/{record}/edit'),
        ];
    }
}
