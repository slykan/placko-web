<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RacunResource\Pages;
use App\Mail\RacunMail;
use App\Models\Klijent;
use App\Models\Racun;
use App\Models\TvrtkaPostavke;
use App\Models\Usluga;
use App\Services\EracunService;
use App\Services\FiskalizacijaService;
use App\Services\Hub3Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RacunResource extends Resource
{
    protected static ?string $model = Racun::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Izlazni računi (IRA)';

    protected static ?string $modelLabel = 'Račun';

    protected static ?string $pluralModelLabel = 'Računi';

    protected static ?int $navigationSort = 4;

    protected static ?string $tenantRelationshipName = 'racuni';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                // Kupac - searchable
                Select::make('klijent_id')
                    ->label('Kupac')
                    ->options(fn () => Klijent::where('tvrtka_id', filament()->getTenant()->id)
                        ->orderBy('naziv')
                        ->pluck('naziv', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('broj')
                    ->label('Broj računa')
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(1),

                DatePicker::make('datum_izdavanja')
                    ->label('Datum izdavanja')
                    ->default(today())
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y.')
                    ->columnSpan(1),

                TimePicker::make('vrijeme_izdavanja')
                    ->label('Vrijeme izdavanja')
                    ->default(now()->format('H:i'))
                    ->seconds(false)
                    ->columnSpan(1),

                DatePicker::make('datum_dospijeca')
                    ->label('Datum dospijeća')
                    ->default(today())
                    ->native(false)
                    ->displayFormat('d.m.Y.')
                    ->columnSpan(1),

                DatePicker::make('datum_isporuke')
                    ->label('Datum isporuke')
                    ->default(today())
                    ->native(false)
                    ->displayFormat('d.m.Y.')
                    ->columnSpan(1),

                TextInput::make('mjesto_izdavanja')
                    ->label('Mjesto izdavanja')
                    ->default(fn () => filament()->getTenant()->mjesto)
                    ->columnSpan(1),

                Select::make('nacin_placanja')
                    ->label('Način plaćanja')
                    ->options([
                        'transakcijski' => 'Transakcijski račun',
                        'gotovina'      => 'Gotovina',
                        'kartica'       => 'Kartica',
                        'virman'        => 'Virman',
                    ])
                    ->default('transakcijski')
                    ->native(false)
                    ->columnSpan(1),

                Textarea::make('napomena')
                    ->label('Napomena')
                    ->default(fn () => filament()->getTenant()->napomena)
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(3),

            // Stavke - Repeater s pretragom usluga
            Section::make('Stavke')->schema([
                Repeater::make('stavke')
                    ->relationship()
                    ->schema([
                        // Red 1: Usluga, Naziv, Opis
                        Select::make('usluga_id')
                            ->label('Usluga / Proizvod')
                            ->options(fn () => Usluga::where('tvrtka_id', filament()->getTenant()->id)
                                ->orderBy('naziv')
                                ->pluck('naziv', 'id'))
                            ->searchable()
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
                                    $set('jedinica_mjere', $usluga->jedinica_mjere);
                                }
                            })
                            ->columnSpan(4),

                        TextInput::make('naziv')
                            ->label('Naziv')
                            ->required()
                            ->columnSpan(4),

                        TextInput::make('opis')
                            ->label('Opis')
                            ->columnSpan(4),

                        // Red 2: JM, Količina, Cijena, Rabat, PDV, Ukupno
                        TextInput::make('jedinica_mjere')
                            ->label('JM')
                            ->default('kom')
                            ->columnSpan(1),

                        TextInput::make('kolicina')
                            ->label('Količina')
                            ->numeric()
                            ->default(1)
                            ->step(0.001)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))
                            ->columnSpan(2),

                        TextInput::make('cijena')
                            ->label('Cijena (bez PDV)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))
                            ->columnSpan(2),

                        TextInput::make('rabat_posto')
                            ->label('Rabat %')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('%')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))
                            ->columnSpan(2),

                        Select::make('pdv_stopa')
                            ->label('PDV %')
                            ->options(Usluga::pdvStope())
                            ->placeholder('PDV')
                            ->default(null)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajStavku($get, $set))
                            ->columnSpan(2),

                        TextInput::make('ukupno')
                            ->label('Ukupno €')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('€')
                            ->columnSpan(3),
                    ])
                    ->columns(12)
                    ->addActionLabel('+ Dodaj stavku')
                    ->reorderable()
                    ->cloneable()
                    ->defaultItems(1)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajUkupno($get, $set)),
            ]),

            // Sažetak
            Section::make('Ukupno')->schema([
                TextInput::make('ukupno_osnovica')
                    ->label('Osnovica')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('€'),

                TextInput::make('ukupno_rabat')
                    ->label('Rabat')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('€'),

                TextInput::make('ukupno_pdv')
                    ->label('PDV')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('€'),

                TextInput::make('ukupno')
                    ->label('UKUPNO za platiti')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('€'),
            ])->columns(4),
        ]);
    }

    protected static function izracunajStavku(Get $get, Set $set): void
    {
        $cijena   = (float) ($get('cijena') ?? 0);
        $kolicina = (float) ($get('kolicina') ?? 1);
        $rabat    = (float) ($get('rabat_posto') ?? 0);
        $pdv      = (float) ($get('pdv_stopa') ?? 0);

        $bruto     = $cijena * $kolicina;
        $rabatIzn  = $bruto * ($rabat / 100);
        $neto      = $bruto - $rabatIzn;
        $ukupno    = $neto + ($neto * ($pdv / 100));

        $set('ukupno', round($ukupno, 2));
    }

    protected static function izracunajUkupno(Get $get, Set $set): void
    {
        $stavke   = $get('stavke') ?? [];
        $osnovica = 0;
        $rabat    = 0;
        $pdv      = 0;

        foreach ($stavke as $stavka) {
            $cijena   = (float) ($stavka['cijena'] ?? 0);
            $kolicina = (float) ($stavka['kolicina'] ?? 1);
            $rabatP   = (float) ($stavka['rabat_posto'] ?? 0);
            $pdvS     = (float) ($stavka['pdv_stopa'] ?? 0);

            $bruto      = $cijena * $kolicina;
            $rabatIzn   = $bruto * ($rabatP / 100);
            $neto       = $bruto - $rabatIzn;

            $osnovica += $bruto;
            $rabat    += $rabatIzn;
            $pdv      += $neto * ($pdvS / 100);
        }

        $set('ukupno_osnovica', round($osnovica, 2));
        $set('ukupno_rabat',    round($rabat, 2));
        $set('ukupno_pdv',      round($pdv, 2));
        $set('ukupno',          round($osnovica - $rabat + $pdv, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('broj')
                    ->label('Broj')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('datum_izdavanja')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('klijent.naziv')
                    ->label('Klijent')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ukupno')
                    ->label('Ukupno')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nacin_placanja')
                    ->label('Način plaćanja')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'transakcijski' => 'transakcijski',
                        'gotovina'      => 'gotovina',
                        'kartica'       => 'kartica',
                        'virman'        => 'virman',
                        default         => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'nacrt',
                        'success' => 'final',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('placen_at')
                    ->label('Plaćanje')
                    ->dateTime('d.m.Y. H:i')
                    ->placeholder('NIJE')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('fiskaliziran_at')
                    ->label('Fisk.')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->fiskaliziran_at
                        ? 'Fiskalizirano: ' . $record->fiskaliziran_at->format('d.m.Y. H:i') . "\nJIR: " . $record->jir
                        : 'Nije fiskalizirano')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('poslan_eracun_at')
                    ->label('eRač.')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-airplane')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->poslan_eracun_at
                        ? 'eRačun poslan: ' . $record->poslan_eracun_at->format('d.m.Y. H:i') . ($record->eracun_poruka_id ? "\nID: " . $record->eracun_poruka_id : '')
                        : 'eRačun nije poslan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'nacrt' => 'Nacrt',
                        'final' => 'Final',
                    ]),

                Filter::make('placeni')
                    ->label('Plaćeni')
                    ->query(fn (Builder $q) => $q->whereNotNull('placen_at')),

                Filter::make('neplaceni')
                    ->label('Neplaćeni')
                    ->query(fn (Builder $q) => $q->whereNull('placen_at')),

                Tables\Filters\SelectFilter::make('godina')
                    ->label('Godina')
                    ->options(fn () => Racun::where('tvrtka_id', filament()->getTenant()->id)
                        ->distinct()
                        ->orderByDesc('godina')
                        ->pluck('godina', 'godina')),
            ])
            ->actions([
                Tables\Actions\Action::make('eracun')
                    ->label('Preuzmi eRačun XML')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Preuzmi eRačun (UBL 2.1 XML)')
                    ->action(function (Racun $r) {
                        try {
                            $xml = EracunService::buildCiusHrXml($r);
                            return response()->streamDownload(
                                fn () => print($xml),
                                'eRacun_' . $r->broj . '.xml',
                                ['Content-Type' => 'application/xml']
                            );
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Greška pri generiranju eRačuna')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('pdf')
                    ->label('Preuzmi PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Preuzmi PDF')
                    ->action(function (Racun $r) {
                        $pdfOutput = static::generirajPdf($r);
                        return response()->streamDownload(
                            fn () => print($pdfOutput),
                            'Racun_' . $r->broj . '.pdf'
                        );
                    }),

                Tables\Actions\Action::make('arhiviraj')
                    ->icon(fn (Racun $r) => $r->arhiviran_at ? 'heroicon-o-archive-box-arrow-down' : 'heroicon-o-archive-box')
                    ->color(fn (Racun $r) => $r->arhiviran_at ? 'success' : 'gray')
                    ->iconButton()
                    ->tooltip(fn (Racun $r) => $r->arhiviran_at ? 'Arhivirano ' . $r->arhiviran_at->format('d.m.Y. H:i') : 'Arhiviraj')
                    ->requiresConfirmation()
                    ->modalHeading('Arhiviraj račun')
                    ->modalDescription(fn (Racun $r) => $r->arhiviran_at
                        ? 'Račun je već arhiviran ' . $r->arhiviran_at->format('d.m.Y. H:i') . '. Želiš ga ponovo arhivirati?'
                        : 'Spremi PDF računa na server.')
                    ->action(function (Racun $r) {
                        $pdfOutput = static::generirajPdf($r);
                        $putanja   = 'racuni/' . $r->tvrtka_id . '/' . $r->godina . '/Racun_' . $r->broj . '.pdf';
                        Storage::put($putanja, $pdfOutput);
                        $r->update(['arhiviran_at' => now()]);

                        Notification::make()
                            ->title('Račun arhiviran')
                            ->body('Spremnjen: ' . $putanja)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('posalji')
                    ->label('Pošalji')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->iconButton()
                    ->tooltip('Pošalji e-mailom')
                    ->form(function (Racun $r): array {
                        $r->load(['tvrtka', 'klijent']);
                        $postavke = TvrtkaPostavke::where('tvrtka_id', $r->tvrtka_id)->first();

                        $defaultPredlozak = $postavke?->racun_email_predlozak
                            ?? "Poštovani {klijent},\n\nu privitku se nalazi račun broj {broj}.\n\nSrdačan pozdrav,\n{tvrtka}, vl. {vlasnik}";

                        $poruka = str_replace(
                            ['{klijent}', '{broj}', '{datum}', '{ukupno}', '{tvrtka}', '{vlasnik}'],
                            [
                                $r->klijent->naziv ?? '',
                                $r->broj,
                                $r->datum_izdavanja->format('d.m.Y.'),
                                number_format((float) $r->ukupno, 2, ',', '.') . ' €',
                                $r->tvrtka->naziv ?? '',
                                $r->tvrtka->vlasnik ?? '',
                            ],
                            $defaultPredlozak
                        );

                        return [
                            TextInput::make('od')
                                ->label('Pošiljatelj')
                                ->default($postavke?->smtp_from_email ?? '')
                                ->required(),

                            TextInput::make('prima')
                                ->label('Prima')
                                ->email()
                                ->default($r->klijent->email ?? '')
                                ->required(),

                            TextInput::make('cc')
                                ->label('CC')
                                ->email()
                                ->placeholder('kopija@mail.hr'),

                            Textarea::make('poruka')
                                ->label('Poruka')
                                ->default($poruka)
                                ->rows(8)
                                ->helperText('Dostupni tagovi: {klijent}, {broj}, {datum}, {ukupno}, {tvrtka}, {vlasnik}'),

                            FileUpload::make('dodatni_privitci')
                                ->label('Dodatni privitci')
                                ->multiple()
                                ->disk('local')
                                ->directory('tmp-privitci')
                                ->preserveFilenames(),
                        ];
                    })
                    ->modalHeading(fn (Racun $r) => 'Pošalji račun ' . $r->broj)
                    ->modalSubmitActionLabel('Pošalji')
                    ->action(function (Racun $r, array $data) {
                        $r->load(['stavke', 'tvrtka', 'klijent']);
                        $postavke = TvrtkaPostavke::where('tvrtka_id', $r->tvrtka_id)->first();

                        // Dinamički SMTP
                        if ($postavke?->smtp_host) {
                            config([
                                'mail.mailers.tvrtka' => [
                                    'transport'  => 'smtp',
                                    'host'       => $postavke->smtp_host,
                                    'port'       => $postavke->smtp_port ?? 587,
                                    'encryption' => $postavke->smtp_sigurnost === 'none' ? null : $postavke->smtp_sigurnost,
                                    'username'   => $postavke->smtp_user,
                                    'password'   => $postavke->smtp_pass,
                                    'timeout'    => null,
                                ],
                                'mail.from' => [
                                    'address' => $data['od'],
                                    'name'    => $postavke->smtp_from_name ?? $r->tvrtka->naziv,
                                ],
                            ]);
                            $mailer = Mail::mailer('tvrtka');
                        } else {
                            $mailer = Mail::mailer();
                        }

                        $pdfOutput       = static::generirajPdf($r);
                        $pdfNaziv        = 'Racun_' . $r->broj . '.pdf';
                        $dodatniPrivitci = [];

                        foreach ($data['dodatni_privitci'] ?? [] as $file) {
                            $dodatniPrivitci[] = Storage::disk('local')->path($file);
                        }

                        $mailable = (new RacunMail($data['poruka'], $pdfOutput, $pdfNaziv, $dodatniPrivitci))
                            ->subject('Račun ' . $r->broj . ' - ' . ($r->tvrtka->naziv ?? ''));

                        $send = $mailer->to($data['prima']);
                        if (! empty($data['cc'])) {
                            $send = $send->cc($data['cc']);
                        }
                        $send->send($mailable);

                        // Počisti tmp privitke
                        foreach ($data['dodatni_privitci'] ?? [] as $file) {
                            Storage::disk('local')->delete($file);
                        }

                        Notification::make()
                            ->title('E-mail poslan')
                            ->body('Račun ' . $r->broj . ' poslan na ' . $data['prima'])
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('posalji_eracun')
                    ->icon(fn (Racun $r) => $r->poslan_eracun_at ? 'heroicon-o-paper-airplane' : 'heroicon-o-arrow-up-tray')
                    ->color(fn (Racun $r) => $r->poslan_eracun_at ? 'info' : 'gray')
                    ->iconButton()
                    ->tooltip(fn (Racun $r) => $r->poslan_eracun_at
                        ? 'eRačun poslan ' . $r->poslan_eracun_at->format('d.m.Y. H:i')
                        : 'Pošalji eRačun na FINA')
                    ->visible(function () {
                        $postavke = TvrtkaPostavke::where('tvrtka_id', filament()->getTenant()->id)->first();
                        return $postavke?->eracun_aktivan ?? false;
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Racun $r) => $r->poslan_eracun_at ? 'Ponovo pošalji eRačun' : 'Pošalji eRačun')
                    ->modalDescription(fn (Racun $r) => $r->poslan_eracun_at
                        ? 'eRačun je već poslan ' . $r->poslan_eracun_at->format('d.m.Y. H:i') . '. Pošalji ponovo?'
                        : 'Pošalji račun ' . $r->broj . ' na FINA eRačun servis.')
                    ->action(function (Racun $r) {
                        $postavke = TvrtkaPostavke::where('tvrtka_id', $r->tvrtka_id)->first();

                        try {
                            $rezultat = EracunService::posalji($r, $postavke);

                            $r->update([
                                'poslan_eracun_at'  => now(),
                                'eracun_poruka_id'  => $rezultat['poruka_id'],
                            ]);

                            Notification::make()
                                ->title('eRačun poslan')
                                ->body('Račun ' . $r->broj . ' uspješno poslan na FINA.' .
                                    ($rezultat['poruka_id'] ? ' ID: ' . $rezultat['poruka_id'] : ''))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Greška pri slanju eRačuna')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('fiskaliziraj')
                    ->icon(fn (Racun $r) => $r->fiskaliziran_at ? 'heroicon-o-shield-check' : 'heroicon-o-shield-exclamation')
                    ->color(fn (Racun $r) => $r->fiskaliziran_at ? 'success' : 'warning')
                    ->iconButton()
                    ->tooltip(fn (Racun $r) => $r->fiskaliziran_at
                        ? 'Fiskalizirano ' . $r->fiskaliziran_at->format('d.m.Y. H:i')
                        : 'Fiskaliziraj račun')
                    ->visible(fn (Racun $r) => FiskalizacijaService::trebaMFiskalizirati($r) && ! $r->jir)
                    ->requiresConfirmation()
                    ->modalHeading(fn (Racun $r) => $r->fiskaliziran_at ? 'Ponovo fiskaliziraj' : 'Fiskalizacija računa')
                    ->modalDescription(fn (Racun $r) => $r->fiskaliziran_at
                        ? 'Račun je već fiskaliziran ' . $r->fiskaliziran_at->format('d.m.Y. H:i') . '. Želiš ponovo poslati na Poreznu upravu?'
                        : 'Potvrdi slanje računa ' . $r->broj . ' na Poreznu upravu (PU).')
                    ->action(function (Racun $r) {
                        try {
                            $postavke = \App\Models\TvrtkaPostavke::where('tvrtka_id', $r->tvrtka_id)->first();
                            $demo = $postavke?->fiskalizacija_demo ?? app()->environment('local', 'testing');
                            $result = FiskalizacijaService::fiskaliziraj($r, $demo);
                            Notification::make()
                                ->title('Račun fiskaliziran')
                                ->body('ZKI: ' . substr($result['zki'], 0, 20) . '...')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Greška pri fiskalizaciji')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('placeno')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Označi kao plaćeno')
                    ->visible(fn (Racun $r) => $r->placen_at === null)
                    ->requiresConfirmation()
                    ->action(fn (Racun $r) => $r->update(['placen_at' => now()])),

                Tables\Actions\Action::make('neplaceno')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->tooltip('Označi kao neplaćeno')
                    ->visible(fn (Racun $r) => $r->placen_at !== null)
                    ->action(fn (Racun $r) => $r->update(['placen_at' => null])),

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

    protected static function generirajPdf(Racun $r): string
    {
        $r->load(['stavke', 'tvrtka', 'klijent']);
        $barkod = null;
        try {
            $barkod = Hub3Service::generirajBarkodBase64($r);
        } catch (\Throwable) {}

        return Pdf::loadView('pdf.racun', [
            'racun'        => $r,
            'tvrtka'       => $r->tvrtka,
            'klijent'      => $r->klijent,
            'barkodBase64' => $barkod,
        ])->setPaper('a4')->output();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRacuni::route('/'),
            'create' => Pages\CreateRacun::route('/create'),
            'edit'   => Pages\EditRacun::route('/{record}/edit'),
        ];
    }
}
