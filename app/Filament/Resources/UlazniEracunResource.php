<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UlazniEracunResource\Pages;
use App\Models\UlazniEracun;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UlazniEracunResource extends Resource
{
    protected static ?string $model = UlazniEracun::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Ulazni eRačuni (URA)';

    protected static ?string $modelLabel = 'Ulazni eRačun';

    protected static ?string $pluralModelLabel = 'Ulazni eRačuni';

    protected static ?int $navigationSort = 5;

    protected static ?string $tenantRelationshipName = 'ulazniEracuni';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('status')
                ->label('Status')
                ->options([
                    'nova'       => 'Nova',
                    'pregledana' => 'Pregledana',
                    'prihvacena' => 'Prihvaćena',
                    'odbijena'   => 'Odbijena',
                ])
                ->native(false)
                ->required(),

            Textarea::make('napomena')
                ->label('Napomena / razlog odbijanja')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'nova',
                        'info'    => 'pregledana',
                        'success' => 'prihvacena',
                        'danger'  => 'odbijena',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'nova'       => 'Nova',
                        'pregledana' => 'Pregledana',
                        'prihvacena' => 'Prihvaćena',
                        'odbijena'   => 'Odbijena',
                        default      => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('datum_izdavanja')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('broj_racuna')
                    ->label('Broj računa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dobavljac_naziv')
                    ->label('Dobavljač')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('dobavljac_oib')
                    ->label('OIB')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('iznos')
                    ->label('Iznos')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('datum_dospijeca')
                    ->label('Dospijeće')
                    ->date('d.m.Y.')
                    ->sortable()
                    ->color(fn ($record) => $record->datum_dospijeca && $record->datum_dospijeca->isPast() && $record->status !== 'prihvacena'
                        ? 'danger' : null)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('primljeno_at')
                    ->label('Primljeno')
                    ->dateTime('d.m.Y. H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'nova'       => 'Nova',
                        'pregledana' => 'Pregledana',
                        'prihvacena' => 'Prihvaćena',
                        'odbijena'   => 'Odbijena',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('preuzmi_xml')
                    ->label('XML')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Preuzmi UBL XML')
                    ->visible(fn (UlazniEracun $r) => ! empty($r->xml))
                    ->action(function (UlazniEracun $r) {
                        return response()->streamDownload(
                            fn () => print($r->xml),
                            'eRacun_ulazni_' . ($r->broj_racuna ?? $r->id) . '.xml',
                            ['Content-Type' => 'application/xml']
                        );
                    }),

                Tables\Actions\Action::make('prihvati')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Prihvati')
                    ->visible(fn (UlazniEracun $r) => ! in_array($r->status, ['prihvacena']))
                    ->requiresConfirmation()
                    ->action(function (UlazniEracun $r) {
                        $r->update(['status' => 'prihvacena']);
                        Notification::make()->title('eRačun prihvaćen')->success()->send();
                    }),

                Tables\Actions\Action::make('odbij')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->tooltip('Odbij')
                    ->visible(fn (UlazniEracun $r) => $r->status !== 'odbijena')
                    ->form([
                        Textarea::make('napomena')
                            ->label('Razlog odbijanja')
                            ->rows(3),
                    ])
                    ->action(function (UlazniEracun $r, array $data) {
                        $r->update([
                            'status'   => 'odbijena',
                            'napomena' => $data['napomena'] ?? null,
                        ]);
                        Notification::make()->title('eRačun odbijen')->warning()->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Detalji'),
                    Tables\Actions\EditAction::make()->label('Uredi'),
                    Tables\Actions\DeleteAction::make()->label('Obriši'),
                ])->iconButton()->icon('heroicon-o-ellipsis-vertical')->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('oznaci_pregledane')
                        ->label('Označi kao pregledane')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['status' => 'pregledana']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUlazniEracuni::route('/'),
            'view'   => Pages\ViewUlazniEracun::route('/{record}'),
            'edit'   => Pages\EditUlazniEracun::route('/{record}/edit'),
        ];
    }
}
