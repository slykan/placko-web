<?php

namespace App\Services;

use App\Models\Skladiste;
use App\Models\SkladisnaTransakcija;
use App\Models\Usluga;

class ZalihaService
{
    public static function zabiljezi(
        Usluga $usluga,
        Skladiste $skladiste,
        string $tip,
        float $kolicinaDelta,
        ?float $cijena = null,
        array $meta = [],
    ): SkladisnaTransakcija {
        $zaliha = $usluga->zalihe()->firstOrCreate(
            ['skladiste_id' => $skladiste->id],
            ['tvrtka_id' => $usluga->tvrtka_id, 'kolicina' => 0, 'prosjecna_nabavna_cijena' => 0]
        );

        if ($tip === 'ulaz' && $cijena !== null && $kolicinaDelta > 0) {
            $novaKolicina = (float) $zaliha->kolicina + $kolicinaDelta;
            $zaliha->prosjecna_nabavna_cijena = $novaKolicina > 0
                ? round((((float) $zaliha->kolicina * (float) $zaliha->prosjecna_nabavna_cijena) + ($kolicinaDelta * $cijena)) / $novaKolicina, 2)
                : $zaliha->prosjecna_nabavna_cijena;
        }

        $zaliha->kolicina = (float) $zaliha->kolicina + $kolicinaDelta;
        $zaliha->save();

        return SkladisnaTransakcija::create(array_merge([
            'tvrtka_id' => $usluga->tvrtka_id,
            'usluga_id' => $usluga->id,
            'skladiste_id' => $skladiste->id,
            'tip' => $tip,
            'kolicina' => $kolicinaDelta,
            'cijena' => $cijena,
            'datum' => now(),
        ], $meta));
    }

    public static function zadanoSkladiste(int $tvrtkaId): Skladiste
    {
        return Skladiste::firstOrCreate(
            ['tvrtka_id' => $tvrtkaId, 'zadano' => true],
            ['naziv' => 'Glavno skladište']
        );
    }
}
