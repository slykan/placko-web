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
                'slug' => 'kako-otvoriti-pausalni-obrt',
                'kategorija' => 'vodic',
                'title' => 'Kako otvoriti paušalni obrt u 2026. — korak po korak',
                'excerpt' => 'Od prijave obrta preko e-Obrta ili START-a, upisa u registar poreznih obveznika i izbora paušala do bankovnog računa, fiskalizacije i knjige prometa. Kontrolna lista za prvi mjesec.',
                'date' => '2026-10-26',
                'read' => '7 min čitanja',
            ],
            [
                'slug' => 'knjiga-prometa-kpr-pausalni-obrt',
                'kategorija' => 'vodic',
                'title' => 'Knjiga prometa (KPR) za paušalni obrt: što se upisuje i kako je voditi',
                'excerpt' => 'Upisuju se naplaćeni, a ne izdani računi. Koje stupce KPR ima, kako upisati predujmove i djelomične uplate, koliko često je voditi, koliko je čuvati i najčešće pogreške.',
                'date' => '2026-10-26',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'po-sd-obrazac-pausalni-obrt',
                'kategorija' => 'vodic',
                'title' => 'PO-SD obrazac: kako i do kada ga predati (rok 15. siječnja)',
                'excerpt' => 'Godišnje izvješće o paušalnom dohotku predaje se putem ePorezne do 15. siječnja. Što ti treba, kako se ispunjava korak po korak, što se mijenja nakon predaje i najčešće pogreške.',
                'date' => '2026-10-26',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'pausalni-obrt-i-pdv-prag-60000',
                'kategorija' => 'vodic',
                'title' => 'Paušalni obrt i PDV: prag od 60.000 € i strani klijenti',
                'excerpt' => 'Kada paušalist ulazi u sustav PDV-a, što se događa kad prijeđeš prag tijekom godine, ima li smisla dobrovoljni ulazak i zašto ti za klijente iz EU treba PDV identifikacijski broj.',
                'date' => '2026-10-26',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'ponuda-predracun-racun-razlike',
                'kategorija' => 'vodic',
                'title' => 'Ponuda, predračun i račun — koja je razlika i kada koristiti koji',
                'excerpt' => 'Ponuda je prijedlog, predračun poziv na plaćanje unaprijed, a račun poslovna isprava. Usporedna tablica: obveza plaćanja, fiskalizacija, knjiga prometa i ovrha, uz tipičan tijek posla.',
                'date' => '2026-10-26',
                'read' => '5 min čitanja',
            ],
            [
                'slug' => 'fiskalizacija-racuna-jir-zki',
                'kategorija' => 'vodic',
                'title' => 'Fiskalizacija računa: JIR, ZKI i što ti treba za početak',
                'excerpt' => 'Koji se računi fiskaliziraju, što su ZKI i JIR, koji certifikat i interni akt trebaš, što napraviti kad nema interneta i najčešće pogreške kod fiskalizacije.',
                'date' => '2026-10-26',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'naplata-duga-opomena-ovrha',
                'kategorija' => 'vodic',
                'title' => 'Klijent ne plaća račun: opomena, ovrha kod javnog bilježnika i zastara',
                'excerpt' => 'Od podsjetnika i opomene do ovrhe na temelju vjerodostojne isprave: kako teče postupak, što mora pisati na računu građaninu, zatezne kamate i rokovi zastare.',
                'date' => '2026-10-26',
                'read' => '6 min čitanja',
            ],
            [
                'slug' => 'bazna-cijena-od-17-studenoga-2026',
                'kategorija' => 'vodic',
                'title' => 'Bazna cijena od 17. studenoga 2026. — sidrena cijena odgođena i ulazi u Zakon o zaštiti potrošača',
                'excerpt' => 'Obveza isticanja sidrene cijene pomaknuta je s 1. listopada na 17. studenoga 2026., a od tada se po Zakonu o zaštiti potrošača zove bazna cijena. Što se mijenja, kazne i svi službeni dokumenti na jednom mjestu.',
                'date' => '2026-10-23',
                'read' => '5 min čitanja',
            ],
            [
                'slug' => 'sidrena-cijena-i-cjenik-usluga-2026',
                'kategorija' => 'vodic',
                'title' => 'Sidrena (bazna) cijena i cjenik usluga: što moraju napraviti obrtnici i pružatelji usluga',
                'excerpt' => 'Od 17. studenoga 2026. (odgođeno s 1. listopada) dodatnu (sidrenu, odnosno baznu) cijenu moraju isticati i pružatelji usluga, a tko ima web stranicu mora objavljivati i strojno čitljiv cjenik. Što to znači u praksi i kako smo to riješili na plačko.app.',
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
                'title' => 'Skladište i praćenje zaliha u plačko.app — primke, inventura i upozorenja',
                'excerpt' => 'Modul Skladište je dostupan: zaliha po proizvodu i skladištu, primke s dobavljačima i prosječnom nabavnom cijenom, automatsko oduzimanje pri izdavanju računa, inventura i upozorenja na nisku zalihu.',
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
