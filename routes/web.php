<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::post('/turnstile/verify', function (\Illuminate\Http\Request $request) {
    $token = $request->input('token');
    if ($token) {
        session(['turnstile_token' => $token]);
    }
    return response()->json(['ok' => true]);
})->name('turnstile.verify');
