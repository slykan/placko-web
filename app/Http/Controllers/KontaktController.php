<?php

namespace App\Http\Controllers;

use App\Mail\KontaktMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Kontakt obrazac (/kontakt). Zaštita od spama: Cloudflare Turnstile, skriveno polje (honeypot),
 * minimalno vrijeme ispunjavanja i ograničenje broja slanja po IP adresi (ruta).
 */
class KontaktController extends Controller
{
    public const TEME = [
        'nadogradnja' => 'Nadogradnja računa',
        'podrska' => 'Tehnička podrška',
        'ostalo' => 'Ostala pitanja',
    ];

    public function posalji(Request $request): RedirectResponse
    {
        // Robot je ispunio skriveno polje ili poslao obrazac prebrzo: tiho "uspije", bez slanja.
        $otvoreno = (int) rescue(fn () => decrypt((string) $request->input('_t')), 0, false);
        if (filled($request->input('web_stranica')) || $otvoreno <= 0 || time() - $otvoreno < 3) {
            return redirect()->route('stranica.kontakt')->with('kontakt_poslano', true);
        }

        $d = $request->validate([
            'ime' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:191'],
            'tema' => ['required', Rule::in(array_keys(self::TEME))],
            'poruka' => ['required', 'string', 'min:10', 'max:5000'],
            'privatnost' => ['accepted'],
            'kopija' => ['nullable', 'boolean'],
        ], [
            'ime.required' => 'Upiši ime.',
            'email.required' => 'Upiši e-mail adresu na koju ti odgovaramo.',
            'email.email' => 'E-mail adresa nije ispravna.',
            'tema.*' => 'Odaberi temu.',
            'poruka.required' => 'Napiši poruku.',
            'poruka.min' => 'Poruka je prekratka.',
            'poruka.max' => 'Poruka je preduga (najviše 5000 znakova).',
            'privatnost.accepted' => 'Za slanje je potrebna privola za obradu podataka.',
        ]);

        $this->provjeriTurnstile($request);

        $poruka = [
            'ime' => $d['ime'],
            'email' => $d['email'],
            'tema' => self::TEME[$d['tema']],
            'poruka' => $d['poruka'],
            'korisnik' => $request->user()?->only(['id', 'email']),
            'vrijeme' => now()->timezone(config('app.timezone'))->format('d.m.Y. H:i'),
        ];

        try {
            Mail::to(config('mail.kontakt'))->send(new KontaktMail($poruka, zaAdmina: true));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['poruka' => 'Slanje trenutno nije uspjelo. Pokušaj ponovno ili piši na '.config('mail.kontakt').'.']);
        }

        if ($request->boolean('kopija')) {
            try {
                Mail::to($d['email'])->send(new KontaktMail($poruka, zaAdmina: false));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('stranica.kontakt')->with('kontakt_poslano', true);
    }

    private function provjeriTurnstile(Request $request): void
    {
        if (app()->environment('local', 'testing') || ! config('services.turnstile.secret')) {
            return;
        }

        $token = (string) $request->input('cf-turnstile-response');
        $ok = $token !== '' && rescue(fn () => Http::timeout(10)->asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ])->json('success'), false, false);

        if (! $ok) {
            throw ValidationException::withMessages(['turnstile' => 'Potvrdi da nisi robot pa pošalji ponovno.']);
        }
    }
}
