<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PonudaPrihvatiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', ['cjenik' => config('cjenik.planovi')]);
});

Route::get('/cjenik.csv', function () {
    $planovi = config('cjenik.planovi');

    $redovi = ["Plan,Cijena (EUR),Jedinica,Sidrena cijena (EUR)"];
    foreach ($planovi as $plan) {
        // Sidrena cijena = ista vrijednost kao prikazana cijena (nema aktivnog sniženja/akcije).
        $redovi[] = sprintf(
            '%s,%s,%s,%s',
            $plan['naziv'],
            $plan['cijena_csv'],
            $plan['jedinica'],
            $plan['cijena_csv']
        );
    }

    return response(implode("\n", $redovi)."\n", 200, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="cjenik-placko-app.csv"',
    ]);
})->name('cjenik.csv');

Route::get('/novosti', [BlogController::class, 'index'])->name('novosti.index');
Route::get('/novosti/{slug}', [BlogController::class, 'show'])->name('novosti.show');

Route::get('/ponuda/{ponuda}/prihvati', [PonudaPrihvatiController::class, 'prihvati'])
    ->name('ponuda.prihvati')
    ->middleware('signed');

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::post('/turnstile/verify', function (\Illuminate\Http\Request $request) {
    $token = $request->input('token');
    if ($token) {
        session(['turnstile_token' => $token]);
    }
    return response()->json(['ok' => true]);
})->name('turnstile.verify');
