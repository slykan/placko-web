<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AlatiController extends Controller
{
    /**
     * Besplatni javni alati (bez prijave). Svaki ima view resources/views/alati/{slug}.blade.php.
     */
    public static function alati(): array
    {
        return [
            [
                'slug' => 'kalkulator-pausalnog-obrta',
                'ico' => '🧮',
                'title' => 'Kalkulator paušalnog obrta 2026.',
                'excerpt' => 'Upiši godišnji promet i odmah vidi razred, porez, doprinose i koliko ti ostaje.',
            ],
            [
                'slug' => 'hub3-uplatnica',
                'ico' => '🏦',
                'title' => 'HUB-3 uplatnica s barkodom',
                'excerpt' => 'Izradi uplatnicu s 2D barkodom koji se skenira mobilnim bankarstvom. Ispis ili PNG.',
            ],
            [
                'slug' => 'provjera-oib-iban',
                'ico' => '✅',
                'title' => 'Provjera OIB-a i IBAN-a',
                'excerpt' => 'Provjeri je li OIB ili IBAN ispravno upisan (kontrolna znamenka) prije nego izdaš račun.',
            ],
            [
                'slug' => 'pdv-kalkulator',
                'ico' => '％',
                'title' => 'PDV kalkulator',
                'excerpt' => 'Izračunaj iznos s PDV-om ili bez njega po stopama 25 %, 13 % i 5 %.',
            ],
        ];
    }

    public function index(): View
    {
        return view('alati.index', ['alati' => self::alati()]);
    }

    public function show(string $slug): View
    {
        $alat = collect(self::alati())->firstWhere('slug', $slug);

        abort_unless($alat && view()->exists('alati.'.$slug), 404);

        return view('alati.'.$slug, ['alat' => $alat, 'ostali' => collect(self::alati())->where('slug', '!=', $slug)->values()->all()]);
    }
}
