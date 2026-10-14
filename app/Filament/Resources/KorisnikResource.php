<?php

namespace App\Filament\Resources;

use App\Enums\Plan;
use App\Filament\Resources\KorisnikResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Administracija korisnika i paketa (Starter / Pro / Business). Vidljivo samo administratorima.
 */
class KorisnikResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administrator';
    protected static ?string $navigationLabel = 'Korisnici';
    protected static ?string $modelLabel = 'korisnik';
    protected static ?string $pluralModelLabel = 'Korisnici';
    protected static ?string $slug = 'korisnici';
    protected static ?int $navigationSort = 1;

    // Korisnici nisu vezani uz tvrtku (tenanta) — admin vidi sve
    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Korisnik')->schema([
                Placeholder::make('ime')->label('Ime')->content(fn (?User $record) => $record?->name),
                Placeholder::make('mail')->label('E-mail')->content(fn (?User $record) => $record?->email),
                Placeholder::make('tvrtke_popis')->label('Tvrtke')
                    ->content(fn (?User $record) => $record?->tvrtke->pluck('naziv')->join(', ') ?: '—'),
                Placeholder::make('registriran')->label('Registriran')
                    ->content(fn (?User $record) => $record?->created_at?->format('d.m.Y. H:i')),
            ])->columns(2),

            Section::make('Paket')->schema([
                Select::make('plan')
                    ->label('Paket')
                    ->options(Plan::opcije())
                    ->required()
                    ->native(false),
                DatePicker::make('plan_do')
                    ->label('Vrijedi do')
                    ->helperText('Nakon ovog datuma korisnik automatski postaje Starter. Prazno = bez isteka.')
                    ->displayFormat('d.m.Y.')
                    ->native(false),
                Toggle::make('is_admin')
                    ->label('Administrator')
                    ->helperText('Administrator vidi izbornik Administrator i može mijenjati pakete.')
                    ->disabled(fn (?User $record) => $record?->is(auth()->user())),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Ime')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                Tables\Columns\SelectColumn::make('plan')
                    ->label('Paket')
                    ->options(Plan::opcije())
                    ->selectablePlaceholder(false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan_do')
                    ->label('Vrijedi do')
                    ->date('d.m.Y.')
                    ->placeholder('—')
                    ->color(fn (User $r) => $r->plan_do && $r->plan_do->isPast() ? 'danger' : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tvrtke_count')->label('Tvrtke')->counts('tvrtke')->alignCenter()->sortable(),
                Tables\Columns\IconColumn::make('is_admin')->label('Admin')->boolean()->trueIcon('heroicon-o-shield-check')->falseIcon('')->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')->label('Registriran')->dateTime('d.m.Y.')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')->label('Paket')->options(Plan::opcije()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKorisnici::route('/'),
            'edit' => Pages\EditKorisnik::route('/{record}/edit'),
        ];
    }
}
