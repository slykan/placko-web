<?php

namespace App\Filament\Pages;

use App\Models\Tvrtka;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterTvrtka extends RegisterTenant
{
    protected static string $view = 'filament.pages.register-tvrtka';

    public function getCachedSubNavigation(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return 'Dodaj obrt / tvrtku';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Osnovni podaci')->schema([
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('logos')
                    ->columnSpanFull(),

                TextInput::make('naziv')
                    ->label('Naziv')
                    ->required()
                    ->columnSpanFull(),

                Select::make('vrsta_poslovanja')
                    ->label('Vrsta poslovanja')
                    ->options(Tvrtka::vrstePoslovanja())
                    ->default('pausalni_obrt')
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),

                TextInput::make('vlasnik')
                    ->label('Vlasnik / direktor'),

                TextInput::make('oib')
                    ->label('OIB')
                    ->length(11),

                TextInput::make('nkd')
                    ->label('NKD'),

                TextInput::make('djelatnost')
                    ->label('Djelatnost'),
            ])->columns(2),

            Section::make('Adresa')->schema([
                TextInput::make('adresa')
                    ->label('Adresa'),

                TextInput::make('mjesto')
                    ->label('Mjesto'),

                TextInput::make('po_broj')
                    ->label('Poštanski broj'),
            ])->columns(3),

            Section::make('Bankovni podaci')->schema([
                TextInput::make('iban')
                    ->label('IBAN'),

                TextInput::make('swift')
                    ->label('SWIFT'),

                TextInput::make('banka')
                    ->label('Banka'),
            ])->columns(3),

            Section::make('Kontakt')->schema([
                TextInput::make('kontakt_broj')
                    ->label('Kontakt broj')
                    ->tel(),

                TextInput::make('email')
                    ->label('Email')
                    ->email(),

                TextInput::make('web_mjesto')
                    ->label('Web mjesto')
                    ->url(),

                TextInput::make('oznaka_operatera')
                    ->label('Oznaka operatera (npr. AF)'),
            ])->columns(2),

            Section::make('PDV')->schema([
                Toggle::make('u_sustavu_pdv')
                    ->label('U sustavu PDV-a')
                    ->helperText('Ako ste u sustavu PDV-a, PDV će se prikazivati na računima')
                    ->default(false)
                    ->inline(false),
            ]),

            Section::make('Napomena na računu')->schema([
                Textarea::make('napomena')
                    ->label('Napomena (prikazuje se na dnu računa)')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    protected function handleRegistration(array $data): Tvrtka
    {
        $tvrtka = Tvrtka::create($data);
        $tvrtka->users()->attach(auth()->user());

        return $tvrtka;
    }
}
