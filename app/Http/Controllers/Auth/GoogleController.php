<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MobileGoogleLogin;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        session()->forget('mobile_google_browser_state');
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $mobileState = session()->pull('mobile_google_browser_state');
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            if ($mobileState) {
                return response()->view('auth.mobile-google-finish', ['code' => null, 'state' => null])
                    ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
            }
            return redirect('/admin/login')->withErrors(['email' => 'Google prijava nije uspjela. Pokušajte ponovo.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        $isNew = false;

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $isNew = true;
            $user = User::create([
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password'  => null,
            ]);
            // Obavijest adminima šalje User::created (NovaRegistracijaMail)
        }

        if ($mobileState) {
            $code = MobileGoogleLogin::issue($user, $mobileState);

            return response()->view('auth.mobile-google-finish', ['code' => $code, 'state' => $mobileState])
                ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect('/admin');
    }
}
