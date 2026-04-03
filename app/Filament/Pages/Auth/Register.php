<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Http;

class Register extends BaseRegister
{
    protected static string $view = 'filament.pages.auth.register';

    public string $turnstileToken = '';

    public function register(): ?RegistrationResponse
    {
        try {
            $this->validateTurnstile();
        } catch (\Filament\Support\Exceptions\Halt) {
            return null;
        }

        return parent::register();
    }

    protected function validateTurnstile(): void
    {
        if (app()->environment('local', 'testing')) {
            return;
        }

        // Token dolazi ili iz Livewire propertija ili iz sessiona
        $token = $this->turnstileToken ?: session()->pull('turnstile_token');

        if (empty($token)) {
            $this->addError('data.email', 'Molimo potvrdite da niste robot.');
            $this->halt();
        }

        $response = Http::timeout(10)->asForm()->post('https://challenges.cloudflare.com/turnstile/v1/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        \Log::info('Turnstile register', [
            'token_len' => strlen($token),
            'secret_len' => strlen(config('services.turnstile.secret') ?? ''),
            'cf_response' => $response->json(),
        ]);

        if (! ($response->json('success') ?? false)) {
            // Resetiraj token za novi pokušaj
            $this->turnstileToken = '';
            $this->addError('data.email', 'Provjera nije uspjela. Pokušajte ponovo.');
            $this->halt();
        }
    }
}
