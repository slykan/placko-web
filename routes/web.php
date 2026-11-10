<?php

use App\Http\Controllers\AlatiController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\MobileGoogleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\MojiNaloziController;
use App\Http\Controllers\PonudaPrihvatiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', ['cjenik' => config('cjenik.planovi')]);
});

Route::get('/cjenik.csv', function () {
    $planovi = config('cjenik.planovi');

    // Stupci prema odluci o objavi cjenika (NN 101/2026) i ZZP čl. 7. (NN 59/2026): naziv usluge, cijena, akcija, bazna (sidrena) cijena.
    $redovi = ["Plan,Cijena (EUR),Jedinica,Akcijska cijena,Bazna (sidrena) cijena (EUR)"];
    foreach ($planovi as $plan) {
        // Bazna cijena = ista vrijednost kao prikazana cijena (nema aktivnog sniženja/akcije).
        $redovi[] = sprintf(
            '%s,%s,%s,%s,%s',
            $plan['naziv'],
            $plan['cijena_csv'],
            $plan['jedinica'],
            'NE',
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

Route::get('/alati', [AlatiController::class, 'index'])->name('alati.index');
Route::get('/alati/{slug}', [AlatiController::class, 'show'])->name('alati.show');

foreach (['o-nama', 'kontakt', 'privatnost', 'uvjeti-koristenja'] as $stranica) {
    Route::view('/'.$stranica, 'stranice.'.$stranica)->name('stranica.'.$stranica);
}

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'), 'priority' => '1.0'],
        ['loc' => url('/alati'), 'priority' => '0.9'],
        ['loc' => url('/novosti'), 'priority' => '0.8'],
    ];
    foreach (AlatiController::alati() as $a) {
        $urls[] = ['loc' => url('/alati/'.$a['slug']), 'priority' => '0.9'];
    }
    foreach (BlogController::posts() as $p) {
        $urls[] = ['loc' => route('novosti.show', $p['slug']), 'priority' => $p['kategorija'] === 'vodic' ? '0.8' : '0.6', 'lastmod' => $p['modified'] ?? $p['date']];
    }
    foreach (['o-nama', 'kontakt', 'privatnost', 'uvjeti-koristenja'] as $s) {
        $urls[] = ['loc' => url('/'.$s), 'priority' => '0.3'];
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $u) {
        $xml .= '  <url><loc>'.e($u['loc']).'</loc>'.(isset($u['lastmod']) ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '').'<priority>'.$u['priority'].'</priority></url>'."\n";
    }

    return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::get('/ponuda/{ponuda}/prihvati', [PonudaPrihvatiController::class, 'prihvati'])
    ->name('ponuda.prihvati')
    ->middleware('signed');

Route::prefix('moji-nalozi/{token}')->name('moji-nalozi.')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [MojiNaloziController::class, 'index'])->name('index');
    Route::get('/{nalog}', [MojiNaloziController::class, 'show'])->whereNumber('nalog')->name('show');
    Route::post('/{nalog}', [MojiNaloziController::class, 'spremi'])->whereNumber('nalog')->name('spremi');
    Route::post('/{nalog}/prihvati', [MojiNaloziController::class, 'prihvati'])->whereNumber('nalog')->name('prihvati');
});

Route::get('/auth/google',[GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/auth/mobile/google', [MobileGoogleController::class, 'prepare'])->name('auth.mobile.google');
    Route::get('/auth/mobile/google/launch', [MobileGoogleController::class, 'launch'])
        ->middleware('signed')->name('auth.mobile.google.launch');
    Route::get('/auth/mobile/google/return', [MobileGoogleController::class, 'bridge'])->name('auth.mobile.google.return');
    Route::post('/auth/mobile/google/complete', [MobileGoogleController::class, 'complete'])->name('auth.mobile.google.complete');
});

Route::post('/turnstile/verify', function (\Illuminate\Http\Request $request) {
    $token = $request->input('token');
    if ($token) {
        session(['turnstile_token' => $token]);
    }
    return response()->json(['ok' => true]);
})->name('turnstile.verify');
