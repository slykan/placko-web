<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    public const KATEGORIJE = [
        'novosti' => 'Novosti',
        'vodic'   => 'Vodič',
    ];

    /**
     * Objave, najnovije prve. Sadržaj je u resources/views/novosti/posts/{slug}.blade.php.
     * 'kategorija': novosti = što je novo u aplikaciji, vodic = korisni sadržaj za obrtnike.
     */
    public static function posts(): array
    {
        return [
            [
                'slug' => 'sidrena-cijena-i-cjenik-usluga-2026',
                'kategorija' => 'vodic',
                'title' => 'Sidrena cijena od 1. listopada 2026.: što moraju napraviti obrtnici i pružatelji usluga',
                'excerpt' => 'Od 1. listopada 2026. dodatnu (sidrenu) cijenu moraju isticati i pružatelji usluga, a tko ima web stranicu mora objavljivati i strojno čitljiv cjenik. Što to znači u praksi i kako smo to riješili na plačko.app.',
                'date' => '2026-10-13',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'pocetni-broj-racuna',
                'kategorija' => 'novosti',
                'title' => 'Novo: nastavak numeracije računa iz drugog programa',
                'excerpt' => 'Prelaziš u plačko.app usred godine? U Postavkama sad upišeš početni broj računa i numeracija se nastavlja točno tamo gdje je stala u starom programu.',
                'date' => '2026-10-13',
                'read' => '3 min čitanja',
            ],
            [
                'slug' => 'prihvacanje-ponude-i-racun-iz-ponude',
                'kategorija' => 'novosti',
                'title' => 'Klijent prihvaća ponudu jednim klikom — a ti od nje radiš račun',
                'excerpt' => 'Ponuda poslana iz plačko.app ima gumb „Prihvati ponudu". Klijent klikne, ti dobiješ obavijest, a prihvaćenu ponudu jednim klikom pretvaraš u račun.',
                'date' => '2026-10-13',
                'read' => '4 min čitanja',
            ],
            [
                'slug' => 'placko-na-mobitelu',
                'kategorija' => 'novosti',
                'title' => 'plačko.app na mobitelu: instaliraj aplikaciju u 10 sekundi',
                'excerpt' => 'plačko.app se može instalirati na početni zaslon iPhonea i Androida kao prava aplikacija — bez App Storea i bez preuzimanja. Evo kako.',
                'date' => '2026-10-13',
                'read' => '3 min čitanja',
            ],
            [
                'slug' => 'pausalni-obrt-2026-porez-doprinosi-rokovi',
                'kategorija' => 'vodic',
                'title' => 'Paušalni obrt 2026.: porezni razredi, doprinosi i rokovi na jednom mjestu',
                'excerpt' => 'Koliko paušalist plaća poreza i doprinosa u 2026., do kada se što plaća, što je PO-SD i što se najavljuje za 2027. — sažeto, s tablicama i primjerima.',
                'date' => '2026-10-13',
                'read' => '7 min čitanja',
            ],
            [
                'slug' => 'fiskalizacija-2-0-eracun-pausalni-obrt',
                'kategorija' => 'vodic',
                'title' => 'Fiskalizacija 2.0 i eRačun za paušaliste: što vrijedi 2026., a što od 2027.',
                'excerpt' => 'Od 2026. paušalisti moraju zaprimati eRačune i fiskalizirati račune građanima bez obzira na način plaćanja, a od 2027. i izdavati eRačune. Pregled obveza i rokova.',
                'date' => '2026-10-13',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'sto-mora-pisati-na-racunu-pausalni-obrt',
                'kategorija' => 'vodic',
                'title' => 'Što mora pisati na računu paušalnog obrta (s primjerom)',
                'excerpt' => 'Obvezni elementi računa za paušaliste: broj računa, datum i vrijeme, OIB, napomena o PDV-u, JIR i ZKI. Kontrolna lista koju možeš provjeriti na svom računu.',
                'date' => '2026-10-13',
                'read' => '5 min čitanja',
            ],
            [
                'slug' => 'skladiste-zalihe-uskoro',
                'kategorija' => 'novosti',
                'title' => 'Uskoro stiže: Skladište i praćenje zaliha',
                'excerpt' => 'Radimo na modulu Skladište — praćenje zaliha po proizvodu, automatsko oduzimanje pri izdavanju računa, upozorenja kod niske zalihe i još mnogo toga.',
                'date' => '2026-07-09',
                'read' => '4 min čitanja',
            ],
            [
                'slug' => 'ponude-izrada-slanje-i-racun',
                'kategorija' => 'novosti',
                'title' => 'Ponude u plačko.app: izrada, slanje mailom i pretvaranje u račun',
                'excerpt' => 'Kako brzo izraditi profesionalnu ponudu, poslati je klijentu e-mailom i pretvoriti u račun jednim klikom čim je klijent prihvati.',
                'date' => '2026-07-09',
                'read' => '5 min čitanja',
            ],
        ];
    }

    public function index(Request $request): View
    {
        $kategorija = $request->query('kategorija');
        $posts = collect(self::posts());

        if (isset(self::KATEGORIJE[$kategorija])) {
            $posts = $posts->where('kategorija', $kategorija);
        } else {
            $kategorija = null;
        }

        return view('novosti.index', [
            'posts' => $posts->values()->all(),
            'kategorija' => $kategorija,
        ]);
    }

    public function show(string $slug): View|Response
    {
        $post = collect(self::posts())->firstWhere('slug', $slug);

        if (! $post || ! view()->exists('novosti.posts.'.$slug)) {
            abort(404);
        }

        $ostale = collect(self::posts())
            ->where('slug', '!=', $slug)
            ->sortBy(fn ($p) => $p['kategorija'] === $post['kategorija'] ? 0 : 1)
            ->take(3)
            ->values()
            ->all();

        return view('novosti.show', ['post' => $post, 'ostale' => $ostale]);
    }
}
