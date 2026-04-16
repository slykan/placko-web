<?php

namespace App\Filament\Pages;

use App\Models\TvrtkaPostavke;
use App\Services\EracunService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Postavke extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Postavke';

    protected static ?string $title = 'Postavke';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.postavke';

    public ?array $korisnikData       = [];
    public ?array $smtpData           = [];
    public ?array $emailData          = [];
    public ?array $pretplateData      = [];
    public ?array $fiskalizacijaData  = [];
    public ?array $eracunData         = [];

    public function mount(): void
    {
        $user     = auth()->user();
        $postavke = TvrtkaPostavke::firstOrCreate(
            ['tvrtka_id' => filament()->getTenant()->id],
            ['pretplate_dani_upozorenja' => '30,15,1']
        );

        $this->korisnikForm->fill([
            'user_name'  => $user->name,
            'user_email' => $user->email,
        ]);

        $this->smtpForm->fill([
            'smtp_host'       => $postavke->smtp_host,
            'smtp_port'       => $postavke->smtp_port ?? 587,
            'smtp_user'       => $postavke->smtp_user,
            'smtp_sigurnost'  => $postavke->smtp_sigurnost ?? 'tls',
            'smtp_from_name'  => $postavke->smtp_from_name,
            'smtp_from_email' => $postavke->smtp_from_email,
        ]);

        $this->emailForm->fill([
            'racun_email_predlozak' => $postavke->racun_email_predlozak,
        ]);

        $this->pretplateForm->fill([
            'pretplate_dani_upozorenja' => $postavke->pretplate_dani_upozorenja ?? '30,15,1',
            'pretplate_email_predlozak' => $postavke->pretplate_email_predlozak,
        ]);

        $this->fiskalizacijaForm->fill([
            'fiskalizacija_aktivna' => $postavke->fiskalizacija_aktivna ?? false,
            'fiskalizacija_demo'    => $postavke->fiskalizacija_demo ?? false,
            'fina_cert_putanja'     => $postavke->fina_cert_putanja,
            'fis_prostor_oznaka'    => $postavke->fis_prostor_oznaka ?? '1',
            'fis_uredaj_oznaka'     => $postavke->fis_uredaj_oznaka ?? '1',
        ]);

        $this->eracunForm->fill([
            'eracun_aktivan'        => $postavke->eracun_aktivan ?? false,
            'eracun_demo'           => $postavke->eracun_demo ?? false,
            'eracun_middleware_url' => $postavke->eracun_middleware_url,
            'eracun_jks_uuid'       => $postavke->eracun_jks_uuid,
            'eracun_cert_putanja'   => $postavke->eracun_cert_putanja,
        ]);
    }

    protected function getForms(): array
    {
        return ['korisnikForm', 'smtpForm', 'emailForm', 'pretplateForm', 'fiskalizacijaForm', 'eracunForm'];
    }

    public function korisnikForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Korisnik')
                    ->description('Promjena korisničkih podataka i lozinke')
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Ime i prezime')
                            ->required(),

                        TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required(),

                        TextInput::make('user_password')
                            ->label('Nova lozinka')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->minLength(8),

                        TextInput::make('user_password_confirmation')
                            ->label('Ponovi lozinku')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->same('user_password'),
                    ])->columns(2),
            ])
            ->statePath('korisnikData');
    }

    public function smtpForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Email postavke (SMTP)')
                    ->description('Vlastiti SMTP server za slanje računa i podsjetnika')
                    ->schema([
                        TextInput::make('smtp_host')
                            ->label('SMTP Host'),

                        TextInput::make('smtp_port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->default(587),

                        TextInput::make('smtp_user')
                            ->label('SMTP User'),

                        TextInput::make('smtp_pass')
                            ->label('SMTP Lozinka')
                            ->password()
                            ->revealable()
                            ->helperText('Ostavi prazno ako ne želiš mjenjati lozinku'),

                        Select::make('smtp_sigurnost')
                            ->label('Sigurnost')
                            ->options([
                                'tls'  => 'TLS',
                                'ssl'  => 'SSL',
                                'none' => 'Bez sigurnosti',
                            ])
                            ->default('tls')
                            ->native(false),

                        TextInput::make('smtp_from_name')
                            ->label('From Name'),

                        TextInput::make('smtp_from_email')
                            ->label('From Email')
                            ->email(),
                    ])->columns(2),
            ])
            ->statePath('smtpData');
    }

    public function emailForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Predložak email-a za račune')
                    ->description('Poruka koja se šalje uz račun. Tagovi: {klijent}, {broj}, {datum}, {ukupno}, {tvrtka}, {vlasnik}')
                    ->schema([
                        Textarea::make('racun_email_predlozak')
                            ->label('Predložak poruke')
                            ->rows(6)
                            ->placeholder("Poštovani {klijent},\n\nu privitku se nalazi račun broj {broj}.\n\nSrdačan pozdrav,\n{tvrtka}, vl. {vlasnik}")
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('emailData');
    }

    public function pretplateForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Postavke obavijesti za pretplate')
                    ->description('Automatsko slanje podsjetnika klijentima kada pretplate ističu')
                    ->schema([
                        TextInput::make('pretplate_dani_upozorenja')
                            ->label('Slanje obavijesti prije isteka (u danima)')
                            ->placeholder('npr. 30,15,1')
                            ->helperText('Unesite dane odvojene zarezom')
                            ->default('30,15,1'),

                        Textarea::make('pretplate_email_predlozak')
                            ->label('Predložak email poruke')
                            ->rows(10)
                            ->placeholder('Dostupne varijable: {klijent}, {usluga}, {datum_isteka}, {cijena}, {opis}, {tvrtka}')
                            ->columnSpanFull(),
                    ])->columns(1),
            ])
            ->statePath('pretplateData');
    }

    public function spremiKorisnika(): void
    {
        $data = $this->korisnikForm->getState();
        $user = auth()->user();

        $user->name  = $data['user_name'];
        $user->email = $data['user_email'];

        if (! empty($data['user_password'])) {
            $user->password = bcrypt($data['user_password']);
        }

        $user->save();

        Notification::make()->title('Korisnički podaci spremljeni')->success()->send();
    }

    public function spremiSmtp(): void
    {
        $data     = $this->smtpForm->getState();
        $tvrtkaId = filament()->getTenant()->id;

        $postojecaLozinka = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->value('smtp_pass');

        TvrtkaPostavke::updateOrCreate(
            ['tvrtka_id' => $tvrtkaId],
            [
                'smtp_host'       => $data['smtp_host'] ?? null,
                'smtp_port'       => $data['smtp_port'] ?? 587,
                'smtp_user'       => $data['smtp_user'] ?? null,
                'smtp_pass'       => ! empty($data['smtp_pass']) ? $data['smtp_pass'] : $postojecaLozinka,
                'smtp_sigurnost'  => $data['smtp_sigurnost'] ?? 'tls',
                'smtp_from_name'  => $data['smtp_from_name'] ?? null,
                'smtp_from_email' => $data['smtp_from_email'] ?? null,
            ]
        );

        Notification::make()->title('SMTP postavke spremljene')->success()->send();
    }

    public function spremiEmail(): void
    {
        $data = $this->emailForm->getState();

        TvrtkaPostavke::updateOrCreate(
            ['tvrtka_id' => filament()->getTenant()->id],
            ['racun_email_predlozak' => $data['racun_email_predlozak'] ?? null]
        );

        Notification::make()->title('Predložak e-maila za račune spremljen')->success()->send();
    }

    public function spremiPretplate(): void
    {
        $data = $this->pretplateForm->getState();

        TvrtkaPostavke::updateOrCreate(
            ['tvrtka_id' => filament()->getTenant()->id],
            [
                'pretplate_dani_upozorenja' => $data['pretplate_dani_upozorenja'] ?? '30,15,1',
                'pretplate_email_predlozak' => $data['pretplate_email_predlozak'] ?? null,
            ]
        );

        Notification::make()->title('Postavke pretplata spremljene')->success()->send();
    }

    public function fiskalizacijaForm(Form $form): Form
    {
        $tvrtkaId = filament()->getTenant()->id;

        return $form
            ->schema([
                Section::make('Fiskalizacija')
                    ->description('Fiskalizacija gotovinske naplate sukladno Zakonu o fiskalizaciji (NN 133/12). Potreban je važeći FINA certifikat.')
                    ->schema([
                        Toggle::make('fiskalizacija_aktivna')
                            ->label('Aktiviraj fiskalizaciju')
                            ->helperText('Uključi ako naplaćuješ gotovinom ili karticom')
                            ->columnSpanFull(),

                        Toggle::make('fiskalizacija_demo')
                            ->label('Demo način rada')
                            ->helperText('Koristi FINA demo certifikat i testni CIS server — isključi za produkciju')
                            ->columnSpanFull(),

                        FileUpload::make('fina_cert_putanja')
                            ->label('FINA certifikat (.p12)')
                            ->disk('local')
                            ->directory('fina/' . $tvrtkaId)
                            ->acceptedFileTypes(['application/x-pkcs12', 'application/octet-stream'])
                            ->maxSize(2048)
                            ->helperText('Sprema se sigurno na server — privatna lokacija')
                            ->columnSpan(2),

                        TextInput::make('fina_cert_lozinka')
                            ->label('Lozinka certifikata')
                            ->password()
                            ->revealable()
                            ->placeholder(function () use ($tvrtkaId) {
                                $postavke = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->first();
                                return $postavke?->fina_cert_lozinka ? '••••••••' : '';
                            })
                            ->helperText('Ostavi prazno ako ne mijenjаš lozinku')
                            ->columnSpan(2),

                        TextInput::make('fis_prostor_oznaka')
                            ->label('Oznaka poslovnog prostora')
                            ->default('1')
                            ->helperText('Prema prijavi u ePorezna — obično "1"')
                            ->columnSpan(1),

                        TextInput::make('fis_uredaj_oznaka')
                            ->label('Oznaka naplatnog uređaja')
                            ->default('1')
                            ->helperText('Prema prijavi u ePorezna — obično "1"')
                            ->columnSpan(1),
                    ])->columns(2),
            ])
            ->statePath('fiskalizacijaData');
    }

    public function testirajCertifikat(): void
    {
        $tvrtkaId = filament()->getTenant()->id;
        $postavke = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->first();

        if (! $postavke?->fina_cert_putanja) {
            Notification::make()->title('Certifikat nije uploadan')->warning()->send();
            return;
        }

        $certPutanja = \Illuminate\Support\Facades\Storage::disk('local')->path($postavke->fina_cert_putanja);

        if (! file_exists($certPutanja)) {
            Notification::make()->title('Certifikat nije pronađen na serveru')->danger()->send();
            return;
        }

        $lozinka = $postavke->fina_cert_lozinka ?? '';

        // Legacy OpenSSL provider za FINA certifikate
        if (file_exists(base_path('openssl-legacy.cnf'))) {
            putenv('OPENSSL_CONF=' . base_path('openssl-legacy.cnf'));
        }

        // Pokušaj otvoriti PKCS#12 certifikat
        $result = openssl_pkcs12_read(file_get_contents($certPutanja), $certs, $lozinka);

        if ($result) {
            Notification::make()
                ->title('Certifikat je ispravan')
                ->body('Lozinka je točna i certifikat se može koristiti za fiskalizaciju.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Certifikat nije ispravan')
                ->body('Lozinka je pogrešna ili je certifikat oštećen. Provjerite .p12 datoteku i lozinku.')
                ->danger()
                ->send();
        }
    }

    public function spreminiFiskalizaciju(): void
    {
        $data     = $this->fiskalizacijaForm->getState();
        $tvrtkaId = filament()->getTenant()->id;

        $update = [
            'fiskalizacija_aktivna' => $data['fiskalizacija_aktivna'] ?? false,
            'fiskalizacija_demo'    => $data['fiskalizacija_demo'] ?? false,
            'fis_prostor_oznaka'    => $data['fis_prostor_oznaka'] ?? '1',
            'fis_uredaj_oznaka'     => $data['fis_uredaj_oznaka'] ?? '1',
        ];

        // FileUpload vraća array ili string
        $cert = $data['fina_cert_putanja'] ?? null;
        if (is_array($cert)) {
            $cert = array_values($cert)[0] ?? null;
        }
        if (! empty($cert)) {
            // Obriši stare certifikate iz direktorija
            $stariCert = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->value('fina_cert_putanja');
            if ($stariCert && $stariCert !== $cert) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($stariCert);
            }
            // Obriši sve ostale .p12 fajlove u direktoriju
            $dir = 'fina/' . $tvrtkaId;
            foreach (\Illuminate\Support\Facades\Storage::disk('local')->files($dir) as $file) {
                if ($file !== $cert) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($file);
                }
            }
            $update['fina_cert_putanja'] = $cert;
        }

        if (! empty($data['fina_cert_lozinka'])) {
            $update['fina_cert_lozinka'] = $data['fina_cert_lozinka'];
        }

        TvrtkaPostavke::updateOrCreate(['tvrtka_id' => $tvrtkaId], $update);

        Notification::make()->title('Postavke fiskalizacije spremljene')->success()->send();
    }

    public function eracunForm(Form $form): Form
    {
        $tvrtkaId = filament()->getTenant()->id;

        return $form
            ->schema([
                Section::make('eRačun (FINA Middleware)')
                    ->description('Elektroničko fakturiranje putem FINA eRačun Middlewarea. Middleware mora biti instaliran i pokrenut na serveru.')
                    ->schema([
                        Toggle::make('eracun_aktivan')
                            ->label('Aktiviraj eRačun')
                            ->helperText('Omogući slanje i primanje eRačuna putem middlewarea')
                            ->columnSpanFull(),

                        Toggle::make('eracun_demo')
                            ->label('Demo (prezentacijska) okolina')
                            ->helperText('Koristi FINA prezentacijski server — isključi za produkciju')
                            ->columnSpanFull(),

                        TextInput::make('eracun_middleware_url')
                            ->label('Middleware URL')
                            ->placeholder('http://localhost:8888')
                            ->helperText('Adresa na kojoj radi FINA eRačun Middleware')
                            ->url()
                            ->columnSpanFull(),

                        TextInput::make('eracun_jks_uuid')
                            ->label('JKS UUID')
                            ->placeholder('npr. 62c81250-8cfc-4605-9627-e9a52231b869')
                            ->helperText('UUID certifikata u middleware keystoreu — dobiva se iz middleware konfiguracije')
                            ->columnSpanFull(),

                        FileUpload::make('eracun_cert_putanja')
                            ->label('eRačun certifikat (.p12)')
                            ->disk('local')
                            ->directory('eracun/' . $tvrtkaId)
                            ->acceptedFileTypes(['application/x-pkcs12', 'application/octet-stream'])
                            ->maxSize(2048)
                            ->helperText('Spremi certifikat ovdje, a zatim ga konfiguriraj u middleware keystoreu')
                            ->columnSpan(2),

                        TextInput::make('eracun_cert_lozinka')
                            ->label('Lozinka certifikata')
                            ->password()
                            ->revealable()
                            ->placeholder(function () use ($tvrtkaId) {
                                $postavke = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->first();
                                return $postavke?->eracun_cert_lozinka ? '••••••••' : '';
                            })
                            ->helperText('Ostavi prazno ako ne mijenjаš lozinku')
                            ->columnSpan(2),
                    ])->columns(2),
            ])
            ->statePath('eracunData');
    }

    public function spremiEracun(): void
    {
        $data     = $this->eracunForm->getState();
        $tvrtkaId = filament()->getTenant()->id;

        $update = [
            'eracun_aktivan'        => $data['eracun_aktivan'] ?? false,
            'eracun_demo'           => $data['eracun_demo'] ?? false,
            'eracun_middleware_url' => $data['eracun_middleware_url'] ?? null,
            'eracun_jks_uuid'       => $data['eracun_jks_uuid'] ?? null,
        ];

        // Certifikat — čisti stari ako je novi uploadан
        $cert = $data['eracun_cert_putanja'] ?? null;
        if (is_array($cert)) {
            $cert = array_values($cert)[0] ?? null;
        }
        if (! empty($cert)) {
            $stariCert = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->value('eracun_cert_putanja');
            if ($stariCert && $stariCert !== $cert) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($stariCert);
            }
            $dir = 'eracun/' . $tvrtkaId;
            foreach (\Illuminate\Support\Facades\Storage::disk('local')->files($dir) as $file) {
                if ($file !== $cert) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($file);
                }
            }
            $update['eracun_cert_putanja'] = $cert;
        }

        if (! empty($data['eracun_cert_lozinka'])) {
            $update['eracun_cert_lozinka'] = $data['eracun_cert_lozinka'];
        }

        // Auto-registracija certifikata u middlewareu kad se uploada novi cert
        $middlewareUrl = $data['eracun_middleware_url'] ?? null;
        if (! empty($cert) && ! empty($middlewareUrl)) {
            $postojecePostavke = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->first();
            $certLozinka = ! empty($data['eracun_cert_lozinka'])
                ? $data['eracun_cert_lozinka']
                : $postojecePostavke?->eracun_cert_lozinka;

            if ($certLozinka) {
                try {
                    $stariUuid = $postojecePostavke?->eracun_jks_uuid;
                    $uuid = EracunService::registrirajCertifikat(
                        $cert,
                        $certLozinka,
                        $middlewareUrl,
                        $data['eracun_demo'] ?? false,
                        $stariUuid
                    );
                    $update['eracun_jks_uuid'] = $uuid;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Auto-registracija eRačun certifikata neuspješna: ' . $e->getMessage());
                    Notification::make()
                        ->title('Certifikat nije automatski registriran u middlewareu')
                        ->body($e->getMessage())
                        ->warning()
                        ->send();
                }
            }
        }

        TvrtkaPostavke::updateOrCreate(['tvrtka_id' => $tvrtkaId], $update);

        Notification::make()->title('eRačun postavke spremljene')->success()->send();
    }

    public function testirajEracunCertifikat(): void
    {
        $tvrtkaId = filament()->getTenant()->id;
        $postavke = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->first();

        if (! $postavke?->eracun_cert_putanja) {
            Notification::make()->title('eRačun certifikat nije uploadан')->warning()->send();
            return;
        }

        try {
            $info = EracunService::testirajEracunCertifikat(
                $postavke->eracun_cert_putanja,
                $postavke->eracun_cert_lozinka ?? ''
            );

            Notification::make()
                ->title('eRačun certifikat je ispravan')
                ->body('Subjekt: ' . $info['subjekt'] . "\nIzdavač: " . $info['izdavac'] . "\nVrijedi do: " . $info['vrijedi_do'])
                ->success()
                ->send();
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('eRačun certifikat nije ispravan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testirajMiddleware(): void
    {
        $tvrtkaId = filament()->getTenant()->id;
        $postavke = TvrtkaPostavke::where('tvrtka_id', $tvrtkaId)->first();

        if (! $postavke?->eracun_middleware_url) {
            Notification::make()->title('Middleware URL nije konfiguriran')->warning()->send();
            return;
        }

        $baseUrl = rtrim($postavke->eracun_middleware_url, '/');

        // Provjeri je li middleware uopće dostupan
        try {
            $health = \Illuminate\Support\Facades\Http::timeout(5)->get($baseUrl . '/actuator/health');
            if (! $health->successful()) {
                Notification::make()->title('Middleware nije dostupan')->body('Health check nije OK.')->danger()->send();
                return;
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Middleware nije dostupan')->body($e->getMessage())->danger()->send();
            return;
        }

        // Ako nema UUID, samo javi da je middleware živ
        if (empty($postavke->eracun_jks_uuid)) {
            Notification::make()->title('Middleware dostupan')->body('Middleware radi, ali JKS UUID nije konfiguriran.')->warning()->send();
            return;
        }

        // Testiraj FINA vezu putem echo endpointa
        $okolina = $postavke->eracun_demo ? 'prez' : 'prod';
        $url     = $baseUrl . '/pki/echoB2B/' . $okolina . '/' . $postavke->eracun_jks_uuid;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/xml', 'Accept' => 'application/xml'])
                ->post($url);

            if ($response->successful() || str_contains($response->body(), 'zaprimljena')) {
                Notification::make()->title('FINA veza OK')->body('Middleware i FINA certifikat rade ispravno.')->success()->send();
            } else {
                Notification::make()->title('Middleware dostupan, FINA greška')->body('HTTP ' . $response->status() . ': ' . $response->body())->warning()->send();
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Greška pri testiranju FINA veze')->body($e->getMessage())->danger()->send();
        }
    }
}
