<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PutniNalogResource\Pages;
use App\Models\PutniNalog;
use App\Models\RadniNalog;
use App\Models\Zaposlenik;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class PutniNalogResource extends Resource
{
    protected static ?string $model = PutniNalog::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Putni nalozi';

    protected static ?string $modelLabel = 'Putni nalog';

    protected static ?string $pluralModelLabel = 'Putni nalozi';

    protected static ?string $slug = 'putni-nalozi';

    protected static ?int $navigationSort = 7;

    protected static ?string $tenantRelationshipName = 'putniNalozi';

    /** Isti paketi kao radni nalozi (Pro, Business, admin). */
    public static function can(string $action, ?Model $record = null): bool
    {
        return RadniNalogResource::dostupno() && parent::can($action, $record);
    }

    public static function getNavigationBadge(): ?string
    {
        $otvoreni = PutniNalog::where('tvrtka_id', filament()->getTenant()?->id)->where('status', 'otvoren')->count();

        return $otvoreni ?: null;
    }

    private static function datumVrijeme(string $polje, string $oznaka): DateTimePicker
    {
        return DateTimePicker::make($polje)->label($oznaka)->native(false)->seconds(false)
            ->displayFormat('d.m.Y. H:i')->locale('hr')->firstDayOfWeek(1);
    }

    public static function form(Form $form): Form
    {
        $tvrtkaId = fn () => filament()->getTenant()->id;
        $auto = fn (Get $get) => in_array($get('prijevoz'), ['privatni_auto', 'sluzbeni_auto'], true);

        return $form->schema([
            Section::make('Nalog')->schema([
                TextInput::make('broj')->label('Broj naloga')->disabled()->dehydrated()
                    ->placeholder('dodjeljuje se kod spremanja'),
                Select::make('status')->label('Status')->options(PutniNalog::statusi())->default('otvoren')->required()->native(false),
                DatePicker::make('datum_naloga')->label('Datum naloga')->default(today())->required()
                    ->native(false)->displayFormat('d.m.Y.')->locale('hr')->firstDayOfWeek(1),
                TextInput::make('nalogodavac')->label('Nalog izdaje')
                    ->default(fn () => auth()->user()?->name)->helperText('Tko odobrava putovanje (vlasnik, direktor).'),
                ToggleButtons::make('vrsta')->label('Vrsta putovanja')->inline()->required()->live()
                    ->options(['tuzemni' => 'Tuzemno', 'inozemni' => 'Inozemstvo'])->default('tuzemni')
                    ->colors(['tuzemni' => 'primary', 'inozemni' => 'warning'])->columnSpan(2),
                Select::make('radni_nalog_id')->label('Vezani radni nalog')
                    ->options(fn ($state) => RadniNalog::where('tvrtka_id', $tvrtkaId())
                        ->where(fn ($q) => $q->whereIn('status', ['otvoren', 'u_tijeku', 'zavrsen'])->orWhere('id', $state))
                        ->latest('datum_otvaranja')->limit(200)->get()
                        ->mapWithKeys(fn (RadniNalog $n) => [$n->id => $n->broj.($n->lokacija ? ' — '.$n->lokacija : '')]))
                    ->searchable()->nullable()->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($state && ($n = RadniNalog::find($state))) {
                            if (blank($get('odrediste')) && $n->lokacija) {
                                $set('odrediste', $n->lokacija);
                            }
                            if (blank($get('svrha'))) {
                                $set('svrha', 'Radni nalog '.$n->broj.($n->predmet ? ' — '.$n->predmet : ''));
                            }
                        }
                    })->helperText('Po želji: odredište i svrha preuzmu se s naloga.')->columnSpan(2),
            ])->columns(4),

            Section::make('Putnik')->schema([
                Select::make('zaposlenik_id')->label('Zaposlenik')
                    ->options(fn ($state) => Zaposlenik::opcije($tvrtkaId(), [$state]))
                    ->searchable()->nullable()->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state && ($z = Zaposlenik::find($state))) {
                            $set('putnik', $z->ime_prezime);
                            $set('radno_mjesto', $z->radno_mjesto);
                            if ($z->registracija) {
                                $set('registracija', $z->registracija);
                                $set('vozilo', $z->vozilo);
                            }
                        }
                    })->helperText('Za vlasnika ili direktora ostavi prazno i upiši ime ispod.'),
                TextInput::make('putnik')->label('Ime i prezime putnika')->required()->maxLength(255),
                TextInput::make('radno_mjesto')->label('Radno mjesto / funkcija')->placeholder('npr. direktor, serviser'),
            ])->columns(3),

            Section::make('Putovanje')->schema([
                TextInput::make('odrediste')->label('Odredište (mjesto, adresa)')->required()->columnSpan(2),
                Select::make('drzava')->label('Država')
                    ->options(fn () => collect(config('putni.inozemne'))->mapWithKeys(fn ($v, $k) => [$k => $k.' — '.number_format($v[0], 2, ',', '.').' '.$v[1]]))
                    ->searchable()->live()
                    ->visible(fn (Get $get) => $get('vrsta') === 'inozemni')
                    ->required(fn (Get $get) => $get('vrsta') === 'inozemni')
                    ->helperText('Dnevnica po Odluci Vlade RH (NN 16/2024).'),
                Textarea::make('svrha')->label('Svrha putovanja')->required()->rows(2)->columnSpanFull(),
            ])->columns(3),

            Section::make('Prijevoz')->schema([
                Select::make('prijevoz')->label('Prijevozno sredstvo')->options(PutniNalog::prijevozi())
                    ->default('privatni_auto')->required()->native(false)->live(),
                TextInput::make('registracija')->label('Registarska oznaka')
                    ->placeholder('npr. OS-123-AB')->maxLength(20)
                    ->visible($auto)->required($auto)
                    ->dehydrateStateUsing(fn (?string $state) => $state ? mb_strtoupper(trim($state)) : null),
                TextInput::make('vozilo')->label('Marka i model vozila')->placeholder('npr. Škoda Octavia')->maxLength(100)->visible($auto),
                TextInput::make('km_pocetno')->label('Brojilo na polasku (km)')->numeric()->minValue(0)->visible($auto),
                TextInput::make('km_zavrsno')->label('Brojilo na povratku (km)')->numeric()->minValue(0)->visible($auto)
                    ->gte('km_pocetno'),
                TextInput::make('km')->label('Prijeđeno km (ručno)')->numeric()->minValue(0)->step(0.1)->visible($auto)
                    ->helperText(fn () => 'Prazno = razlika brojila. Privatni auto: '.number_format((float) config('putni.km_naknada'), 2, ',', '.').' €/km.'),
            ])->columns(3),

            Section::make('Trajanje i dnevnice')->schema([
                static::datumVrijeme('polazak_at', 'Polazak')->required(),
                static::datumVrijeme('povratak_at', 'Povratak')->after('polazak_at'),
                Select::make('obroci')->label('Osigurani obroci po danu')->native(false)->default(0)
                    ->options([0 => 'Nijedan', 1 => '1 obrok (−30 %)', 2 => '2 ili više obroka (−60 %)']),
                static::datumVrijeme('granica_odlazak_at', 'Prelazak granice — odlazak')
                    ->visible(fn (Get $get) => $get('vrsta') === 'inozemni'),
                static::datumVrijeme('granica_povratak_at', 'Prelazak granice — povratak')
                    ->visible(fn (Get $get) => $get('vrsta') === 'inozemni'),
                TextInput::make('tecaj')->label('Tečaj (1 EUR = x USD)')->numeric()->step(0.000001)
                    ->visible(fn (Get $get) => $get('vrsta') === 'inozemni' && (config('putni.inozemne.'.$get('drzava'))[1] ?? 'EUR') !== 'EUR')
                    ->helperText('Prazno = srednji tečaj HNB-a na dan povratka.'),
            ])->columns(3)
                ->description('Do 8 h nema dnevnice, 8–12 h pola, više od 12 h puna. U inozemstvu se računa vrijeme od granice do granice.'),

            Section::make('Ostali troškovi')->schema([
                Repeater::make('troskovi')->relationship()->orderColumn('redni_broj')->hiddenLabel()->schema([
                    Select::make('vrsta')->label('Vrsta')->options(PutniNalog::vrsteTroskova())->required()->native(false)->columnSpan(['lg' => 3]),
                    TextInput::make('opis')->label('Opis')->columnSpan(['lg' => 4]),
                    TextInput::make('iznos')->label('Iznos €')->numeric()->required()->minValue(0)->step(0.01)->prefix('€')->columnSpan(['lg' => 2]),
                    FileUpload::make('privitak')->label('Račun (slika/PDF)')
                        ->disk('local')->directory(fn () => 'putni-nalozi/'.filament()->getTenant()->id)
                        ->visibility('private')->acceptedFileTypes(['image/*', 'application/pdf'])->maxSize(10240)
                        ->downloadable()->openable()->columnSpan(['lg' => 3]),
                ])->columns(12)->addActionLabel('+ Dodaj trošak')->defaultItems(0),
                TextInput::make('predujam')->label('Isplaćeni predujam')->numeric()->minValue(0)->step(0.01)->prefix('€')->default(0),
            ])->collapsible(),

            Section::make('Izvješće s puta')->schema([
                Textarea::make('izvjesce')->label('Izvješće')->rows(3)
                    ->placeholder('Što je obavljeno na putu (obavezni dio putnog naloga).'),
            ]),

            Section::make('Obračun')->schema([
                Placeholder::make('obracun_prikaz')->hiddenLabel()
                    ->content(fn (?PutniNalog $record) => new HtmlString(view('filament.putni-nalog-obracun', ['nalog' => $record])->render())),
            ])->hiddenOn('create')->description('Osvježi se nakon spremanja.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('broj')->label('Broj')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('datum_naloga')->label('Datum')->date('d.m.Y.')->sortable(),
            Tables\Columns\TextColumn::make('putnik')->label('Putnik')->searchable()->sortable()
                ->description(fn (PutniNalog $n) => $n->registracija ? PutniNalog::prijevozi()[$n->prijevoz].' · '.$n->registracija : PutniNalog::prijevozi()[$n->prijevoz] ?? null),
            Tables\Columns\TextColumn::make('odrediste')->label('Odredište')->searchable()->limit(35)
                ->description(fn (PutniNalog $n) => $n->vrsta === 'inozemni' ? $n->drzava : null),
            Tables\Columns\TextColumn::make('polazak_at')->label('Polazak')->dateTime('d.m.Y. H:i')->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('za_isplatu')->label('Za isplatu')->money('EUR', locale: 'hr')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state) => PutniNalog::statusi()[$state] ?? $state)
                ->color(fn (string $state) => PutniNalog::statusBoja($state))->sortable(),
        ])->defaultSort('datum_naloga', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(PutniNalog::statusi())->multiple(),
                Tables\Filters\SelectFilter::make('vrsta')->label('Vrsta')->options(['tuzemni' => 'Tuzemno', 'inozemni' => 'Inozemstvo']),
                Tables\Filters\SelectFilter::make('zaposlenik_id')->label('Zaposlenik')
                    ->relationship('zaposlenik', 'ime_prezime', fn (Builder $query) => $query->where('tvrtka_id', filament()->getTenant()->id)),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')->label('PDF')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->iconButton()->tooltip('Preuzmi putni nalog s obračunom (PDF)')
                    ->action(fn (PutniNalog $n) => response()->streamDownload(
                        fn () => print (static::generirajPdf($n)), 'Putni_nalog_'.$n->broj.'.pdf'
                    )),
                Tables\Actions\Action::make('obracunaj')->label('Obračunaj')->icon('heroicon-o-calculator')->color('warning')
                    ->iconButton()->tooltip('Zaključi obračun')
                    ->visible(fn (PutniNalog $n) => $n->status === 'otvoren')
                    ->requiresConfirmation()
                    ->modalHeading(fn (PutniNalog $n) => 'Zaključiti obračun naloga '.$n->broj.'?')
                    ->modalDescription(fn (PutniNalog $n) => 'Za isplatu: '.number_format((float) $n->za_isplatu, 2, ',', '.').' €.')
                    ->action(function (PutniNalog $n) {
                        $n->izracunaj();
                        $n->update(['status' => 'obracunat']);
                        Notification::make()->title('Nalog '.$n->broj.' je obračunat')->success()->send();
                    }),
                Tables\Actions\Action::make('isplaceno')->label('Isplaćeno')->icon('heroicon-o-banknotes')->color('success')
                    ->iconButton()->tooltip('Označi kao isplaćeno')
                    ->visible(fn (PutniNalog $n) => $n->status === 'obracunat')
                    ->requiresConfirmation()
                    ->action(fn (PutniNalog $n) => $n->update(['status' => 'isplacen'])),
                Tables\Actions\EditAction::make()->label('Uredi')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('Obriši')->iconButton()
                    ->before(fn (PutniNalog $n) => $n->troskovi->each->delete()),
            ]);
    }

    public static function generirajPdf(PutniNalog $n): string
    {
        $n->izracunaj();
        $n->load(['tvrtka', 'troskovi', 'radniNalog']);

        return Pdf::loadView('pdf.putni-nalog', ['nalog' => $n, 'tvrtka' => $n->tvrtka, 'o' => $n->obracun])
            ->setPaper('a4')->output();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPutniNalozi::route('/'),
            'create' => Pages\CreatePutniNalog::route('/create'),
            'edit' => Pages\EditPutniNalog::route('/{record}/edit'),
        ];
    }
}
