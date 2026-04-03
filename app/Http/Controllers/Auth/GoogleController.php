<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
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

            // Obavijesti admina o novoj registraciji
            $adminEmail = config('mail.from.address');
            if ($adminEmail) {
                try {
                    Mail::raw(
                        "Nova Google registracija:\nIme: {$user->name}\nEmail: {$user->email}",
                        fn ($m) => $m->to($adminEmail)->subject('Nova registracija — ' . config('app.name'))
                    );
                } catch (\Throwable) {}
            }
        }

        Auth::login($user, remember: true);

        return redirect('/admin');
    }
}
