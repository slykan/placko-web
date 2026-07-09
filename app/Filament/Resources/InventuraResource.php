<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventuraResource\Pages;
use App\Models\Inventura;
use App\Models\Skladiste;
use App\Services\ZalihaService;
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

class InventuraResource extends Resource
{
    protected static ?string $model = Inventura::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Inventura';

    protected static ?string $modelLabel = 'Inventura';

    protected static ?string $pluralModelLabel = 'Inventure';

    protected static ?string $slug = 'inventure';

    protected static ?string $navigationGroup = 'Skladište';

    protected static ?int $navigationSort = 10;

    protected static ?string $tenantRelationshipName = 'inventure';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Select::make('skladiste_id')->label('Skladište')
                    ->options(fn () => Skladiste::where('tvrtka_id', filament()->getTenant()->id)
                        ->orderBy('naziv')->pluck('naziv', 'id'))
                    ->default(fn () => ZalihaService::zadanoSkladiste(filament()->getTenant()->id)->id)
                    ->required()->native(false)
                    ->disabled(fn (string $context) => $context === 'edit')
                    ->dehydrated(),
                DatePicker::make('datum')->label('Datum')
                    ->default(today())->required()->native(false)->displayFormat('d.m.Y.'),
                Textarea::make('napomena')->label('Napomena')->rows(2)->columnSpanFull(),
            ])->columns(3),

            Section::make('Stavke')->schema([
                Repeater::make('stavke')->schema([
                    Hidden::make('id'),
                    Hidden::make('usluga_id'),
                    TextInput::make('naziv')->label('Proizvod')->disabled()->dehydrated(false)->columnSpan(5),
                    TextInput::make('ocekivana_kolicina')->label('Očekivano')->disabled()->dehydrated()->numeric()->columnSpan(2),
                    TextInput::make('stvarna_kolicina')->label('Stvarno stanje')->numeric()->step(0.001)->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $stvarno = $get('stvarna_kolicina');
                            $ocekivano = (float) ($get('ocekivana_kolicina') ?? 0);
                            $set('razlika', $stvarno === null || $stvarno === '' ? null : round((float) $stvarno - $ocekivano, 3));
                        })->columnSpan(2),
                    TextInput::make('razlika')->label('Razlika')->disabled()->dehydrated(false)->columnSpan(3),
                ])->columns(12)
                    ->disableItemCreation()
                    ->disableItemDeletion()
                    ->disableItemMovement()
                    ->defaultItems(0),
            ])->visible(fn (string $context) => $context === 'edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('datum')->label('Datum')->date('d.m.Y.')->sortable(),
            Tables\Columns\TextColumn::make('skladiste.naziv')->label('Skladište')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Status')
                ->badge()
                ->colors(['warning' => 'u_tijeku', 'success' => 'zavrsena'])
                ->formatStateUsing(fn (string $state) => $state === 'zavrsena' ? 'Završena' : 'U tijeku'),
            Tables\Columns\TextColumn::make('stavke_count')->label('Stavki')
                ->getStateUsing(fn (Inventura $inventura) => $inventura->stavke()->count()),
        ])->defaultSort('datum', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('Otvori')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('Obriši')->iconButton()
                    ->visible(fn (Inventura $inventura) => $inventura->status !== 'zavrsena'),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('Obriši označene'),
            ])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tvrtka_id', filament()->getTenant()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventure::route('/'),
            'create' => Pages\CreateInventura::route('/create'),
            'edit' => Pages\EditInventura::route('/{record}/edit'),
        ];
    }
}
