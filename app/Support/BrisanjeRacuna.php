<?php

namespace App\Support;

use App\Models\Tvrtka;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Trajno brisanje korisničkog računa (Postavke → Opasna zona).
 *
 * Tvrtke kojima je korisnik jedini član brišu se sa svim podacima i datotekama.
 * Iz tvrtki koje dijeli s drugim korisnicima samo se uklanja, podaci ostaju ostalima.
 */
final class BrisanjeRacuna
{
    /**
     * Tablice koje drže restrictOnDelete prema klijentima, skladištima ili uslugama:
     * brišu se prije tvrtke, a ostatak (klijenti, usluge, skladišta, postavke...) ide kaskadom.
     * Stavke svake od njih imaju cascadeOnDelete na roditelja.
     */
    private const PRIJE_TVRTKE = ['radni_nalozi', 'racuni', 'ponude', 'pretplate', 'primke', 'inventure'];

    public static function obrisi(User $user): void
    {
        $datoteke = [];

        DB::transaction(function () use ($user, &$datoteke) {
            foreach ($user->tvrtke()->get() as $tvrtka) {
                $ostali = DB::table('tvrtka_user')
                    ->where('tvrtka_id', $tvrtka->id)
                    ->where('user_id', '!=', $user->id)
                    ->exists();

                if ($ostali) {
                    $user->tvrtke()->detach($tvrtka->id);
                    continue;
                }

                $datoteke[] = $tvrtka;
                foreach (self::PRIJE_TVRTKE as $tablica) {
                    DB::table($tablica)->where('tvrtka_id', $tvrtka->id)->delete();
                }
                $tvrtka->delete();
            }

            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete();
        });

        // Datoteke tek nakon uspješne transakcije, da neuspjeh ne ostavi bazu bez certifikata.
        foreach ($datoteke as $tvrtka) {
            self::obrisiDatoteke($tvrtka);
        }
    }

    private static function obrisiDatoteke(Tvrtka $tvrtka): void
    {
        $lokalno = Storage::disk('local');
        foreach (['fina/', 'eracun/', 'racuni/', 'putni-nalozi/'] as $mapa) {
            $lokalno->deleteDirectory($mapa . $tvrtka->id);
        }

        if (filled($tvrtka->logo)) {
            Storage::disk(config('filament.default_filesystem_disk', 'public'))->delete($tvrtka->logo);
        }
    }
}
