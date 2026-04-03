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

        $token = $this->turnstileToken ?: session()->pull('turnstile_token');

        if (empty($token)) {
            $this->addError('data.email', 'Molimo potvrdite da niste robot.');
            $this->halt();
        }

        $response = Http::timeout(10)->asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        if (! ($response->json('success') ?? false)) {
            $this->turnstileToken = '';
            $this->addError('data.email', 'Provjera nije uspjela. Pokušajte ponovo.');
            $this->halt();
        }
    }
}
