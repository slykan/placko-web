<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PonudaPrihvatiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
