<?php

namespace App\Services;

use App\Models\Racun;
use App\Models\TvrtkaPostavke;
use Illuminate\Support\Facades\Storage;
use Nticaric\Fiskalizacija\Fiskalizacija;
use Nticaric\Fiskalizacija\Generators\BrojRacunaType;
use Nticaric\Fiskalizacija\Generators\PorezType;
use Nticaric\Fiskalizacija\Generators\RacunType;
use Nticaric\Fiskalizacija\Generators\RacunZahtjev;
use Nticaric\Fiskalizacija\Generators\ZaglavljeType;

class FiskalizacijaService
{
    /**
     * Fiskaliziraj račun — pošalji na PU i spremi ZKI + JIR.
     */
    public static function fiskaliziraj(Racun $racun, bool $demo = false): array
    {
        $tvrtka   = $racun->tvrtka;
        $postavke = TvrtkaPostavke::where('tvrtka_id', $tvrtka->id)->firstOrFail();

        if (! $postavke->fiskalizacija_aktivna) {
            throw new \Exception('Fiskalizacija nije aktivirana za ovu tvrtku.');
        }

        // Legacy OpenSSL provider za FINA certifikate (RC2/3DES algoritmi)
        if (file_exists(base_path('openssl-legacy.cnf'))) {
            putenv('OPENSSL_CONF=' . base_path('openssl-legacy.cnf'));
        }

        $certPutanja = Storage::disk('local')->path($postavke->fina_cert_putanja);
        if (! file_exists($certPutanja)) {
            throw new \Exception('FINA certifikat nije pronađen na: ' . $certPutanja);
        }

        // Inicijalizacija
        $fis = new Fiskalizacija(
            $certPutanja,
            $postavke->fina_cert_lozinka,
            'SSL',
            $demo
        );

        // Broj računa — format: redni_broj / prostorOznaka / uredajOznaka
        $brRac = new BrojRacunaType(
            $racun->redni_broj,
            $postavke->fis_prostor_oznaka,
            $postavke->fis_uredaj_oznaka
        );

        // Datum i vrijeme
        $datVrijeme = $racun->datum_izdavanja->format('d.m.Y') . 'T' .
            ($racun->vrijeme_izdavanja
                ? \Carbon\Carbon::parse($racun->vrijeme_izdavanja)->format('H:i:s')
                : '00:00:00');

        // PDV stavke
        $pdvStavke = [];
        $pdvGrupe  = $racun->stavke->groupBy('pdv_stopa');

        foreach ($pdvGrupe as $stopa => $stavke) {
            $stopaFloat = (float) $stopa;
            if ($stopaFloat == 0) {
                continue;
            }
            $osnovica = $stavke->sum(function ($s) {
                $bruto     = (float) $s->cijena * (float) $s->kolicina;
                $rabatIzn  = $bruto * ((float) ($s->rabat_posto ?? 0) / 100);
                return round($bruto - $rabatIzn, 2);
            });
            $pdvIznos = round($osnovica * ($stopaFloat / 100), 2);
            $pdvStavke[] = new PorezType($stopaFloat, $osnovica, $pdvIznos, null);
        }

        // Račun objekt
        $bill = new RacunType();
        $bill->setOib($tvrtka->oib);
        $bill->setOznSlijed('P'); // P = poslovni prostor, B = blagajna
        $bill->setUSustPdv($tvrtka->u_sustavu_pdv ? true : false);
        $bill->setDatVrijeme($datVrijeme);
        $bill->setBrRac($brRac);
        $bill->setIznosUkupno((float) $racun->ukupno);
        $bill->setNacinPlac(static::nacinPlacanja($racun->nacin_placanja));
        $bill->setOibOper($tvrtka->oib); // OIB operatera = OIB tvrtke za obrtnike
        $bill->setNakDost(false); // false = nije naknadna dostava

        if (! empty($pdvStavke)) {
            $bill->setPdv($pdvStavke);
        }

        // ZKI — generira se lokalno s privatnim ključem
        $zki = $bill->generirajZastKod(
            $fis->getPrivateKey(),
            $bill->getOib(),
            $bill->getDatVrijeme(),
            $bill->getBrRac()->getBrOznRac(),
            $bill->getBrRac()->getOznPosPr(),
            $bill->getBrRac()->getOznNapUr(),
            $bill->getIznosUkupno()
        );
        $bill->setZastKod($zki);

        // Zaglavlje
        $zaglavlje = new ZaglavljeType();
        $zaglavlje->setIdPoruke(static::generirajIdPoruke());
        $zaglavlje->setDatumVrijeme(now()->format('d.m.Y\TH:i:s'));

        // Zahtjev
        $zahtjev = new RacunZahtjev();
        $zahtjev->setZaglavlje($zaglavlje);
        $zahtjev->setRacun($bill);

        // Pošalji
        $odgovor = $fis->signAndSend($zahtjev);

        // Parsiraj JIR iz odgovora
        $jir = $odgovor->getJir();

        // Spremi na račun
        $racun->update([
            'zki'              => $zki,
            'jir'              => $jir,
            'fiskaliziran_at'  => now(),
        ]);

        return ['zki' => $zki, 'jir' => $jir];
    }

    /**
     * Provjeri je li račun potrebno fiskalizirati.
     */
    public static function trebaMFiskalizirati(Racun $racun): bool
    {
        if (! in_array($racun->nacin_placanja, ['gotovina', 'kartica'])) {
            return false;
        }

        $postavke = TvrtkaPostavke::where('tvrtka_id', $racun->tvrtka_id)->first();
        return $postavke?->fiskalizacija_aktivna ?? false;
    }

    private static function nacinPlacanja(string $nacin): string
    {
        return match ($nacin) {
            'gotovina' => 'G',
            'kartica'  => 'K',
            'virman'   => 'T',
            default    => 'O', // ostalo
        };
    }

    private static function generirajIdPoruke(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private static function parsirajJir(mixed $odgovor): ?string
    {
        try {
            $xml = new \DOMDocument();
            $xml->loadXML($odgovor);
            $jirNodes = $xml->getElementsByTagNameNS(
                'http://www.apis-it.hr/fin/2012/types/f73',
                'Jir'
            );
            return $jirNodes->length > 0 ? $jirNodes->item(0)->nodeValue : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
