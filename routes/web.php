<?php

use App\Http\Controllers\AlatiController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PonudaPrihvatiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', ['cjenik' => config('cjenik.planovi')]);
});

Route::get('/cjenik.csv', function () {
    $planovi = config('cjenik.planovi');

    // Stupci prema odluci o objavi cjenika (NN 101/2026): naziv usluge, cijena, akcija, sidrena cijena.
    $redovi = ["Plan,Cijena (EUR),Jedinica,Akcijska cijena,Sidrena cijena (EUR)"];
    foreach ($planovi as $plan) {
        // Sidrena cijena = ista vrijednost kao prikazana cijena (nema aktivnog sniženja/akcije).
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

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::post('/turnstile/verify', function (\Illuminate\Http\Request $request) {
    $token = $request->input('token');
    if ($token) {
        session(['turnstile_token' => $token]);
    }
    return response()->json(['ok' => true]);
})->name('turnstile.verify');
