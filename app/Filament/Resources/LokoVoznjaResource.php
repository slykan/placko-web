<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LokoVoznjaResource\Pages;
use App\Models\LokoVoznja;
use App\Models\RadniNalogRad;
use App\Models\Zaposlenik;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LokoVoznjaResource extends Resource
{
    protected static ?string $model = LokoVoznja::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Loko vožnja';

    protected static ?string $modelLabel = 'Loko vožnja';

    protected static ?string $pluralModelLabel = 'Loko vožnja';

    protected static ?string $slug = 'loko-voznja';

    protected static ?int $navigationSort = 8;

    protected static ?string $tenantRelationshipName = 'lokoVoznje';

    public static function can(string $action, ?Model $record = null): bool
    {
        return RadniNalogResource::dostupno() && parent::can($action, $record);
    }

    /** Mjeseci za izbornike: zadnjih 12, najnoviji prvi ("2026-11" => "11.2026."). */
    public static function mjeseci(): array
    {
        return collect(range(0, 11))->mapWithKeys(function (int $i) {
            $m = now()->startOfMonth()->subMonths($i);

            return [$m->format('Y-m') => $m->format('m.Y.')];
        })->all();
    }

    public static function form(Form $form): Form
    {
        $tvrtkaId = fn () => filament()->getTenant()->id;

        return $form->schema([
            Select::make('zaposlenik_id')->label('Zaposlenik')
                ->options(fn ($state) => Zaposlenik::opcije($tvrtkaId(), [$state]))
                ->searchable()->nullable()->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ($state && ($z = Zaposlenik::find($state))) {
                        $set('putnik', $z->ime_prezime);
                        $set('registracija', $z->registracija);
                    }
                }),
            TextInput::make('putnik')->label('Vozač (ime i prezime)')->required(),
            DatePicker::make('datum')->label('Datum')->default(today())->required()
                ->native(false)->displayFormat('d.m.Y.')->locale('hr')->firstDayOfWeek(1),
            TimePicker::make('vrijeme')->label('Vrijeme')->native(false)->seconds(false)->displayFormat('H:i')->format('H:i'),
            TextInput::make('relacija')->label('Relacija (od – do)')->required()->placeholder('npr. Osijek, Ilirska 27 – Josipovac – Osijek')->columnSpanFull(),
            TextInput::make('svrha')->label('Svrha')->required()->columnSpanFull(),
            TextInput::make('km')->label('Prijeđeno km')->numeric()->required()->minValue(0)->step(0.1),
            TextInput::make('registracija')->label('Registarska oznaka')->required()->maxLength(20)
                ->dehydrateStateUsing(fn (?string $state) => $state ? mb_strtoupper(trim($state)) : null),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('datum')->label('Datum')->date('d.m.Y.')->sortable()
                ->description(fn (LokoVoznja $v) => $v->vrijeme ? substr($v->vrijeme, 0, 5) : null),
            Tables\Columns\TextColumn::make('putnik')->label('Vozač')->searchable()->sortable()
                ->description(fn (LokoVoznja $v) => $v->registracija),
            Tables\Columns\TextColumn::make('relacija')->label('Relacija')->searchable()->limit(40)
                ->description(fn (LokoVoznja $v) => \Illuminate\Support\Str::limit($v->svrha, 50)),
            Tables\Columns\TextColumn::make('km')->label('km')->numeric(1, ',', '.')->sortable()
                ->color(fn (LokoVoznja $v) => (float) $v->km <= 0 ? 'danger' : null)
                ->tooltip(fn (LokoVoznja $v) => (float) $v->km <= 0 ? 'Upiši prijeđene kilometre' : null),
            Tables\Columns\TextColumn::make('iznos')->label('Iznos')
                ->getStateUsing(fn (LokoVoznja $v) => $v->iznos())->money('EUR', locale: 'hr'),
        ])->defaultSort('datum', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('mjesec')->label('Mjesec')->options(static::mjeseci())
                    ->default(now()->format('Y-m'))
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereBetween('datum', [Carbon::parse($data['value'].'-01')->startOfMonth(), Carbon::parse($data['value'].'-01')->endOfMonth()])
                        : $query),
                Tables\Filters\SelectFilter::make('zaposlenik_id')->label('Zaposlenik')
                    ->relationship('zaposlenik', 'ime_prezime', fn (Builder $query) => $query->where('tvrtka_id', filament()->getTenant()->id)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Uredi')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('Obriši')->iconButton(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
            ])]);
    }

    /**
     * Iz evidencije rada na radnim nalozima (dolasci zaposlenika) napravi retke loko vožnje za mjesec.
     * Već uvezeni dolasci (isti nalog, datum i vozač) se preskaču; kilometre korisnik upisuje sam.
     */
    public static function uveziIzRadnihNaloga(int $tvrtkaId, int $zaposlenikId, string $mjesec, ?string $polaziste, ?string $registracija): int
    {
        $z = Zaposlenik::where('tvrtka_id', $tvrtkaId)->findOrFail($zaposlenikId);
        $od = Carbon::parse($mjesec.'-01')->startOfMonth();
        $polaziste = trim((string) $polaziste);
        $dodano = 0;

        $radovi = RadniNalogRad::with('radniNalog.klijent')
            ->where('zaposlenik_id', $z->id)
            ->whereBetween('datum', [$od, $od->copy()->endOfMonth()])
            ->whereHas('radniNalog', fn ($q) => $q->where('tvrtka_id', $tvrtkaId))
            ->orderBy('datum')->orderBy('vrijeme_dolaska')->get();

        foreach ($radovi as $r) {
            $n = $r->radniNalog;
            $postoji = LokoVoznja::where('tvrtka_id', $tvrtkaId)->where('zaposlenik_id', $z->id)
                ->where('radni_nalog_id', $n->id)->whereDate('datum', $r->datum)->exists();
            if ($postoji) {
                continue;
            }
            $cilj = $n->lokacija ?: ($n->klijent?->mjesto ?? 'teren');

            LokoVoznja::create([
                'tvrtka_id' => $tvrtkaId,
                'zaposlenik_id' => $z->id,
                'radni_nalog_id' => $n->id,
                'putnik' => $z->ime_prezime,
                'datum' => $r->datum,
                'vrijeme' => $r->vrijeme_dolaska,
                'relacija' => $polaziste !== '' ? $polaziste.' – '.$cilj.' – '.$polaziste : $cilj,
                'svrha' => 'Radni nalog '.$n->broj.($n->klijent ? ' ('.$n->klijent->naziv.')' : ''),
                'km' => 0,
                'registracija' => $registracija ?: $z->registracija,
            ]);
            $dodano++;
        }

        return $dodano;
    }

    public static function generirajPdf(int $tvrtkaId, string $mjesec, ?int $zaposlenikId, ?string $putnik): string
    {
        $od = Carbon::parse($mjesec.'-01')->startOfMonth();
        $voznje = LokoVoznja::where('tvrtka_id', $tvrtkaId)
            ->whereBetween('datum', [$od, $od->copy()->endOfMonth()])
            ->when($zaposlenikId, fn ($q) => $q->where('zaposlenik_id', $zaposlenikId))
            ->when(! $zaposlenikId && $putnik, fn ($q) => $q->where('putnik', $putnik))
            ->orderBy('datum')->orderBy('vrijeme')->get();

        return Pdf::loadView('pdf.loko-voznja', [
            'tvrtka' => filament()->getTenant() ?? \App\Models\Tvrtka::find($tvrtkaId),
            'voznje' => $voznje,
            'mjesec' => $od,
            'vozac' => $voznje->first()?->putnik ?? $putnik,
        ])->setPaper('a4', 'landscape')->output();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLokoVoznje::route('/'),
        ];
    }
}
