<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Http;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public string $turnstileToken = '';

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->validateTurnstile();
        } catch (\Filament\Support\Exceptions\Halt) {
            return null;
        }

        return parent::authenticate();
    }

    protected function validateTurnstile(): void
    {
        if (app()->environment('local', 'testing')) {
            return;
        }

        if (empty($this->turnstileToken)) {
            $this->addError('data.email', 'Molimo potvrdite da niste robot.');
            $this->halt();
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v1/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $this->turnstileToken,
            'remoteip' => request()->ip(),
        ]);

        if (! ($response->json('success') ?? false)) {
            $this->addError('data.email', 'Provjera nije uspjela. Pokušajte ponovo.');
            $this->halt();
        }
    }
}
