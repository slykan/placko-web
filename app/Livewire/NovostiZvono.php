<?php

namespace App\Livewire;

use App\Http\Controllers\BlogController;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Zvono u gornjoj traci admina: najave novih mogućnosti.
 * Izvor su objave iz BlogController::posts() s kategorijom 'novosti' — nova objava se pojavi sama.
 * Nepročitane su one koje korisnik još nije otvorio zvonom, a objavljene su na dan registracije ili kasnije.
 */
class NovostiZvono extends Component
{
    private const BROJ = 6;

    public function oznaciProcitano(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $user->forceFill([
            'procitane_novosti' => array_values(array_unique([
                ...($user->procitane_novosti ?? []),
                ...collect(static::novosti())->pluck('slug')->all(),
            ])),
        ])->save();

        $this->skipRender();
    }

    public static function novosti(): array
    {
        return collect(BlogController::posts())
            ->where('kategorija', 'novosti')
            ->sortByDesc('date')
            ->take(self::BROJ)
            ->values()
            ->all();
    }

    public function render()
    {
        $user = auth()->user();
        $procitane = $user?->procitane_novosti ?? [];
        $od = $user?->created_at?->toDateString() ?? '1970-01-01';

        $novosti = collect(static::novosti())->map(fn (array $p) => $p + [
            'novo' => $p['date'] >= $od && ! in_array($p['slug'], $procitane, true),
            'datum' => Carbon::parse($p['date'])->format('d.m.Y.'),
        ]);

        return view('livewire.novosti-zvono', [
            'novosti' => $novosti,
            'neprocitano' => $novosti->where('novo', true)->count(),
        ]);
    }
}
