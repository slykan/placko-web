<?php

namespace App\Support;

use App\Livewire\NovostiZvono;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Push obavijest o novoj objavi kategorije "novosti" (iste koje prikazuje zvono).
 * Šalje se samo objava s datumom od zadnjih nekoliko dana, i to jednom, pa stare objave
 * nikad ne odu masovno (ni pri prvom pokretanju), a deploy nove objave je sam okidač.
 */
final class PushNovosti
{
    private const DANA_UNAZAD = 3;

    /** @return array<string, int> slug => broj uređaja kojima je stiglo */
    public static function posalji(): array
    {
        $danas = today()->toDateString();
        $od = today()->subDays(self::DANA_UNAZAD)->toDateString();
        $poslano = [];

        foreach (NovostiZvono::novosti() as $objava) {
            if ($objava['date'] > $danas || $objava['date'] < $od) {
                continue;
            }
            // Prvo zauzmi slug: dva istodobna pokretanja ne smiju poslati istu objavu.
            $zauzeto = DB::table('push_novosti_poslano')->insertOrIgnore([
                'slug' => $objava['slug'], 'created_at' => now(),
            ]);
            if (! $zauzeto) {
                continue;
            }

            $n = 0;
            $url = route('novosti.show', $objava['slug']);
            $tekst = Str::limit($objava['excerpt'] ?? '', 180);
            User::where('push_obavijesti', true)
                ->where(fn ($q) => $q->has('pushPretplate')->orHas('mobilePushDevices'))
                ->each(function (User $user) use ($objava, $tekst, $url, &$n) {
                    $n += WebPush::korisniku($user, $objava['title'], $tekst, $url);
                });

            DB::table('push_novosti_poslano')->where('slug', $objava['slug'])->update(['uredjaja' => $n]);
            $poslano[$objava['slug']] = $n;
        }

        return $poslano;
    }
}
