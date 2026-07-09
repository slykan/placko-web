<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    public static function posts(): array
    {
        return [
            [
                'slug' => 'skladiste-zalihe-uskoro',
                'title' => 'Uskoro stiže: Skladište i praćenje zaliha',
                'excerpt' => 'Radimo na modulu Skladište — praćenje zaliha po proizvodu, automatsko oduzimanje pri izdavanju računa, upozorenja kod niske zalihe i još mnogo toga.',
                'date' => '2026-07-09',
                'read' => '4 min čitanja',
            ],
            [
                'slug' => 'ponude-izrada-slanje-i-racun',
                'title' => 'Ponude u plačko.app: izrada, slanje mailom i pretvaranje u račun',
                'excerpt' => 'Kako brzo izraditi profesionalnu ponudu, poslati je klijentu e-mailom i pretvoriti u račun jednim klikom čim je klijent prihvati.',
                'date' => '2026-07-09',
                'read' => '5 min čitanja',
            ],
        ];
    }

    public function index(): View
    {
        return view('novosti.index', ['posts' => self::posts()]);
    }

    public function show(string $slug): View|Response
    {
        $post = collect(self::posts())->firstWhere('slug', $slug);

        if (! $post || ! view()->exists('novosti.posts.'.$slug)) {
            abort(404);
        }

        return view('novosti.show', ['post' => $post]);
    }
}
