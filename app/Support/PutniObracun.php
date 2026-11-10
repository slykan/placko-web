<?php

namespace App\Support;

use App\Models\PutniNalog;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Obračun putnog naloga po neoporezivim iznosima iz config/putni.php.
 *
 * Dnevnice: svaka puna 24 sata = 1 dnevnica; ostatak > 12 h = još jedna, 8–12 h = pola, do 8 h ništa.
 * Inozemni put: dnevnice po državi za vrijeme od prelaska granice do povratka preko granice,
 * a vrijeme u Hrvatskoj (do granice i od granice) obračunava se kao tuzemno.
 * Osigurani obrok umanjuje dnevnicu za 30 % (dva obroka 60 %).
 */
final class PutniObracun
{
    /** @return array{0: int, 1: int} [punih dnevnica, pola dnevnica] */
    public static function brojDnevnica(float $sati): array
    {
        if ($sati <= 0) {
            return [0, 0];
        }
        $pune = (int) floor($sati / 24);
        $ostatak = $sati - $pune * 24;

        if ($ostatak > 12) {
            return [$pune + 1, 0];
        }

        return [$pune, $ostatak > 8 ? 1 : 0];
    }

    public static function sati(?CarbonInterface $od, ?CarbonInterface $do): float
    {
        if (! $od || ! $do || $do->lte($od)) {
            return 0.0;
        }

        return $od->diffInMinutes($do) / 60;
    }

    public static function obracunaj(PutniNalog $n): array
    {
        $faktor = max(0, 1 - (float) config('putni.umanjenje_po_obroku') * min(2, (int) $n->obroci));
        $tuzemna = (float) config('putni.dnevnica_tuzemna');
        $stavke = [];
        $dnevnice = 0.0;
        $tecaj = null;

        if ($n->vrsta === 'inozemni') {
            $granicaOd = $n->granica_odlazak_at ?? $n->polazak_at;
            $granicaDo = $n->granica_povratak_at ?? $n->povratak_at;
            $satiVani = self::sati($granicaOd, $granicaDo);
            $satiDoma = self::sati($n->polazak_at, $granicaOd) + self::sati($granicaDo, $n->povratak_at);

            [$iznosDrzave, $valuta] = config('putni.inozemne.'.$n->drzava) ?? config('putni.inozemne.Sve druge države');
            [$pune, $pola] = self::brojDnevnica($satiVani);
            $broj = $pune + $pola / 2;
            $uValuti = round($broj * $iznosDrzave * $faktor, 2);

            $uEurima = $uValuti;
            if ($valuta !== 'EUR' && $uValuti > 0) {
                $tecaj = (float) $n->tecaj ?: self::tecaj($valuta, $n->povratak_at ?? now());
                $uEurima = $tecaj ? round($uValuti / $tecaj, 2) : 0.0;
            }

            $stavke[] = [
                'opis' => 'Inozemne dnevnice — '.($n->drzava ?: 'Sve druge države'),
                'sati' => round($satiVani, 2), 'broj' => $broj,
                'jedinicni' => round($iznosDrzave * $faktor, 2), 'valuta' => $valuta,
                'u_valuti' => $uValuti, 'ukupno' => $uEurima,
            ];
            $dnevnice += $uEurima;

            [$puneT, $polaT] = self::brojDnevnica($satiDoma);
            if ($puneT + $polaT > 0) {
                $brojT = $puneT + $polaT / 2;
                $stavke[] = [
                    'opis' => 'Tuzemne dnevnice (u Hrvatskoj do i od granice)',
                    'sati' => round($satiDoma, 2), 'broj' => $brojT,
                    'jedinicni' => round($tuzemna * $faktor, 2), 'valuta' => 'EUR',
                    'u_valuti' => round($brojT * $tuzemna * $faktor, 2), 'ukupno' => round($brojT * $tuzemna * $faktor, 2),
                ];
                $dnevnice += round($brojT * $tuzemna * $faktor, 2);
            }
        } else {
            $sati = self::sati($n->polazak_at, $n->povratak_at);
            [$pune, $pola] = self::brojDnevnica($sati);
            $broj = $pune + $pola / 2;
            $stavke[] = [
                'opis' => 'Tuzemne dnevnice',
                'sati' => round($sati, 2), 'broj' => $broj,
                'jedinicni' => round($tuzemna * $faktor, 2), 'valuta' => 'EUR',
                'u_valuti' => round($broj * $tuzemna * $faktor, 2), 'ukupno' => round($broj * $tuzemna * $faktor, 2),
            ];
            $dnevnice += round($broj * $tuzemna * $faktor, 2);
        }

        // Kilometri: naknada samo za privatni automobil; kod službenog se gorivo upisuje kao trošak.
        $km = $n->km !== null ? (float) $n->km
            : (($n->km_pocetno !== null && $n->km_zavrsno !== null && $n->km_zavrsno > $n->km_pocetno)
                ? (float) ($n->km_zavrsno - $n->km_pocetno) : 0.0);
        $kmIznos = $n->prijevoz === 'privatni_auto' ? round($km * (float) config('putni.km_naknada'), 2) : 0.0;

        $troskovi = round((float) $n->troskovi->sum('iznos'), 2);
        $dnevnice = round($dnevnice, 2);

        return [
            'dnevnice' => $stavke,
            'dnevnice_ukupno' => $dnevnice,
            'obroci' => (int) $n->obroci,
            'km' => $km,
            'km_naknada' => (float) config('putni.km_naknada'),
            'km_iznos' => $kmIznos,
            'troskovi_ukupno' => $troskovi,
            'ukupno' => round($dnevnice + $kmIznos + $troskovi, 2),
            'predujam' => (float) $n->predujam,
            'za_isplatu' => round($dnevnice + $kmIznos + $troskovi - (float) $n->predujam, 2),
            'tecaj' => $tecaj,
            'propisi_provjereni' => config('putni.provjereno'),
        ];
    }

    /** Srednji tečaj HNB-a (1 EUR = x valute) na dan; null ako HNB nije dostupan (tečaj se tada upisuje ručno). */
    public static function tecaj(string $valuta, CarbonInterface $datum): ?float
    {
        $dan = $datum->toDateString();

        return Cache::remember("hnb-tecaj-{$valuta}-{$dan}", 86400, function () use ($valuta, $dan) {
            $odgovor = rescue(fn () => Http::timeout(8)->get('https://api.hnb.hr/tecajn-eur/v3', [
                'valuta' => $valuta, 'datum-primjene' => $dan,
            ])->json(), null, false);
            $srednji = $odgovor[0]['srednji_tecaj'] ?? null;

            return $srednji ? (float) str_replace(',', '.', $srednji) : null;
        });
    }
}
