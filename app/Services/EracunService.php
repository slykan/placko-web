<?php

namespace App\Services;

use App\Models\Racun;
use App\Models\TvrtkaPostavke;
use App\Models\UlazniEracun;
use DateTime;
use Einvoicing\AllowanceOrCharge;
use Einvoicing\Identifier;
use Einvoicing\Invoice;
use Einvoicing\InvoiceLine;
use Einvoicing\Party;
use Einvoicing\Payments\Transfer;
use Einvoicing\Presets\Peppol;
use Einvoicing\Writers\UblWriter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EracunService
{
    /**
     * Generiraj UBL 2.1 XML string iz računa.
     */
    public static function generirajXml(Racun $racun): string
    {
        $racun->load(['stavke', 'tvrtka', 'klijent']);
        $tvrtka  = $racun->tvrtka;
        $klijent = $racun->klijent;

        // ── Invoice ──────────────────────────────────────────────
        $invoice = new Invoice(Peppol::class);
        $invoice->setNumber($racun->broj);
        $invoice->setCurrency('EUR');
        $invoice->setIssueDate(new DateTime($racun->datum_izdavanja->format('Y-m-d')));

        if ($racun->datum_dospijeca) {
            $invoice->setDueDate(new DateTime($racun->datum_dospijeca->format('Y-m-d')));
        }

        if ($racun->datum_isporuke) {
            $invoice->setTaxPointDate(new DateTime($racun->datum_isporuke->format('Y-m-d')));
        }

        if ($racun->napomena) {
            $invoice->addNote($racun->napomena);
        }

        // ── Prodavatelj (Tvrtka) ──────────────────────────────────
        $prodavatelj = new Party();
        $prodavatelj->setName($tvrtka->naziv);
        $prodavatelj->setVatNumber($tvrtka->u_sustavu_pdv ? 'HR' . $tvrtka->oib : null);
        $prodavatelj->setCompanyId(new Identifier($tvrtka->oib, '0096')); // 0096 = OIB scheme

        if ($tvrtka->adresa) {
            $prodavatelj->setAddress([$tvrtka->adresa]);
        }
        if ($tvrtka->mjesto) {
            $prodavatelj->setCity($tvrtka->mjesto);
        }
        if ($tvrtka->po_broj) {
            $prodavatelj->setPostalCode($tvrtka->po_broj);
        }
        $prodavatelj->setCountry('HR');

        if ($tvrtka->email) {
            $prodavatelj->setContactEmail($tvrtka->email);
        }
        if ($tvrtka->kontakt_broj) {
            $prodavatelj->setContactPhone($tvrtka->kontakt_broj);
        }

        // Elektronička adresa prodavatelja (obavezno za PEPPOL)
        $prodavatelj->setElectronicAddress(
            new Identifier($tvrtka->email ?? $tvrtka->oib, '0184') // 0184 = email scheme
        );

        $invoice->setSeller($prodavatelj);

        // ── Kupac (Klijent) ───────────────────────────────────────
        $kupac = new Party();
        $kupac->setName($klijent->naziv);

        if ($klijent->oib) {
            $kupac->setCompanyId(new Identifier($klijent->oib, '0096'));
            // Ako je klijent PDV obveznik — pretpostavljamo HR OIB kao VAT ID
            $kupac->setVatNumber('HR' . $klijent->oib);
        }

        if ($klijent->adresa) {
            $kupac->setAddress([$klijent->adresa]);
        }
        if ($klijent->mjesto) {
            $kupac->setCity($klijent->mjesto);
        }
        if ($klijent->po_broj) {
            $kupac->setPostalCode($klijent->po_broj);
        }
        $kupac->setCountry('HR');

        if ($klijent->email) {
            $kupac->setContactEmail($klijent->email);
            $kupac->setElectronicAddress(new Identifier($klijent->email, '0184'));
        } elseif ($klijent->oib) {
            $kupac->setElectronicAddress(new Identifier($klijent->oib, '0096'));
        }

        $invoice->setBuyer($kupac);

        // ── Plaćanje — IBAN transakcija ───────────────────────────
        if ($tvrtka->iban) {
            $transfer = new Transfer();
            $transfer->setAccountId($tvrtka->iban);
            $transfer->setAccountName($tvrtka->naziv);
            if ($tvrtka->banka) {
                $transfer->setProvider($tvrtka->banka);
            }

            $payment = new \Einvoicing\Payments\Payment();
            $payment->addTransfer($transfer);

            // Poziv na broj
            $invoice->setPaymentTerms('Poziv na broj: ' . $racun->broj);
            $invoice->addPayment($payment);
        }

        // ── Stavke ────────────────────────────────────────────────
        foreach ($racun->stavke as $i => $stavka) {
            $bruto     = (float) $stavka->cijena * (float) $stavka->kolicina;
            $rabatP    = (float) ($stavka->rabat_posto ?? 0);
            $rabatIzn  = round($bruto * ($rabatP / 100), 8);
            $pdvStopa  = (float) ($stavka->pdv_stopa ?? 0);

            $linija = new InvoiceLine();
            $linija->setId((string) ($i + 1));
            $linija->setName($stavka->naziv);

            if ($stavka->opis) {
                $linija->setDescription($stavka->opis);
            }

            $linija->setPrice((float) $stavka->cijena);
            $linija->setQuantity((float) $stavka->kolicina);
            $linija->setUnit(static::jedinicaMjereKod($stavka->jedinica_mjere ?? 'kom'));

            // PDV
            if ($pdvStopa > 0) {
                $linija->setVatRate($pdvStopa);
                $linija->setVatCategory('S'); // Standard rate
            } else {
                $linija->setVatRate(0.0);
                $linija->setVatCategory($tvrtka->u_sustavu_pdv ? 'Z' : 'E'); // Zero / Exempt
            }

            // Rabat kao AllowanceCharge na stavci
            if ($rabatIzn > 0) {
                $popust = new AllowanceOrCharge();
                $popust->setAmount($rabatIzn);
                $popust->setReason('Rabat');
                $linija->addAllowance($popust);
            }

            $invoice->addLine($linija);
        }

        // ── Generiraj XML ─────────────────────────────────────────
        $writer = new UblWriter();
        return $writer->export($invoice);
    }

    /**
     * Pošalji eRačun na FINA servis putem mTLS-a.
     *
     * @return array{poruka_id: string|null, status_kod: int, odgovor: mixed}
     * @throws \RuntimeException
     */
    public static function posalji(Racun $racun, TvrtkaPostavke $postavke): array
    {
        if (! $postavke->eracun_aktivan) {
            throw new \RuntimeException('eRačun servis nije aktiviran u postavkama.');
        }

        if (! $postavke->eracun_cert_putanja) {
            throw new \RuntimeException('eRačun certifikat nije konfiguriran.');
        }

        if (! $postavke->eracun_api_url) {
            throw new \RuntimeException('eRačun API URL nije konfiguriran.');
        }

        [$tmpCert, $tmpKey] = static::izvuciPrivremeneKljuceve(
            $postavke->eracun_cert_putanja,
            $postavke->eracun_cert_lozinka ?? ''
        );

        try {
            $xml = static::generirajXml($racun);

            $url = rtrim($postavke->eracun_api_url, '/') . '/racuni';

            $response = Http::withOptions([
                'cert'    => $tmpCert,
                'ssl_key' => $tmpKey,
                'verify'  => true,
            ])->withHeaders([
                'Content-Type' => 'application/xml',
                'Accept'       => 'application/json',
            ])->withBody($xml, 'application/xml')
              ->post($url);

            if ($response->failed()) {
                throw new \RuntimeException(
                    'FINA eRačun servis vratio grešku ' . $response->status() . ': ' . $response->body()
                );
            }

            $tijelo = $response->json() ?? [];

            $porukaId = $tijelo['id'] ?? $tijelo['messageId'] ?? $tijelo['porukaId'] ?? null;

            return [
                'poruka_id' => $porukaId,
                'status_kod' => $response->status(),
                'odgovor'   => $tijelo,
            ];
        } finally {
            @unlink($tmpCert);
            @unlink($tmpKey);
        }
    }

    /**
     * Dohvati primljene eRačune s FINA servisa i spremi ih u bazu.
     *
     * @return array{novi: int, ukupno: int}
     * @throws \RuntimeException
     */
    public static function dohvatiPrimljene(TvrtkaPostavke $postavke): array
    {
        if (! $postavke->eracun_aktivan) {
            throw new \RuntimeException('eRačun servis nije aktiviran u postavkama.');
        }

        if (! $postavke->eracun_cert_putanja || ! $postavke->eracun_api_url) {
            throw new \RuntimeException('eRačun certifikat ili API URL nije konfiguriran.');
        }

        [$tmpCert, $tmpKey] = static::izvuciPrivremeneKljuceve(
            $postavke->eracun_cert_putanja,
            $postavke->eracun_cert_lozinka ?? ''
        );

        try {
            $url = rtrim($postavke->eracun_api_url, '/') . '/racuni/primljeni';

            $response = Http::withOptions([
                'cert'    => $tmpCert,
                'ssl_key' => $tmpKey,
                'verify'  => true,
            ])->withHeaders([
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->failed()) {
                throw new \RuntimeException(
                    'Greška pri dohvaćanju primljenih eRačuna ' . $response->status() . ': ' . $response->body()
                );
            }

            $lista   = $response->json() ?? [];
            $novi    = 0;
            $ukupno  = count($lista);

            foreach ($lista as $stavka) {
                $finaId = $stavka['id'] ?? $stavka['messageId'] ?? null;

                // Preskočiti ako već postoji
                if ($finaId && UlazniEracun::where('tvrtka_id', $postavke->tvrtka_id)
                        ->where('fina_id', $finaId)->exists()) {
                    continue;
                }

                // Dohvati XML pojedinog računa ako API vraća zasebni endpoint
                $xml = null;
                if (isset($stavka['xmlUrl'])) {
                    try {
                        $xmlResp = Http::withOptions([
                            'cert'    => $tmpCert,
                            'ssl_key' => $tmpKey,
                            'verify'  => true,
                        ])->get($stavka['xmlUrl']);
                        $xml = $xmlResp->ok() ? $xmlResp->body() : null;
                    } catch (\Throwable $e) {
                        Log::warning('eRačun: ne mogu dohvatiti XML za ' . $finaId . ': ' . $e->getMessage());
                    }
                } elseif (isset($stavka['xml'])) {
                    $xml = $stavka['xml'];
                }

                $podaci = static::parsirajUlazniXml($xml ?? '');

                UlazniEracun::create([
                    'tvrtka_id'       => $postavke->tvrtka_id,
                    'fina_id'         => $finaId,
                    'broj_racuna'     => $stavka['brojRacuna'] ?? $podaci['broj_racuna'] ?? null,
                    'dobavljac_naziv' => $stavka['dobavljacNaziv'] ?? $podaci['dobavljac_naziv'] ?? null,
                    'dobavljac_oib'   => $stavka['dobavljacOib'] ?? $podaci['dobavljac_oib'] ?? null,
                    'datum_izdavanja' => $stavka['datumIzdavanja'] ?? $podaci['datum_izdavanja'] ?? null,
                    'datum_dospijeca' => $stavka['datumDospijeca'] ?? $podaci['datum_dospijeca'] ?? null,
                    'iznos'           => $stavka['iznos'] ?? $podaci['iznos'] ?? 0,
                    'valuta'          => $stavka['valuta'] ?? $podaci['valuta'] ?? 'EUR',
                    'status'          => 'nova',
                    'xml'             => $xml,
                    'primljeno_at'    => now(),
                ]);

                $novi++;
            }

            return ['novi' => $novi, 'ukupno' => $ukupno];
        } finally {
            @unlink($tmpCert);
            @unlink($tmpKey);
        }
    }

    /**
     * Testiraj eRačun certifikat - vrati array s informacijama.
     *
     * @throws \RuntimeException
     */
    public static function testirajEracunCertifikat(string $certPutanja, string $lozinka): array
    {
        $putanja = Storage::disk('local')->path($certPutanja);

        if (! file_exists($putanja)) {
            throw new \RuntimeException('Certifikat nije pronađen na serveru.');
        }

        if (file_exists(base_path('openssl-legacy.cnf'))) {
            putenv('OPENSSL_CONF=' . base_path('openssl-legacy.cnf'));
        }

        $sadrzaj = file_get_contents($putanja);
        $ok = openssl_pkcs12_read($sadrzaj, $certs, $lozinka);

        if (! $ok) {
            throw new \RuntimeException('Lozinka je pogrešna ili je certifikat oštećen.');
        }

        $certInfo = openssl_x509_parse($certs['cert']);

        return [
            'subjekt'    => $certInfo['subject']['CN'] ?? 'N/A',
            'izdavac'    => $certInfo['issuer']['CN'] ?? 'N/A',
            'vrijedi_do' => isset($certInfo['validTo_time_t'])
                ? date('d.m.Y.', $certInfo['validTo_time_t'])
                : 'N/A',
        ];
    }

    /**
     * Parsira UBL XML i izvuče ključne podatke.
     */
    public static function parsirajUlazniXml(string $xml): array
    {
        if (empty($xml)) {
            return [];
        }

        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $xpath = new \DOMXPath($dom);

            // UBL 2.1 namespace
            $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

            $get = fn (string $q) => $xpath->evaluate('string(' . $q . ')', $dom);

            return [
                'broj_racuna'     => $get('//cbc:ID') ?: null,
                'datum_izdavanja' => $get('//cbc:IssueDate') ?: null,
                'datum_dospijeca' => $get('//cac:PaymentMeans/cbc:PaymentDueDate') ?: null,
                'iznos'           => (float) ($get('//cac:LegalMonetaryTotal/cbc:PayableAmount') ?: 0),
                'valuta'          => $get('//cbc:DocumentCurrencyCode') ?: 'EUR',
                'dobavljac_naziv' => $get('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name') ?: null,
                'dobavljac_oib'   => $get('//cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID') ?: null,
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Iz .p12 certifikata izvuci PEM cert i ključ u privremene fajlove.
     *
     * @return array{0: string, 1: string} Putanje do [cert.pem, key.pem]
     * @throws \RuntimeException
     */
    private static function izvuciPrivremeneKljuceve(string $certPutanja, string $lozinka): array
    {
        $putanja = Storage::disk('local')->path($certPutanja);

        if (! file_exists($putanja)) {
            throw new \RuntimeException('eRačun certifikat nije pronađen: ' . $certPutanja);
        }

        if (file_exists(base_path('openssl-legacy.cnf'))) {
            putenv('OPENSSL_CONF=' . base_path('openssl-legacy.cnf'));
        }

        $sadrzaj = file_get_contents($putanja);
        $ok = openssl_pkcs12_read($sadrzaj, $certs, $lozinka);

        if (! $ok) {
            throw new \RuntimeException('Neispravan eRačun certifikat ili lozinka.');
        }

        $tmpCert = tempnam(sys_get_temp_dir(), 'eracun_c_');
        $tmpKey  = tempnam(sys_get_temp_dir(), 'eracun_k_');

        file_put_contents($tmpCert, $certs['cert']);
        file_put_contents($tmpKey, $certs['pkey']);

        return [$tmpCert, $tmpKey];
    }

    /**
     * Mapira tekstualnu JM u UN/ECE recomendation 20 kod.
     */
    private static function jedinicaMjereKod(string $jm): string
    {
        return match (strtolower(trim($jm))) {
            'kom', 'kos', 'pc', 'pcs', 'komad' => 'C62', // piece
            'h', 'sat', 'hr', 'hour'            => 'HUR', // hour
            'dan', 'day', 'd'                   => 'DAY', // day
            'mj', 'month', 'mjes'               => 'MON', // month
            'kg'                                => 'KGM', // kilogram
            'g', 'gram'                         => 'GRM', // gram
            't', 'tona'                         => 'TNE', // tonne
            'l', 'lit', 'litra'                 => 'LTR', // litre
            'm'                                 => 'MTR', // metre
            'm2'                                => 'MTK', // square metre
            'm3'                                => 'MTQ', // cubic metre
            'km'                                => 'KMT', // kilometre
            default                             => 'C62', // fallback: piece
        };
    }
}
