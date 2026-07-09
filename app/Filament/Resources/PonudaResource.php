<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PonudaResource\Pages;
use App\Mail\PonudaMail;
use App\Models\Klijent;
use App\Models\Ponuda;
use App\Models\TvrtkaPostavke;
use App\Models\Usluga;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PonudaResource extends Resource
{
    protected static ?string $model = Ponuda::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Ponude';

    protected static ?string $modelLabel = 'Ponuda';

    protected static ?string $pluralModelLabel = 'Ponude';

    protected static ?string $slug = 'ponude';

    protected static ?int $navigationSort = 5;

    protected static ?string $tenantRelationshipName = 'ponude';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Select::make('klijent_id')->label('Kupac')
                    ->options(fn () => Klijent::where('tvrtka_id', filament()->getTenant()->id)
                        ->orderBy('naziv')->pluck('naziv', 'id'))
                    ->searchable()->required()->columnSpanFull(),
                TextInput::make('broj')->label('Broj ponude')->disabled()->dehydrated(),
                DatePicker::make('datum_izdavanja')->label('Datum izdavanja')
                    ->default(today())->required()->native(false)->displayFormat('d.m.Y.'),
                TextInput::make('vrijeme_izdavanja')->label('Vrijeme izdavanja')
                    ->default(now()->format('H:i'))->placeholder('HH:MM')->mask('99:99')
                    ->rule('regex:/^([01]\d|2[0-3]):([0-5]\d)$/'),
                TextInput::make('mjesto_izdavanja')->label('Mjesto izdavanja')
                    ->default(fn () => filament()->getTenant()->mjesto),
                TextInput::make('valjanost_dana')->label('Valjanost ponude (dana)')
                    ->numeric()->integer()->minValue(1)->default(30)->required()->suffix('dana'),
                TextInput::make('rok_ispostave')->label('Rok ispostave')
                    ->placeholder('npr. 15 radnih dana')->required(),
                Textarea::make('napomena')->label('Napomena')
                    ->default(fn () => filament()->getTenant()->napomena)->rows(2)->columnSpanFull(),
            ])->columns(3),

            Section::make('Stavke')->schema([
                Repeater::make('stavke')->schema([
                    Hidden::make('id'),
                    Select::make('usluga_id')->label('Usluga / Proizvod')
                        ->options(fn () => Usluga::where('tvrtka_id', filament()->getTenant()->id)
                            ->orderBy('naziv')->pluck('naziv', 'id'))
                        ->searchable()->nullable()->live()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            if ($state && ($usluga = Usluga::find($state))) {
                                $set('naziv', $usluga->naziv);
                                $set('cijena', $usluga->cijena);
                                $set('pdv_stopa', $usluga->pdv_stopa);
                                $set('jedinica_mjere', $usluga->jedinica_mjere);
                            }
                            static::izracunajSve($get, $set);
                        })->columnSpan(4),
                    TextInput::make('naziv')->label('Naziv')->required()->columnSpan(4),
                    TextInput::make('opis')->label('Opis')->columnSpan(4),
                    TextInput::make('jedinica_mjere')->label('JM')->default('kom')->columnSpan(1),
                    TextInput::make('kolicina')->label('Količina')->numeric()->required()->default(1)
                        ->step(0.001)->minValue(0.001)->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajSve($get, $set))->columnSpan(2),
                    TextInput::make('cijena')->label('Cijena (bez PDV)')->numeric()->required()->default(0)
                        ->step(0.01)->prefix('€')->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajSve($get, $set))->columnSpan(2),
                    TextInput::make('rabat_posto')->label('Rabat %')->numeric()->default(0)->step(0.01)
                        ->suffix('%')->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajSve($get, $set))->columnSpan(2),
                    Select::make('pdv_stopa')->label('PDV %')->options(Usluga::pdvStope())->default(null)
                        ->native(false)->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::izracunajSve($get, $set))->columnSpan(2),
                    TextInput::make('ukupno')->label('Ukupno €')->numeric()->disabled()->dehydrated()
                        ->prefix('€')->columnSpan(3),
                ])->columns(12)->addActionLabel('+ Dodaj stavku')->reorderable()->cloneable()
                    ->defaultItems(0)->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::postaviUkupno($get('stavke') ?? [], $set)),
            ]),

            Section::make('Ukupno')->schema([
                TextInput::make('ukupno_osnovica')->label('Osnovica')->disabled()->dehydrated()->prefix('€'),
                TextInput::make('ukupno_rabat')->label('Rabat')->disabled()->dehydrated()->prefix('€'),
                TextInput::make('ukupno_pdv')->label('PDV')->disabled()->dehydrated()->prefix('€'),
                TextInput::make('ukupno')->label('UKUPNO')->disabled()->dehydrated()->prefix('€'),
            ])->columns(4),
        ]);
    }

    protected static function izracunajSve(Get $get, Set $set): void
    {
        $bruto = (float) ($get('cijena') ?? 0) * (float) ($get('kolicina') ?? 1);
        $neto = $bruto * (1 - (float) ($get('rabat_posto') ?? 0) / 100);
        $set('ukupno', round($neto * (1 + (float) ($get('pdv_stopa') ?? 0) / 100), 2));
        static::postaviUkupno($get('../../stavke') ?? [], $set, '../../');
    }

    protected static function postaviUkupno(array $stavke, Set $set, string $prefix = ''): void
    {
        $osnovica = $rabat = $pdv = 0;
        foreach ($stavke as $stavka) {
            $bruto = (float) ($stavka['cijena'] ?? 0) * (float) ($stavka['kolicina'] ?? 1);
            $r = $bruto * ((float) ($stavka['rabat_posto'] ?? 0) / 100);
            $neto = $bruto - $r;
            $osnovica += $bruto;
            $rabat += $r;
            $pdv += $neto * ((float) ($stavka['pdv_stopa'] ?? 0) / 100);
        }
        $set($prefix.'ukupno_osnovica', round($osnovica, 2));
        $set($prefix.'ukupno_rabat', round($rabat, 2));
        $set($prefix.'ukupno_pdv', round($pdv, 2));
        $set($prefix.'ukupno', round($osnovica - $rabat + $pdv, 2));
    }

    public static function filtrirajPrazneStavke(array $stavke): array
    {
        return array_values(array_filter($stavke, fn ($s) => filled($s['usluga_id'] ?? null) || filled($s['naziv'] ?? null)
            || filled($s['opis'] ?? null) || (float) ($s['cijena'] ?? 0) !== 0.0
        ));
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('broj')->label('Broj')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('datum_izdavanja')->label('Datum')->date('d.m.Y.')->sortable(),
            Tables\Columns\TextColumn::make('klijent.naziv')->label('Klijent')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('valjanost_dana')->label('Valjanost')->suffix(' dana')->sortable(),
            Tables\Columns\TextColumn::make('rok_ispostave')->label('Rok ispostave')->searchable(),
            Tables\Columns\TextColumn::make('ukupno')->label('Ukupno')->money('EUR')->sortable(),
            Tables\Columns\IconColumn::make('prihvacena_at')->label('Prihvaćena')
                ->getStateUsing(fn (Ponuda $ponuda) => (bool) $ponuda->prihvacena_at)
                ->boolean()
                ->trueIcon('heroicon-o-check-badge')
                ->falseIcon('heroicon-o-clock')
                ->trueColor('success')
                ->falseColor('gray')
                ->tooltip(fn (Ponuda $ponuda) => $ponuda->prihvacena_at
                    ? 'Prihvaćena '.$ponuda->prihvacena_at->format('d.m.Y. H:i')
                    : 'Klijent još nije prihvatio ponudu'),
        ])->defaultSort('datum_izdavanja', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('godina')->label('Godina')
                    ->options(fn () => Ponuda::where('tvrtka_id', filament()->getTenant()->id)
                        ->distinct()->orderByDesc('godina')->pluck('godina', 'godina')),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')->label('Preuzmi PDF')
                    ->icon('heroicon-o-arrow-down-tray')->color('gray')->iconButton()->tooltip('Preuzmi PDF')
                    ->action(fn (Ponuda $ponuda) => response()->streamDownload(
                        fn () => print (static::generirajPdf($ponuda)), 'Ponuda_'.$ponuda->broj.'.pdf'
                    )),

                Tables\Actions\Action::make('posalji')
                    ->label('Pošalji')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->iconButton()
                    ->tooltip('Pošalji e-mailom')
                    ->form(function (Ponuda $ponuda): array {
                        $ponuda->load(['tvrtka', 'klijent']);

                        $defaultPredlozak = "Poštovani {klijent},\n\nu privitku se nalazi ponuda broj {broj}, s rokom valjanosti {valjanost} dana.\n\nSrdačan pozdrav,\n{tvrtka}, vl. {vlasnik}";

                        $poruka = str_replace(
                            ['{klijent}', '{broj}', '{datum}', '{valjanost}', '{ukupno}', '{tvrtka}', '{vlasnik}'],
                            [
                                $ponuda->klijent->naziv ?? '',
                                $ponuda->broj,
                                $ponuda->datum_izdavanja->format('d.m.Y.'),
                                $ponuda->valjanost_dana,
                                number_format((float) $ponuda->ukupno, 2, ',', '.').' €',
                                $ponuda->tvrtka->naziv ?? '',
                                $ponuda->tvrtka->vlasnik ?? '',
                            ],
                            $defaultPredlozak
                        );

                        $postavke = TvrtkaPostavke::where('tvrtka_id', $ponuda->tvrtka_id)->first();

                        return [
                            TextInput::make('od')->label('Pošiljatelj')
                                ->default($postavke?->smtp_from_email ?? '')->required(),
                            TextInput::make('prima')->label('Prima')->email()
                                ->default($ponuda->klijent->email ?? '')->required(),
                            TextInput::make('cc')->label('CC')->email()->placeholder('kopija@mail.hr'),
                            Textarea::make('poruka')->label('Poruka')->default($poruka)->rows(8)
                                ->helperText('Dostupni tagovi: {klijent}, {broj}, {datum}, {valjanost}, {ukupno}, {tvrtka}, {vlasnik}'),
                            FileUpload::make('dodatni_privitci')->label('Dodatni privitci')
                                ->multiple()->disk('local')->directory('tmp-privitci')->preserveFilenames(),
                        ];
                    })
                    ->modalHeading(fn (Ponuda $ponuda) => 'Pošalji ponudu '.$ponuda->broj)
                    ->modalSubmitActionLabel('Pošalji')
                    ->action(function (Ponuda $ponuda, array $data) {
                        $ponuda->load(['stavke', 'tvrtka', 'klijent']);
                        $postavke = TvrtkaPostavke::where('tvrtka_id', $ponuda->tvrtka_id)->first();

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
                                    'name' => $postavke->smtp_from_name ?? $ponuda->tvrtka->naziv,
                                ],
                            ]);
                            $mailer = Mail::mailer('tvrtka');
                        } else {
                            $mailer = Mail::mailer();
                        }

                        $pdfOutput = static::generirajPdf($ponuda);
                        $pdfNaziv = 'Ponuda_'.$ponuda->broj.'.pdf';
                        $dodatniPrivitci = [];

                        foreach ($data['dodatni_privitci'] ?? [] as $file) {
                            $dodatniPrivitci[] = Storage::disk('local')->path($file);
                        }

                        $prihvatiUrl = URL::signedRoute('ponuda.prihvati', ['ponuda' => $ponuda->id]);

                        $mailable = (new PonudaMail($data['poruka'], $pdfOutput, $pdfNaziv, $dodatniPrivitci, $ponuda->tvrtka, $prihvatiUrl))
                            ->subject('Ponuda '.$ponuda->broj.' - '.($ponuda->tvrtka->naziv ?? ''));

                        try {
                            $send = $mailer->to($data['prima']);
                            if (! empty($data['cc'])) {
                                $send = $send->cc($data['cc']);
                            }
                            $send->send($mailable);

                            Notification::make()
                                ->title('E-mail poslan')
                                ->body('Ponuda '.$ponuda->broj.' poslana na '.$data['prima'])
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Greška pri slanju e-maila')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }

                        foreach ($data['dodatni_privitci'] ?? [] as $file) {
                            Storage::disk('local')->delete($file);
                        }
                    }),

                Tables\Actions\EditAction::make()->label('Uredi')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('Obriši')->iconButton(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
            ])]);
    }

    public static function generirajPdf(Ponuda $ponuda): string
    {
        $ponuda->load(['stavke', 'tvrtka', 'klijent']);

        return Pdf::loadView('pdf.ponuda', [
            'ponuda' => $ponuda, 'tvrtka' => $ponuda->tvrtka, 'klijent' => $ponuda->klijent,
        ])->setPaper('a4')->output();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPonude::route('/'),
            'create' => Pages\CreatePonuda::route('/create'),
            'edit' => Pages\EditPonuda::route('/{record}/edit'),
        ];
    }
}
