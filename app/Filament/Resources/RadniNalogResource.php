<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadniNalogResource\Pages;
use App\Mail\RadniNalogZaposlenikMail;
use App\Models\Klijent;
use App\Models\RadniNalog;
use App\Models\Racun;
use App\Models\RacunStavka;
use App\Models\TvrtkaPostavke;
use App\Models\Usluga;
use App\Models\Zaposlenik;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RadniNalogResource extends Resource
{
    protected static ?string $model = RadniNalog::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Radni nalozi';

    protected static ?string $modelLabel = 'Radni nalog';

    protected static ?string $pluralModelLabel = 'Radni nalozi';

    protected static ?string $slug = 'radni-nalozi';

    protected static ?int $navigationSort = 6;

    protected static ?string $tenantRelationshipName = 'radniNalozi';

    public static function getNavigationBadge(): ?string
    {
        $otvoreni = RadniNalog::where('tvrtka_id', filament()->getTenant()?->id)
            ->whereIn('status', ['otvoren', 'u_tijeku'])->count();

        return $otvoreni ?: null;
    }

    public static function form(Form $form): Form
    {
        $tvrtkaId = fn () => filament()->getTenant()->id;

        return $form->schema([
            Section::make()->schema([
                Select::make('klijent_id')->label('Klijent')
                    ->options(fn () => Klijent::where('tvrtka_id', $tvrtkaId())->orderBy('naziv')->pluck('naziv', 'id'))
                    ->searchable()->nullable()->live()
                    ->helperText('Prazno za interni posao.')
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($state && blank($get('lokacija')) && ($k = Klijent::find($state))) {
                            $set('lokacija', trim($k->adresa.', '.trim($k->po_broj.' '.$k->mjesto), ', '));
                        }
                    })->columnSpan(2),
                TextInput::make('broj')->label('Broj naloga')->disabled()->dehydrated()
                    ->placeholder('dodjeljuje se kod spremanja'),
                Select::make('status')->label('Status')->options(RadniNalog::statusi())
                    ->default('otvoren')->required()->native(false),
                DatePicker::make('datum_otvaranja')->label('Datum otvaranja')
                    ->default(today())->required()->native(false)->displayFormat('d.m.Y.')->locale('hr')->firstDayOfWeek(1),
                DatePicker::make('rok')->label('Rok izvršenja')->native(false)->displayFormat('d.m.Y.')->locale('hr')->firstDayOfWeek(1),
                TextInput::make('predmet')->label('Predmet')
                    ->placeholder('uređaj, vozilo, objekt… npr. Bosch perilica WAN28, ZG-1234-AB')->columnSpan(2),
                TextInput::make('lokacija')->label('Lokacija / adresa izvršenja')->columnSpan(2),
            ])->columns(4),

            Section::make('Izvršitelji')->schema([
                Select::make('nositelj_id')->label('Nositelj posla')
                    ->options(fn ($state) => Zaposlenik::opcije($tvrtkaId(), [$state]))
                    ->searchable()->nullable()->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $odabrani = array_map('strval', $get('zaposlenici') ?? []);
                        if ($state && ! in_array((string) $state, $odabrani, true)) {
                            $set('zaposlenici', [...$odabrani, (string) $state]);
                        }
                    }),
                Select::make('zaposlenici')->label('Svi izvršitelji')
                    ->relationship('zaposlenici', 'ime_prezime', fn (Builder $query) => $query->where('tvrtka_id', $tvrtkaId())->orderBy('prezime')->orderBy('ime'))
                    ->getOptionLabelFromRecordUsing(fn (Zaposlenik $z) => $z->ime_prezime.($z->aktivan ? '' : ' (neaktivan)'))
                    ->multiple()->preload()->searchable(),
            ])->columns(2)
                ->description('Zaposlenike dodaješ u izborniku Zaposlenici.'),

            Section::make('Posao')->schema([
                Textarea::make('opis_posla')->label('Opis posla / zahtjev klijenta')->required()->rows(3),
                Textarea::make('izvrseni_radovi')->label('Izvršeni radovi')->rows(3)
                    ->helperText('Ispisuje se na nalogu za klijenta.'),
                Textarea::make('napomena')->label('Interna napomena')->rows(2)
                    ->helperText('Vidljivo samo tebi i na nalogu za zaposlenika.'),
            ]),

            Section::make('Evidencija rada')->schema([
                Repeater::make('radovi')->relationship()->orderColumn('redni_broj')->hiddenLabel()->schema([
                    DatePicker::make('datum')->label('Datum dolaska')->default(today())->required()
                        ->native(false)->displayFormat('d.m.Y.')->locale('hr')->firstDayOfWeek(1)->columnSpan(2),
                    TimePicker::make('vrijeme_dolaska')->label('Vrijeme')->default(fn () => now()->format('H:i'))
                        ->native(false)->seconds(false)->displayFormat('H:i')->format('H:i')->columnSpan(1),
                    Select::make('zaposlenik_id')->label('Zaposlenik')
                        ->options(fn ($state) => Zaposlenik::opcije($tvrtkaId(), [$state]))
                        ->default(fn (Get $get) => $get('../../nositelj_id'))
                        ->searchable()->nullable()->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state && ($z = Zaposlenik::find($state)) && $z->cijena_sata !== null) {
                                $set('cijena_sata', $z->cijena_sata);
                            }
                        })->columnSpan(3),
                    TextInput::make('sati')->label('Sati')->numeric()->required()->step(0.25)->minValue(0.25)
                        ->default(1)->columnSpan(1),
                    TextInput::make('cijena_sata')->label('Cijena sata')->numeric()->step(0.01)->prefix('€')
                        ->default(fn (Get $get) => Zaposlenik::find($get('../../nositelj_id'))?->cijena_sata)
                        ->columnSpan(2),
                    TextInput::make('opis')->label('Što je napravljeno')->columnSpan(3),
                ])->columns(12)->addActionLabel('+ Upiši rad')->defaultItems(0)->reorderable(false),
            ])->collapsible(),

            Section::make('Materijal i usluge')->schema([
                Repeater::make('stavke')->relationship()->orderColumn('redni_broj')->hiddenLabel()->schema([
                    Select::make('usluga_id')->label('Usluga / Proizvod')
                        ->options(fn () => Usluga::where('tvrtka_id', $tvrtkaId())->orderBy('naziv')->pluck('naziv', 'id'))
                        ->searchable()->nullable()->live()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            if ($state && ($usluga = Usluga::find($state))) {
                                $set('naziv', $usluga->naziv);
                                $set('cijena', $usluga->cijena);
                                $set('pdv_stopa', $usluga->pdv_stopa);
                                $set('jedinica_mjere', $usluga->jedinica_mjere);
                            }
                            static::izracunajStavku($get, $set);
                        })->columnSpan(4),
                    TextInput::make('naziv')->label('Naziv')->required()->columnSpan(4),
                    TextInput::make('opis')->label('Opis')->columnSpan(4),
                    TextInput::make('jedinica_mjere')->label('JM')->default('kom')->columnSpan(1),
                    TextInput::make('kolicina')->label('Količina')->numeric()->required()->default(1)
                        ->step(0.001)->minValue(0.001)->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))->columnSpan(2),
                    TextInput::make('cijena')->label('Cijena (bez PDV)')->numeric()->required()->default(0)
                        ->step(0.01)->prefix('€')->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))->columnSpan(2),
                    TextInput::make('rabat_posto')->label('Rabat %')->numeric()->default(0)->step(0.01)
                        ->suffix('%')->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))->columnSpan(2),
                    Select::make('pdv_stopa')->label('PDV %')->options(Usluga::pdvStope())->default(null)
                        ->native(false)->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))->columnSpan(2),
                    TextInput::make('ukupno')->label('Ukupno €')->numeric()->disabled()->dehydrated()
                        ->prefix('€')->columnSpan(3),
                ])->columns(12)->addActionLabel('+ Dodaj materijal / uslugu')->reorderable()->defaultItems(0),
            ])->collapsible(),

            Section::make('Ukupno')->schema([
                Placeholder::make('sazetak_sati')->label('Sati rada')
                    ->content(fn (?RadniNalog $record) => number_format((float) $record?->ukupno_sati, 2, ',', '.').' h'),
                Placeholder::make('sazetak_rad')->label('Rad (bez PDV-a)')
                    ->content(fn (?RadniNalog $record) => number_format((float) $record?->ukupno_rad, 2, ',', '.').' €'),
                Placeholder::make('sazetak_materijal')->label('Materijal i usluge (s PDV-om)')
                    ->content(fn (?RadniNalog $record) => number_format((float) $record?->ukupno_materijal, 2, ',', '.').' €'),
                Placeholder::make('sazetak_racun')->label('Račun')
                    ->content(fn (?RadniNalog $record) => $record?->racun?->broj ?? '—'),
                Placeholder::make('potpis')->label('Potpis klijenta')
                    ->visible(fn (?RadniNalog $record) => (bool) $record?->potpis_klijenta)
                    ->content(fn (?RadniNalog $record) => new \Illuminate\Support\HtmlString(
                        '<img src="'.e($record->potpis_klijenta).'" style="max-height:90px;background:#fff;border:1px solid #e5e7eb;border-radius:8px">'
                        .'<div style="font-size:12px;color:#6b7280">'.e($record->potpisao ?? '').' · '.$record->potpisano_at?->format('d.m.Y. H:i').'</div>'
                    ))->columnSpanFull(),
            ])->columns(4)->hiddenOn('create')
                ->description('Zbrojevi se osvježe nakon spremanja.'),
        ]);
    }

    protected static function izracunajStavku(Get $get, Set $set): void
    {
        $neto = (float) ($get('cijena') ?? 0) * (float) ($get('kolicina') ?? 1) * (1 - (float) ($get('rabat_posto') ?? 0) / 100);
        $set('ukupno', round($neto * (1 + (float) ($get('pdv_stopa') ?? 0) / 100), 2));
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('broj')->label('Broj')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('datum_otvaranja')->label('Otvoren')->date('d.m.Y.')->sortable(),
            Tables\Columns\TextColumn::make('klijent.naziv')->label('Klijent')->searchable()->sortable()
                ->placeholder('interno'),
            Tables\Columns\TextColumn::make('predmet')->label('Predmet')->searchable()->limit(40)
                ->description(fn (RadniNalog $n) => \Illuminate\Support\Str::limit($n->opis_posla, 60)),
            Tables\Columns\TextColumn::make('izvrsitelji')->label('Izvršitelji')
                ->getStateUsing(fn (RadniNalog $n) => $n->zaposlenici
                    ->map(fn (Zaposlenik $z) => $z->ime_prezime.($z->pivot->prihvacen_at ? ' ✓' : ''))->all())
                ->badge()->color(fn (string $state) => str_ends_with($state, '✓') ? 'success' : 'gray')->limitList(3)
                ->tooltip('✓ = zaposlenik je prihvatio nalog'),
            Tables\Columns\TextColumn::make('rok')->label('Rok')->date('d.m.Y.')->sortable()
                ->color(fn (RadniNalog $n) => $n->rok && $n->jeOtvoren() && $n->rok->isPast() && ! $n->rok->isToday() ? 'danger' : null),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state) => RadniNalog::statusi()[$state] ?? $state)
                ->color(fn (string $state) => RadniNalog::statusBoja($state))->sortable(),
            Tables\Columns\TextColumn::make('ukupno_sati')->label('Sati')->numeric(2, ',', '.')->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('racun.broj')->label('Račun')->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),
        ])->modifyQueryUsing(fn (Builder $query) => $query->with(['zaposlenici', 'klijent', 'racun']))
            ->defaultSort('datum_otvaranja', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(RadniNalog::statusi())->multiple(),
                Tables\Filters\SelectFilter::make('zaposlenik')->label('Zaposlenik')
                    ->relationship('zaposlenici', 'ime_prezime', fn (Builder $query) => $query->where('tvrtka_id', filament()->getTenant()->id)),
                Tables\Filters\SelectFilter::make('godina')->label('Godina')
                    ->options(fn () => RadniNalog::where('tvrtka_id', filament()->getTenant()->id)
                        ->distinct()->orderByDesc('godina')->pluck('godina', 'godina')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('pdf_zaposlenik')->label('PDF za zaposlenika')
                        ->icon('heroicon-o-user')
                        ->action(fn (RadniNalog $n) => response()->streamDownload(
                            fn () => print (static::generirajPdf($n, false)), 'Radni_nalog_'.$n->broj.'.pdf'
                        )),
                    Tables\Actions\Action::make('pdf_klijent')->label('PDF za klijenta')
                        ->icon('heroicon-o-document-text')
                        ->action(fn (RadniNalog $n) => response()->streamDownload(
                            fn () => print (static::generirajPdf($n, true)), 'Radni_nalog_'.$n->broj.'_klijent.pdf'
                        )),
                ])->label('PDF')->icon('heroicon-o-arrow-down-tray')->color('gray')->iconButton()->tooltip('Preuzmi PDF'),

                static::posaljiZaposlenikuAction(),

                Tables\Actions\Action::make('zavrsi')->label('Završi')
                    ->icon('heroicon-o-check-circle')->color('success')->iconButton()->tooltip('Označi kao završen')
                    ->visible(fn (RadniNalog $n) => $n->jeOtvoren())
                    ->requiresConfirmation()
                    ->modalHeading(fn (RadniNalog $n) => 'Završiti nalog '.$n->broj.'?')
                    ->modalDescription(fn (RadniNalog $n) => 'Upisano: '.number_format((float) $n->ukupno_sati, 2, ',', '.').' h rada i '
                        .$n->stavke()->count().' stavki materijala. Nakon toga možeš izraditi račun.')
                    ->action(function (RadniNalog $n) {
                        $n->update(['status' => 'zavrsen']);
                        Notification::make()->title('Nalog '.$n->broj.' je završen')->success()->send();
                    }),

                static::izradiRacunAction(),

                Tables\Actions\EditAction::make()->label('Uredi')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('Obriši')->iconButton(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
            ])]);
    }

    public static function posaljiZaposlenikuAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('posalji_zaposleniku')
            ->label('Pošalji zaposleniku')
            ->icon('heroicon-o-envelope')->color('primary')->iconButton()->tooltip('Pošalji nalog zaposleniku (e-mail s linkom)')
            ->visible(fn (RadniNalog $n) => $n->jeOtvoren())
            ->form(function (RadniNalog $n): array {
                $n->load(['zaposlenici', 'tvrtka', 'klijent']);
                $sEmailom = $n->zaposlenici->filter(fn (Zaposlenik $z) => filled($z->email));
                $postavke = TvrtkaPostavke::where('tvrtka_id', $n->tvrtka_id)->first();

                $poruka = 'Bok,'."\n\n".'dodijeljen ti je radni nalog '.$n->broj
                    .($n->klijent ? ' za '.$n->klijent->naziv : '').'.'
                    ."\n\n".'Posao: '.$n->opis_posla."\n\n".'Pozdrav,'."\n".($n->tvrtka->vlasnik ?: $n->tvrtka->naziv);

                $linkovi = $n->zaposlenici->map(function (Zaposlenik $z) use ($n) {
                    $z->portalUrl();
                    $url = route('moji-nalozi.show', [$z->portal_token, $n->id]);

                    return '<li style="margin:4px 0"><strong>'.e($z->ime_prezime).'</strong>'.($z->pivot->prihvacen_at ? ' ✓ prihvatio' : '')
                        .'<input readonly onclick="this.select()" value="'.e($url).'" '
                        .'style="width:100%;font-size:12px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px"></li>';
                })->implode('');

                return [
                    TextInput::make('od')->label('Pošiljatelj')
                        ->default($postavke?->smtp_from_email ?? '')->required(),
                    Select::make('prima')->label('Prima')->multiple()->required()
                        ->options($sEmailom->mapWithKeys(fn (Zaposlenik $z) => [$z->id => $z->ime_prezime.' <'.$z->email.'>'])->all())
                        ->default($sEmailom->pluck('id')->values()->all())
                        ->helperText($sEmailom->isEmpty() ? 'Nijedan izvršitelj nema upisan e-mail (Zaposlenici → Uredi).' : 'Svaki dobiva svoj mail s gumbom „Prihvati i otvori nalog”.'),
                    Textarea::make('poruka')->label('Poruka')->default($poruka)->rows(6),
                    Placeholder::make('linkovi')->label('Osobni linkovi na nalog (za WhatsApp/SMS)')
                        ->content(new \Illuminate\Support\HtmlString($linkovi ? '<ul>'.$linkovi.'</ul>' : '—')),
                ];
            })
            ->modalHeading(fn (RadniNalog $n) => 'Pošalji nalog '.$n->broj.' zaposleniku')
            ->modalSubmitActionLabel('Pošalji')
            ->action(function (RadniNalog $n, array $data) {
                $postavke = TvrtkaPostavke::where('tvrtka_id', $n->tvrtka_id)->first();

                if ($postavke?->smtp_host) {
                    config([
                        'mail.mailers.tvrtka' => [
                            'transport' => 'smtp',
                            'host' => $postavke->smtp_host,
                            'port' => $postavke->smtp_port ?? 587,
                            'encryption' => $postavke->smtp_sigurnost === 'none' ? null : $postavke->smtp_sigurnost,
                            'username' => $postavke->smtp_user,
                            'password' => $postavke->smtp_pass,
                            'timeout' => null,
                        ],
                        'mail.from' => [
                            'address' => $data['od'],
                            'name' => $postavke->smtp_from_name ?? $n->tvrtka->naziv,
                        ],
                    ]);
                    $mailer = Mail::mailer('tvrtka');
                } else {
                    $mailer = Mail::mailer();
                }

                $n->load(['tvrtka', 'klijent']);
                $primatelji = $n->zaposlenici()->whereIn('zaposlenici.id', $data['prima'])->whereNotNull('email')->get();

                try {
                    foreach ($primatelji as $z) {
                        $z->portalUrl();
                        $mailer->to($z->email)->send(new RadniNalogZaposlenikMail($n, $z, $data['poruka']));
                    }

                    Notification::make()->title('Nalog poslan')
                        ->body('Radni nalog '.$n->broj.' poslan: '.$primatelji->pluck('ime_prezime')->implode(', '))
                        ->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('Greška pri slanju e-maila')
                        ->body($e->getMessage())->danger()->persistent()->send();
                }
            });
    }

    public static function izradiRacunAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('racun')->label('Izradi račun')
            ->icon('heroicon-o-banknotes')->color('warning')->iconButton()->tooltip('Izradi račun iz naloga')
            ->visible(fn (RadniNalog $n) => ! $n->racun_id && $n->status !== 'otkazan')
            ->form(fn (RadniNalog $n) => [
                Checkbox::make('rad')->label('Rad: '.number_format((float) $n->ukupno_sati, 2, ',', '.').' h ('
                    .number_format((float) $n->ukupno_rad, 2, ',', '.').' € bez PDV-a)')
                    ->default((float) $n->ukupno_sati > 0)->disabled((float) $n->ukupno_sati <= 0),
                Checkbox::make('materijal')->label('Materijal i usluge: '.$n->stavke()->count().' stavki ('
                    .number_format((float) $n->ukupno_materijal, 2, ',', '.').' €)')
                    ->default(true),
            ])
            ->modalHeading(fn (RadniNalog $n) => 'Račun iz naloga '.$n->broj)
            ->modalDescription('Sati se zbrajaju po cijeni sata u stavke „Rad”. Račun se otvara za pregled, a nalog dobiva status Naplaćen.')
            ->modalSubmitActionLabel('Izradi račun')
            ->action(function (RadniNalog $n, array $data, Tables\Actions\Action $action, $livewire) {
                try {
                    $racun = static::izradiRacun($n, (bool) ($data['rad'] ?? false), (bool) ($data['materijal'] ?? false));
                } catch (ValidationException $e) {
                    Notification::make()->title('Račun nije izrađen')
                        ->body(collect($e->errors())->flatten()->implode("\n"))->danger()->persistent()->send();
                    $action->halt();

                    return;
                }

                Notification::make()->title('Račun '.$racun->broj.' izrađen je iz naloga '.$n->broj)->success()->send();

                $livewire->redirect(RacunResource::getUrl('edit', ['record' => $racun]));
            });
    }

    public static function izradiRacun(RadniNalog $n, bool $ukljuciRad, bool $ukljuciMaterijal): Racun
    {
        $n->load(['stavke', 'radovi.zaposlenik', 'tvrtka']);
        $tvrtka = $n->tvrtka;

        if (! $n->klijent_id) {
            throw ValidationException::withMessages(['klijent_id' => 'Nalog nema klijenta — dodaj klijenta na nalog pa izradi račun.']);
        }

        $stavke = [];

        if ($ukljuciRad) {
            $pdvRad = $tvrtka->u_sustavu_pdv ? 25 : null;
            foreach ($n->radovi->groupBy(fn ($r) => number_format((float) ($r->cijena_sata ?? 0), 2, '.', '')) as $cijena => $grupa) {
                $mjesta = $grupa->map(fn ($r) => $r->zaposlenik?->radno_mjesto)->filter()->unique();
                $sati = (float) $grupa->sum('sati');
                $stavke[] = [
                    'usluga_id' => null,
                    'naziv' => 'Rad'.($mjesta->count() === 1 ? ' — '.$mjesta->first() : ''),
                    'opis' => 'Radni nalog '.$n->broj,
                    'jedinica_mjere' => 'h',
                    'kolicina' => $sati,
                    'cijena' => (float) $cijena,
                    'rabat_posto' => 0,
                    'pdv_stopa' => $pdvRad,
                    'ukupno' => round($sati * (float) $cijena * (1 + ($pdvRad ?? 0) / 100), 2),
                ];
            }
        }

        if ($ukljuciMaterijal) {
            foreach ($n->stavke as $s) {
                $stavke[] = $s->only(['usluga_id', 'naziv', 'opis', 'jedinica_mjere', 'kolicina', 'cijena', 'rabat_posto', 'pdv_stopa', 'ukupno']);
            }
        }

        if ($stavke === []) {
            throw ValidationException::withMessages(['stavke' => 'Nema stavki za račun — upiši rad ili materijal na nalog.']);
        }

        $prodano = [];
        foreach ($stavke as $s) {
            if ($s['usluga_id']) {
                $prodano[$s['usluga_id']] = ($prodano[$s['usluga_id']] ?? 0) + (float) $s['kolicina'];
            }
        }
        RacunResource::provjeriDovoljnoZalihe($tvrtka->id, $prodano);

        $racun = DB::transaction(function () use ($n, $tvrtka, $stavke): Racun {
            $broj = Racun::generiraBroj($tvrtka->id);

            $racun = Racun::create([
                'tvrtka_id' => $tvrtka->id,
                'klijent_id' => $n->klijent_id,
                'broj' => $broj['broj'],
                'redni_broj' => $broj['redni_broj'],
                'godina' => $broj['godina'],
                'datum_izdavanja' => today(),
                'vrijeme_izdavanja' => now()->format('H:i'),
                'datum_dospijeca' => today(),
                'datum_isporuke' => $n->zavrsen_at?->toDateString() ?? today(),
                'mjesto_izdavanja' => $tvrtka->mjesto,
                'nacin_placanja' => 'transakcijski',
                'napomena' => trim('Prema radnom nalogu '.$n->broj.'.'."\n".($tvrtka->napomena ?? '')),
                'status' => 'final',
            ]);

            foreach (array_values($stavke) as $i => $s) {
                RacunStavka::create([...$s, 'racun_id' => $racun->id, 'redni_broj' => $i + 1]);
            }

            $racun->load('stavke');
            $racun->izracunajUkupno();

            $n->update(['racun_id' => $racun->id, 'status' => 'naplacen']);

            return $racun;
        });

        RacunResource::primijeniProdajuNaZalihu($racun, $prodano, 'izlaz');

        return $racun;
    }

    public static function generirajPdf(RadniNalog $n, bool $zaKlijenta): string
    {
        $n->load(['stavke', 'radovi.zaposlenik', 'zaposlenici', 'nositelj', 'tvrtka', 'klijent']);

        return Pdf::loadView('pdf.radni-nalog', [
            'nalog' => $n, 'tvrtka' => $n->tvrtka, 'klijent' => $n->klijent, 'zaKlijenta' => $zaKlijenta,
        ])->setPaper('a4')->output();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRadniNalozi::route('/'),
            'create' => Pages\CreateRadniNalog::route('/create'),
            'edit' => Pages\EditRadniNalog::route('/{record}/edit'),
        ];
    }
}
