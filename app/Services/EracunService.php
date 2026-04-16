<?php

namespace App\Services;

use App\Models\Racun;
use App\Models\TvrtkaPostavke;
use App\Models\UlazniEracun;
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
use Illuminate\Support\Str;
use DateTime;

class EracunService
{
    // ────────────────────────────────────────────────────────────────────────
    // UBL XML — za direktno preuzimanje (download)
    // ────────────────────────────────────────────────────────────────────────

    public static function generirajXml(Racun $racun): string
    {
        $racun->load(['stavke', 'tvrtka', 'klijent']);
        $tvrtka  = $racun->tvrtka;
        $klijent = $racun->klijent;

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

        $prodavatelj = new Party();
        $prodavatelj->setName($tvrtka->naziv);
        $prodavatelj->setVatNumber($tvrtka->u_sustavu_pdv ? 'HR' . $tvrtka->oib : null);
        $prodavatelj->setCompanyId(new Identifier($tvrtka->oib, '0096'));
        if ($tvrtka->adresa)       $prodavatelj->setAddress([$tvrtka->adresa]);
        if ($tvrtka->mjesto)       $prodavatelj->setCity($tvrtka->mjesto);
        if ($tvrtka->po_broj)      $prodavatelj->setPostalCode($tvrtka->po_broj);
        $prodavatelj->setCountry('HR');
        if ($tvrtka->email)        $prodavatelj->setContactEmail($tvrtka->email);
        if ($tvrtka->kontakt_broj) $prodavatelj->setContactPhone($tvrtka->kontakt_broj);
        $prodavatelj->setElectronicAddress(new Identifier($tvrtka->email ?? $tvrtka->oib, '0184'));
        $invoice->setSeller($prodavatelj);

        $kupac = new Party();
        $kupac->setName($klijent->naziv);
        if ($klijent->oib) {
            $kupac->setCompanyId(new Identifier($klijent->oib, '0096'));
            $kupac->setVatNumber('HR' . $klijent->oib);
        }
        if ($klijent->adresa)  $kupac->setAddress([$klijent->adresa]);
        if ($klijent->mjesto)  $kupac->setCity($klijent->mjesto);
        if ($klijent->po_broj) $kupac->setPostalCode($klijent->po_broj);
        $kupac->setCountry('HR');
        if ($klijent->email) {
            $kupac->setContactEmail($klijent->email);
            $kupac->setElectronicAddress(new Identifier($klijent->email, '0184'));
        } elseif ($klijent->oib) {
            $kupac->setElectronicAddress(new Identifier($klijent->oib, '0096'));
        }
        $invoice->setBuyer($kupac);

        if ($tvrtka->iban) {
            $transfer = new Transfer();
            $transfer->setAccountId($tvrtka->iban);
            $transfer->setAccountName($tvrtka->naziv);
            if ($tvrtka->banka) $transfer->setProvider($tvrtka->banka);
            $payment = new \Einvoicing\Payments\Payment();
            $payment->addTransfer($transfer);
            $invoice->setPaymentTerms('Poziv na broj: ' . $racun->broj);
            $invoice->addPayment($payment);
        }

        foreach ($racun->stavke as $i => $stavka) {
            $bruto    = (float) $stavka->cijena * (float) $stavka->kolicina;
            $rabatP   = (float) ($stavka->rabat_posto ?? 0);
            $rabatIzn = round($bruto * ($rabatP / 100), 8);
            $pdvStopa = (float) ($stavka->pdv_stopa ?? 0);

            $linija = new InvoiceLine();
            $linija->setId((string) ($i + 1));
            $linija->setName($stavka->naziv);
            if ($stavka->opis) $linija->setDescription($stavka->opis);
            $linija->setPrice((float) $stavka->cijena);
            $linija->setQuantity((float) $stavka->kolicina);
            $linija->setUnit(static::jedinicaMjereKod($stavka->jedinica_mjere ?? 'kom'));

            if ($pdvStopa > 0) {
                $linija->setVatRate($pdvStopa);
                $linija->setVatCategory('S');
            } else {
                $linija->setVatRate(0.0);
                $linija->setVatCategory($tvrtka->u_sustavu_pdv ? 'Z' : 'E');
            }

            if ($rabatIzn > 0) {
                $popust = new AllowanceOrCharge();
                $popust->setAmount($rabatIzn);
                $popust->setReason('Rabat');
                $linija->addAllowance($popust);
            }

            $invoice->addLine($linija);
        }

        return (new UblWriter())->export($invoice);
    }

    // ────────────────────────────────────────────────────────────────────────
    // MIDDLEWARE — slanje izlaznog eRačuna
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Pošalji izlazni račun putem FINA eRačun middlewarea.
     *
     * @return array{poruka_id: string|null, status_kod: int, odgovor: mixed}
     * @throws \RuntimeException
     */
    public static function posalji(Racun $racun, TvrtkaPostavke $postavke): array
    {
        static::provjeriMiddlewarePostavke($postavke);

        $racun->load(['stavke', 'tvrtka', 'klijent']);

        $tvrtka  = $racun->tvrtka;
        $klijent = $racun->klijent;
        $okolina = $postavke->eracun_demo ? 'prez' : 'prod';
        $idPoruke = (string) Str::uuid();

        $xml = static::buildCiusHrXml($racun);

        $tijelo = [
            'okolina'               => $okolina,
            'idPoruke'              => $idPoruke,
            'idDobavljaca'          => '9934:' . $tvrtka->oib,
            'idKupca'               => $klijent->oib ? '9934:' . $klijent->oib : ($klijent->email ?? ''),
            'idSpecifikacije'       => 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0',
            'idDokumentaDobavljaca' => $racun->broj,
            'xmlDokumentaRacuna'    => base64_encode($xml),
        ];

        if ($postavke->eracun_jks_uuid) {
            $tijelo['jksUuid'] = $postavke->eracun_jks_uuid;
        }

        $url = rtrim($postavke->eracun_middleware_url, '/') . '/sendB2BInvoiceCall';

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $tijelo);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Middleware vratio grešku ' . $response->status() . ': ' . $response->body()
            );
        }

        $odgovor  = $response->json() ?? [];
        $porukaId = $odgovor['idPoruke'] ?? $odgovor['id'] ?? $idPoruke;

        return [
            'poruka_id'  => $porukaId,
            'status_kod' => $response->status(),
            'odgovor'    => $odgovor,
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    // MIDDLEWARE — dohvat primljenih eRačuna
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Dohvati primljene eRačune s middlewarea i spremi nove u bazu.
     *
     * @return array{novi: int, ukupno: int}
     * @throws \RuntimeException
     */
    public static function dohvatiPrimljene(TvrtkaPostavke $postavke): array
    {
        static::provjeriMiddlewarePostavke($postavke);

        $tvrtka  = $postavke->tvrtka;
        $okolina = $postavke->eracun_demo ? 'prez' : 'prod';
        $base    = rtrim($postavke->eracun_middleware_url, '/');

        // 1. Dohvati listu primljenih
        $listTijelo = [
            'okolina'  => $okolina,
            'idPoruke' => (string) Str::uuid(),
            'idKupca'  => '9934:' . $tvrtka->oib,
        ];
        if ($postavke->eracun_jks_uuid) {
            $listTijelo['jksUuid'] = $postavke->eracun_jks_uuid;
        }

        $listResponse = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($base . '/getInvoiceListB2BRestCall', $listTijelo);

        if ($listResponse->failed()) {
            throw new \RuntimeException(
                'Greška pri dohvaćanju liste eRačuna ' . $listResponse->status() . ': ' . $listResponse->body()
            );
        }

        $lista   = $listResponse->json() ?? [];
        $stavke  = $lista['lista'] ?? $lista['invoices'] ?? (is_array($lista) ? $lista : []);
        $novi    = 0;
        $ukupno  = count($stavke);

        foreach ($stavke as $stavka) {
            $finaId = $stavka['idDokumenta'] ?? $stavka['id'] ?? null;

            if (! $finaId) {
                continue;
            }

            // Preskoči ako već postoji
            if (UlazniEracun::where('tvrtka_id', $postavke->tvrtka_id)
                    ->where('fina_id', (string) $finaId)->exists()) {
                continue;
            }

            // 2. Dohvati detalje pojedinog računa
            $detaljTijelo = [
                'okolina'     => $okolina,
                'idPoruke'    => (string) Str::uuid(),
                'idDokumenta' => $finaId,
                'idKupca'     => '9934:' . $tvrtka->oib,
            ];
            if ($postavke->eracun_jks_uuid) {
                $detaljTijelo['jksUuid'] = $postavke->eracun_jks_uuid;
            }

            $detaljResponse = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($base . '/getInvoiceB2BRestCall', $detaljTijelo);

            $detalj = $detaljResponse->ok() ? ($detaljResponse->json() ?? []) : [];

            // XML može biti u xmlContent ili xmlRacun polju
            $xml = $detalj['xmlContent'] ?? $detalj['xmlRacun'] ?? $detalj['xml'] ?? null;

            // Pokušaj parsirati iz XML-a ako ga ima
            $podaci = $xml ? static::parsirajUlazniXml($xml) : [];

            // Fallback na JSON polja iz odgovora
            UlazniEracun::create([
                'tvrtka_id'       => $postavke->tvrtka_id,
                'fina_id'         => (string) $finaId,
                'broj_racuna'     => $stavka['idDokumentaDobavljaca'] ?? $detalj['idDokumentaDobavljaca'] ?? $podaci['broj_racuna'] ?? null,
                'dobavljac_naziv' => $stavka['nazivDobavljaca'] ?? $detalj['nazivDobavljaca'] ?? $podaci['dobavljac_naziv'] ?? null,
                'dobavljac_oib'   => $stavka['oibDobavljaca'] ?? $detalj['oibDobavljaca'] ?? $podaci['dobavljac_oib'] ?? null,
                'datum_izdavanja' => $stavka['datumIzdavanja'] ?? $detalj['datumIzdavanja'] ?? $podaci['datum_izdavanja'] ?? null,
                'datum_dospijeca' => $stavka['datumDospijeca'] ?? $detalj['datumDospijeca'] ?? $podaci['datum_dospijeca'] ?? null,
                'iznos'           => $stavka['iznos'] ?? $detalj['iznos'] ?? $podaci['iznos'] ?? 0,
                'valuta'          => $stavka['valuta'] ?? $detalj['valuta'] ?? $podaci['valuta'] ?? 'EUR',
                'status'          => 'nova',
                'xml'             => $xml,
                'primljeno_at'    => now(),
            ]);

            $novi++;
        }

        return ['novi' => $novi, 'ukupno' => $ukupno];
    }

    // ────────────────────────────────────────────────────────────────────────
    // MIDDLEWARE — promjena statusa primljenog eRačuna
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Promijeni status primljenog eRačuna na FINA sustavu.
     *
     * @param  string  $status  'prihvacena' | 'odbijena'
     * @param  string|null  $sifraRazloga  npr. 'VAT_REASON', 'OTHER_REASON'
     * @throws \RuntimeException
     */
    public static function promijeniStatus(
        UlazniEracun $eracun,
        string $status,
        TvrtkaPostavke $postavke,
        ?string $sifraRazloga = null,
        ?string $napomena = null
    ): void {
        static::provjeriMiddlewarePostavke($postavke);

        if (! $eracun->fina_id) {
            throw new \RuntimeException('eRačun nema FINA ID — nije primljen putem sustava.');
        }

        $finaStatus = match ($status) {
            'prihvacena' => 'ACCEPTED',
            'odbijena'   => 'REJECTED',
            default      => throw new \RuntimeException('Nepoznati status: ' . $status),
        };

        $tijelo = [
            'okolina'         => $postavke->eracun_demo ? 'prez' : 'prod',
            'idPoruke'        => (string) Str::uuid(),
            'idDokumenta'     => (int) $eracun->fina_id,
            'idKupca'         => '9934:' . $postavke->tvrtka->oib,
            'statusDokumenta' => $finaStatus,
        ];

        if ($postavke->eracun_jks_uuid) {
            $tijelo['jksUuid'] = $postavke->eracun_jks_uuid;
        }
        if ($sifraRazloga) {
            $tijelo['sifraRazloga'] = $sifraRazloga;
        }
        if ($napomena) {
            $tijelo['napomena'] = $napomena;
        }

        $url      = rtrim($postavke->eracun_middleware_url, '/') . '/changeInvoiceStatusB2BRestCall';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $tijelo);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Greška pri promjeni statusa eRačuna ' . $response->status() . ': ' . $response->body()
            );
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // MIDDLEWARE — provjera statusa poslanog eRačuna
    // ────────────────────────────────────────────────────────────────────────

    /**
     * @throws \RuntimeException
     */
    public static function provjeriStatusPoslanog(Racun $racun, TvrtkaPostavke $postavke): array
    {
        static::provjeriMiddlewarePostavke($postavke);

        $tijelo = [
            'okolina'                => $postavke->eracun_demo ? 'prez' : 'prod',
            'idPoruke'               => (string) Str::uuid(),
            'idDobavljaca'           => '9934:' . $racun->tvrtka->oib,
            'idDokumentaDobavljaca'  => $racun->broj,
            'godina'                 => (string) $racun->datum_izdavanja->year,
        ];

        $url      = rtrim($postavke->eracun_middleware_url, '/') . '/getB2BOutgoingInvoiceStatus';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $tijelo);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Greška pri provjeri statusa ' . $response->status() . ': ' . $response->body()
            );
        }

        return $response->json() ?? [];
    }

    // ────────────────────────────────────────────────────────────────────────
    // Parsiranje UBL XML-a ulaznog eRačuna
    // ────────────────────────────────────────────────────────────────────────

    public static function parsirajUlazniXml(string $xml): array
    {
        if (empty($xml)) {
            return [];
        }

        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $xpath = new \DOMXPath($dom);

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

    // ────────────────────────────────────────────────────────────────────────
    // Interni helperi
    // ────────────────────────────────────────────────────────────────────────

    // ────────────────────────────────────────────────────────────────────────
    // Testiranje lokalnog .p12 certifikata
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Provjeri je li .p12 certifikat ispravan i vrati informacije o njemu.
     *
     * @throws \RuntimeException
     */
    public static function testirajEracunCertifikat(string $certPutanja, string $lozinka): array
    {
        $putanja = \Illuminate\Support\Facades\Storage::disk('local')->path($certPutanja);

        if (! file_exists($putanja)) {
            throw new \RuntimeException('Certifikat nije pronađen na serveru.');
        }

        $legacyCnf = base_path('openssl-legacy.cnf');
        $env       = file_exists($legacyCnf) ? 'OPENSSL_CONF=' . escapeshellarg($legacyCnf) . ' ' : '';
        $p12       = escapeshellarg($putanja);
        $pass      = escapeshellarg($lozinka);

        $tmpCert = tempnam(sys_get_temp_dir(), 'eracun_test_') . '.pem';
        exec("{$env}openssl pkcs12 -in {$p12} -clcerts -nokeys -out " . escapeshellarg($tmpCert) . " -passin pass:{$pass} -legacy 2>&1", $out, $rc);

        if ($rc !== 0 || ! file_exists($tmpCert) || filesize($tmpCert) === 0) {
            @unlink($tmpCert);
            throw new \RuntimeException('Lozinka je pogrešna ili je certifikat oštećen.');
        }

        $certPem  = file_get_contents($tmpCert);
        @unlink($tmpCert);
        $certInfo = openssl_x509_parse($certPem);

        return [
            'subjekt'    => $certInfo['subject']['CN'] ?? 'N/A',
            'izdavac'    => $certInfo['issuer']['CN'] ?? 'N/A',
            'vrijedi_do' => isset($certInfo['validTo_time_t'])
                ? date('d.m.Y.', $certInfo['validTo_time_t'])
                : 'N/A',
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    // MIDDLEWARE — automatska registracija certifikata
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Konvertira .p12 u .jks, registrira u middlewareu i vraća UUID.
     *
     * @throws \RuntimeException
     */
    public static function registrirajCertifikat(
        string $p12RelativePath,
        string $lozinka,
        string $middlewareUrl,
        bool $demo = true,
        ?string $stariUuid = null
    ): string {
        $keytool = env(
            'ERACUN_KEYTOOL_PATH',
            '/home/placko/eRacunMiddleware-2-2-3/jre/openlogic-openjdk-jre-17.0.16+8-linux-x64/bin/keytool'
        );

        if (! file_exists($keytool)) {
            throw new \RuntimeException('keytool nije pronađen: ' . $keytool);
        }

        $p12Abs = \Illuminate\Support\Facades\Storage::disk('local')->path($p12RelativePath);
        if (! file_exists($p12Abs)) {
            throw new \RuntimeException('Certifikat nije pronađen na serveru.');
        }

        $jksPath = dirname($p12Abs) . '/' . pathinfo($p12Abs, PATHINFO_FILENAME) . '.jks';
        $alias   = 'eracun';

        // 1. Kreiraj novi .p12 s čistim aliasom "eracun" putem openssl
        //    (original .p12 može imati UTF-8 alias koji JKS ne podnosi)
        $tmpKeyPem  = $p12Abs . '.key.pem';
        $tmpCertPem = $p12Abs . '.cert.pem';
        $tmpP12     = $p12Abs . '.clean.p12';

        $openssl  = env('OPENSSL_PATH', '/usr/bin/openssl');
        $legacy   = file_exists(base_path('openssl-legacy.cnf'))
            ? 'OPENSSL_CONF=' . escapeshellarg(base_path('openssl-legacy.cnf')) . ' '
            : '';

        // Spremi lozinku u file da izbjegnemo shell escaping probleme
        $passFile = dirname($p12Abs) . '/.pass_' . uniqid();
        file_put_contents($passFile, $lozinka);

        exec($legacy . escapeshellarg($openssl) . ' pkcs12 -in ' . escapeshellarg($p12Abs)
            . ' -nocerts -nodes -passin file:' . escapeshellarg($passFile)
            . ' -out ' . escapeshellarg($tmpKeyPem) . ' -legacy 2>&1', $o1, $rc1);

        exec($legacy . escapeshellarg($openssl) . ' pkcs12 -in ' . escapeshellarg($p12Abs)
            . ' -clcerts -nokeys -passin file:' . escapeshellarg($passFile)
            . ' -out ' . escapeshellarg($tmpCertPem) . ' -legacy 2>&1', $o2, $rc2);

        @unlink($passFile);

        if ($rc1 !== 0 || $rc2 !== 0 || ! file_exists($tmpKeyPem) || filesize($tmpKeyPem) === 0
            || ! file_exists($tmpCertPem) || filesize($tmpCertPem) === 0) {
            $err = implode(' | ', array_filter(array_merge($o1, $o2)));
            @unlink($tmpKeyPem); @unlink($tmpCertPem);
            throw new \RuntimeException('Greška pri ekstrakciji certifikata iz .p12: ' . $err);
        }

        $passOutFile = dirname($p12Abs) . '/.passout_' . uniqid();
        file_put_contents($passOutFile, $lozinka);

        exec($legacy . escapeshellarg($openssl) . ' pkcs12 -export'
            . ' -in ' . escapeshellarg($tmpCertPem)
            . ' -inkey ' . escapeshellarg($tmpKeyPem)
            . ' -name ' . escapeshellarg($alias)
            . ' -passout file:' . escapeshellarg($passOutFile)
            . ' -out ' . escapeshellarg($tmpP12) . ' 2>&1', $o3, $rc3);

        @unlink($passOutFile);

        @unlink($tmpKeyPem); @unlink($tmpCertPem);

        if ($rc3 !== 0 || ! file_exists($tmpP12)) {
            @unlink($tmpP12);
            throw new \RuntimeException('Greška pri kreiranju čistog .p12 s aliasom "eracun".');
        }

        // 2. Konvertiraj čisti .p12 → .jks
        $convertCmd = implode(' ', [
            escapeshellarg($keytool),
            '-importkeystore',
            '-srckeystore', escapeshellarg($tmpP12),
            '-srcstoretype PKCS12',
            '-destkeystore', escapeshellarg($jksPath),
            '-deststoretype JKS',
            '-srcstorepass', escapeshellarg($lozinka),
            '-deststorepass', escapeshellarg($lozinka),
            '-noprompt 2>&1',
        ]);
        exec($convertCmd, $out, $rc);
        @unlink($tmpP12);

        if ($rc !== 0 || ! file_exists($jksPath)) {
            throw new \RuntimeException('Greška pri konverziji .p12 u JKS: ' . implode(' ', $out));
        }

        // 3. Dodaj Sectigo intermediate u JKS (CXF koristi JKS i kao truststore)
        $sectigoCert = env('ERACUN_SECTIGO_CERT', '/home/placko/sectigo_intermediate.pem');
        if (file_exists($sectigoCert)) {
            $addCmd = implode(' ', [
                escapeshellarg($keytool),
                '-import -trustcacerts',
                '-alias sectigo-intermediate',
                '-file', escapeshellarg($sectigoCert),
                '-keystore', escapeshellarg($jksPath),
                '-storepass', escapeshellarg($lozinka),
                '-noprompt',
            ]);
            exec('bash -c ' . escapeshellarg($addCmd) . ' 2>&1');
        }

        $baseUrl = rtrim($middlewareUrl, '/');
        $okolina = $demo ? 'prez' : 'prod';
        $prefix  = "b2b_{$okolina}_";

        // 4. Obriši stari UUID iz middlewarea ako postoji
        if ($stariUuid) {
            try {
                Http::timeout(5)->post($baseUrl . '/obrisiB2BPostavku/' . $okolina . '/' . $stariUuid);
            } catch (\Throwable) {
                // non-critical
            }
        }

        // 5. Registriraj novi JKS u middlewareu
        Http::timeout(10)->asForm()->post($baseUrl . '/spremiB2BPostavke', [
            "{$prefix}putanjaJsk"         => $jksPath,
            "{$prefix}passJks"            => $lozinka,
            "{$prefix}nazivCertifikata"   => $alias,
        ]);

        // 6. Dohvati UUID parsiranjem settings stranice
        $html = Http::timeout(10)->get($baseUrl . '/postavke?type=B2B')->body();

        $jksEsc = preg_quote($jksPath, '/');
        if (preg_match(
            '/<td>([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})<\/td>\s*<td>' . $jksEsc . '<\/td>/s',
            $html,
            $matches
        )) {
            return $matches[1];
        }

        throw new \RuntimeException('UUID nije pronađen u middleware odgovoru. Provjerite middleware logs.');
    }

    private static function stripNulls(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result[$key] = is_array($value) ? static::stripNulls($value) : $value;
        }
        return $result;
    }

    private static function provjeriMiddlewarePostavke(TvrtkaPostavke $postavke): void
    {
        if (! $postavke->eracun_aktivan) {
            throw new \RuntimeException('eRačun servis nije aktiviran u postavkama.');
        }

        if (! $postavke->eracun_middleware_url) {
            throw new \RuntimeException('eRačun Middleware URL nije konfiguriran u postavkama.');
        }
    }

    /**
     * Gradi FINA JSON strukturu računa (jsonContentRacun).
     */
    private static function buildJsonRacun(Racun $racun): array
    {
        $tvrtka  = $racun->tvrtka;
        $klijent = $racun->klijent;

        $datumIzdavanja = $racun->datum_izdavanja->format('Y-m-d');
        $datumDospijeca = $racun->datum_dospijeca?->format('Y-m-d');
        $datumIsporuke  = $racun->datum_isporuke?->format('Y-m-d') ?? $datumIzdavanja;
        $vrijemeIzd     = $racun->vrijeme_izdavanja
            ? (is_string($racun->vrijeme_izdavanja)
                ? $racun->vrijeme_izdavanja
                : $racun->vrijeme_izdavanja->format('H:i:s'))
            : '00:00:00';

        // ── Stavke ───────────────────────────────────────────────────────────
        $stavkeJson = [];
        $pdvGrupe   = []; // stopa => ['osnovica' => ..., 'iznos' => ...]

        foreach ($racun->stavke as $i => $stavka) {
            $kolicina   = (float) $stavka->kolicina;
            $cijena     = (float) $stavka->cijena;
            $rabatP     = (float) ($stavka->rabat_posto ?? 0);
            $pdvStopa   = (float) ($stavka->pdv_stopa ?? 0);

            $bruto     = round($cijena * $kolicina, 2);
            $rabatIzn  = round($bruto * ($rabatP / 100), 2);
            $neto      = round($bruto - $rabatIzn, 2);
            $pdvIznos  = round($neto * ($pdvStopa / 100), 2);

            $katPdv = $pdvStopa > 0 ? 'S' : ($tvrtka->u_sustavu_pdv ? 'Z' : 'E');

            $stavkeJson[] = [
                'nazivArtikla'               => $stavka->naziv,
                'opisArtikla'                => $stavka->opis ?? $stavka->naziv,
                'kolicina'                   => $kolicina,
                'jedinicaMjere'              => static::jedinicaMjereKod($stavka->jedinica_mjere ?? 'kom'),
                'brutoCijenaArtikla'         => $bruto,
                'snizenjeCijeneArtikla'      => $rabatIzn,
                'netoCijenaArtikla'          => $neto,
                'jedinicnaKolicinaCijeneArtikla' => 1,
                'netoIznosStavke'            => $neto,
                'kategorijaPdvStavke'        => $katPdv,
                'stopaPdvStavke'             => number_format($pdvStopa, 2, '.', ''),
                'klasifikacijeArtikla'       => $tvrtka->nkd ? [[
                    'idShemeKlasifikacijeArtikla'        => 'CG',
                    'idKlasifikacijeArtikla'             => $tvrtka->nkd,
                    'verzijaIdShemeKlasifikacijeArtikla' => '2.1.1',
                ]] : null,
                'popustiStavke'              => null,
                'troskoviStavke'             => null,
                'obracunskiPeriodOd'         => $datumIsporuke,
                'obracunskiPeriodDo'         => $datumIsporuke,
                'napomenaStavke'             => null,
            ];

            // Grupiraj PDV
            $kljuc = $pdvStopa . '_' . $katPdv;
            if (! isset($pdvGrupe[$kljuc])) {
                $pdvGrupe[$kljuc] = ['stopa' => $pdvStopa, 'kat' => $katPdv, 'osnovica' => 0, 'iznos' => 0];
            }
            $pdvGrupe[$kljuc]['osnovica'] += $neto;
            $pdvGrupe[$kljuc]['iznos']    += $pdvIznos;
        }

        // ── PDV grupe ────────────────────────────────────────────────────────
        $poreziJson = [];
        foreach ($pdvGrupe as $g) {
            $poreziJson[] = [
                'iznos'           => round($g['iznos'], 2),
                'postotak'        => $g['stopa'],
                'kategorijaPdva'  => $g['kat'],
                'razlog'          => null,
                'osnovicaZaPopust' => 0,
                'schemaPopusta'   => 'VAT',
            ];
        }

        // ── Ukupni iznosi ────────────────────────────────────────────────────
        $ukupnoOsnovica = (float) $racun->ukupno_osnovica;
        $ukupnoRabat    = (float) $racun->ukupno_rabat;
        $ukupnoPdv      = (float) $racun->ukupno_pdv;
        $ukupno         = (float) $racun->ukupno;
        $neto           = round($ukupnoOsnovica - $ukupnoRabat, 2);

        $razlogOslobodenja = in_array(true, array_map(fn($g) => in_array($g['kat'], ['E', 'Z']), $pdvGrupe))
            ? ($racun->napomena ?? null)
            : null;

        $shemaPdv = [];
        foreach ($pdvGrupe as $g) {
            $jeOsloboden = in_array($g['kat'], ['E', 'Z']);
            $shemaPdv[] = [
                'iznosOsnoviceKategorijePdv' => round($g['osnovica'], 2),
                'iznosKategorijePdv'          => round($g['iznos'], 2),
                'sifraKategorijePdv'          => $g['kat'],
                'stopaKategorijePdv'          => $g['stopa'],
                'tekstRazlogaIzuzecaPdv'      => $jeOsloboden ? ($racun->napomena ?? null) : null,
                'sifraRazlogaIzuzecaPdv'      => null,
                'idSheme'                     => 'VAT',
            ];
        }

        return [
            'dokumentPodaci' => [
                'brojRacuna'                          => $racun->broj,
                'datumIzdavanjaRacuna'                => $datumIzdavanja,
                'vrijemeIzdavanja'                    => $vrijemeIzd,
                'sifraValuteRacuna'                   => 'EUR',
                'sifraValuteObracunatogPdva'          => 'EUR',
                'datumDospijecaPlacanja'              => $datumDospijeca,
                'datumPocetkaObracunskogRazdoblja'    => $datumIsporuke,
                'datumZavrsetkaObracunskogRazdoblja'  => $datumIsporuke,
                'sifraDatumaNaKojiPdvPostajeVazeci'   => '3',
                'datumNaKojiPdvPostajeVazeci'         => null,
                'sifraVrsteRacuna'                    => '380',
                'vrstaPoslovnogProcesa'               => 'P5',
                'idSpecifikacije'                     => 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0',
            ],
            'reference' => [
                'referencaNaKupca'         => null,
                'referencaNaProjekt'       => null,
                'referencaNaUgovor'        => null,
                'referencaNaNarudzbenicu'  => null,
                'referencaNaProdajniNalog' => null,
                'referencaNaPrimku'        => null,
                'referencaNaOtpremnicu'    => null,
                'referencaNaZahtjevZaPonudom' => null,
                'mjestoTroska'             => $tvrtka->mjesto ?? null,
            ],
            'sudionici' => [
                'prodavatelj' => [
                    'trgovackiNazivProdavaca'             => $tvrtka->naziv,
                    'idProdavatelja'                      => $tvrtka->oib,
                    'poreznaShema'                        => 'VAT',
                    'oib'                                 => 'HR' . $tvrtka->oib,
                    'ulicaIkucniBroj'                     => $tvrtka->adresa ?? null,
                    'kontaktImePrezime'                   => $tvrtka->vlasnik ?? null,
                    'telefon'                             => $tvrtka->kontakt_broj ?? null,
                    'email'                               => $tvrtka->email ?: null,
                    'elektronickaAdresaProdavatelja'      => $tvrtka->oib,
                    'gradProdavatelja'                    => $tvrtka->mjesto ?? null,
                    'postanskiBrojProdavatelja'           => $tvrtka->po_broj ?? null,
                    'sifraDrzaveProdavatelja'             => 'HR',
                    'nazivProdavatelja'                   => $tvrtka->naziv,
                ],
                'kupac' => [
                    'poreznaShema'          => 'VAT',
                    'oib'                   => $klijent->oib ? 'HR' . $klijent->oib : null,
                    'ulicaIkucniBroj'       => $klijent->adresa ?? null,
                    'kontaktImePrezime'     => $klijent->vlasnik ?? null,
                    'telefon'               => $klijent->kontakt_broj ?? null,
                    'email'                 => $klijent->email ?? null,
                    'imeKupca'              => $klijent->naziv,
                    'trgovackiNazivKupca'   => $klijent->naziv,
                    'elektronickaAdresaKupca' => $klijent->oib ?? $klijent->email,
                    'gradKupca'             => $klijent->mjesto ?? null,
                    'postanskiBrojKupca'    => $klijent->po_broj ?? null,
                    'sifraDrzaveKupca'      => 'HR',
                ],
            ],
            'specifikacija' => [
                'stavkeSpecifikacije'   => $stavkeJson,
                'poreziDokumenta'       => $poreziJson,
                'ukupniIznosiDokumenta' => [
                    'zbrojSvihNetoIznosaStavki'        => $neto,
                    'ukupniIznosRacunaBezPdva'         => $neto,
                    'zbrojPopustaNaRaziniDokumenta'    => 0,
                    'ukupniIznosPdva'                  => $ukupnoPdv,
                    'zbrojTroskovaNaRaziniDokumenta'   => 0,
                    'ukupniIznosRacunaSpdvom'          => $ukupno,
                    'placenIznos'                      => 0,
                    'ukupniIznosPdvaUracValuti'        => $ukupnoPdv,
                    'iznosZaokruzivanja'               => 0,
                    'iznosKojiDospijevaNaPlacanje'     => $ukupno,
                    'shemaPdv'                         => $shemaPdv,
                ],
                'popustiDokumenta' => null,
            ],
            'isporuka' => [
                'sifraDrzaveDostave' => 'HR',
            ],
            'kreditniTransfer' => $tvrtka->iban ? [
                'identifikatorRacunaPlacanja'         => $tvrtka->iban,
                'nazivRacunaZaPlacanje'               => $tvrtka->naziv,
                'identifikatorPruzateljaPlatnihUsluga' => $tvrtka->banka ?? null,
            ] : null,
            'napomene' => $racun->napomena ? [
                ['sifraTekstualneNapomene' => ' ', 'napomena' => $racun->napomena],
            ] : [],
            'podaciZaPlacanje' => [
                'sifraNacinaPlacanja' => '30',
                'tekstNacinaPlacanja' => 'Transakcijski račun',
                'modelPNB'            => null,
                'uvjetiPlacanja'      => $racun->napomena ?? 'Transakcijski račun',
            ],
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    // CIUS-HR 2025 UBL XML builder
    // ────────────────────────────────────────────────────────────────────────

    public static function buildCiusHrXml(Racun $racun): string
    {
        $racun->load(['stavke', 'tvrtka', 'klijent']);
        $tvrtka  = $racun->tvrtka;
        $klijent = $racun->klijent;

        $cbcNs   = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
        $cacNs   = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $extNs   = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
        $hrNs    = 'urn:mfin.gov.hr:schema:xsd:HRExtensionAggregateComponents-1';
        $invoNs  = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';

        // ── Per-line tax accumulation ────────────────────────────────────────
        $pdvGrupe  = [];
        $stavkeData = [];

        foreach ($racun->stavke as $i => $stavka) {
            $kolicina = (float) $stavka->kolicina;
            $cijena   = (float) $stavka->cijena;
            $rabatP   = (float) ($stavka->rabat_posto ?? 0);
            $pdvStopa = (float) ($stavka->pdv_stopa ?? 0);

            $bruto    = round($cijena * $kolicina, 2);
            $rabatIzn = round($bruto * ($rabatP / 100), 2);
            $neto     = round($bruto - $rabatIzn, 2);
            $pdvIznos = round($neto * ($pdvStopa / 100), 2);
            $katPdv   = $pdvStopa > 0 ? 'S' : ($tvrtka->u_sustavu_pdv ? 'Z' : 'E');

            $kljuc = $pdvStopa . '_' . $katPdv;
            if (!isset($pdvGrupe[$kljuc])) {
                $pdvGrupe[$kljuc] = ['stopa' => $pdvStopa, 'kat' => $katPdv, 'osnovica' => 0.0, 'iznos' => 0.0];
            }
            $pdvGrupe[$kljuc]['osnovica'] += $neto;
            $pdvGrupe[$kljuc]['iznos']    += $pdvIznos;

            $stavkeData[] = compact('i', 'stavka', 'kolicina', 'cijena', 'rabatIzn', 'neto', 'pdvIznos', 'katPdv', 'pdvStopa');
        }

        $ukupnoPdv = (float) $racun->ukupno_pdv;
        $ukupno    = (float) $racun->ukupno;
        $ukupnoOsn = (float) $racun->ukupno_osnovica;
        $ukupnoRab = (float) $racun->ukupno_rabat;
        $netoUkupno = round($ukupnoOsn - $ukupnoRab, 2);

        $outOfScopeAmount = 0.0;
        foreach ($pdvGrupe as $g) {
            if ($g['kat'] === 'O') {
                $outOfScopeAmount += $g['osnovica'];
            }
        }

        $vrijemeIzd = $racun->vrijeme_izdavanja
            ? (is_string($racun->vrijeme_izdavanja) ? $racun->vrijeme_izdavanja : $racun->vrijeme_izdavanja->format('H:i:s'))
            : '00:00:00';

        // ── DOM ──────────────────────────────────────────────────────────────
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $invoice = $dom->createElementNS($invoNs, 'Invoice');
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', $cacNs);
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', $cbcNs);
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', $extNs);
        $invoice->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:hrextac', $hrNs);
        $dom->appendChild($invoice);

        // ── UBLExtensions (HRFISK20Data) ─────────────────────────────────────
        $ublExtensions = $dom->createElementNS($extNs, 'ext:UBLExtensions');
        $ublExtension  = $dom->createElementNS($extNs, 'ext:UBLExtension');
        $extContent    = $dom->createElementNS($extNs, 'ext:ExtensionContent');
        $hrFisk        = $dom->createElementNS($hrNs,  'hrextac:HRFISK20Data');

        // HRTaxTotal
        $hrTaxTotal  = $dom->createElementNS($hrNs, 'hrextac:HRTaxTotal');
        $taxTotalAmt = $dom->createElementNS($cbcNs, 'cbc:TaxAmount', number_format($ukupnoPdv, 2, '.', ''));
        $taxTotalAmt->setAttribute('currencyID', 'EUR');
        $hrTaxTotal->appendChild($taxTotalAmt);

        foreach ($pdvGrupe as $g) {
            $hrSub = $dom->createElementNS($hrNs, 'hrextac:HRTaxSubtotal');

            $subTaxable = $dom->createElementNS($cbcNs, 'cbc:TaxableAmount', number_format(round($g['osnovica'], 2), 2, '.', ''));
            $subTaxable->setAttribute('currencyID', 'EUR');
            $hrSub->appendChild($subTaxable);

            $subTaxAmt = $dom->createElementNS($cbcNs, 'cbc:TaxAmount', number_format(round($g['iznos'], 2), 2, '.', ''));
            $subTaxAmt->setAttribute('currencyID', 'EUR');
            $hrSub->appendChild($subTaxAmt);

            $hrTaxCat = $dom->createElementNS($hrNs, 'hrextac:HRTaxCategory');
            $hrTaxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $g['kat']));
            $hrTaxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', static::hrTaxCategoryName($g['kat'], $g['stopa'])));
            $hrTaxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:Percent', (string) (int) $g['stopa']));
            $hrScheme = $dom->createElementNS($hrNs, 'hrextac:HRTaxScheme');
            $hrScheme->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', 'VAT'));
            $hrTaxCat->appendChild($hrScheme);
            $hrSub->appendChild($hrTaxCat);

            $hrTaxTotal->appendChild($hrSub);
        }

        $hrFisk->appendChild($hrTaxTotal);

        // HRLegalMonetaryTotal
        $hrLegal = $dom->createElementNS($hrNs, 'hrextac:HRLegalMonetaryTotal');
        $hrTaxExcl = $dom->createElementNS($cbcNs, 'cbc:TaxExclusiveAmount', number_format($netoUkupno, 2, '.', ''));
        $hrTaxExcl->setAttribute('currencyID', 'EUR');
        $hrLegal->appendChild($hrTaxExcl);
        $hrOutOfScope = $dom->createElementNS($hrNs, 'hrextac:OutOfScopeOfVATAmount', number_format($outOfScopeAmount, 2, '.', ''));
        $hrOutOfScope->setAttribute('currencyID', 'EUR');
        $hrLegal->appendChild($hrOutOfScope);
        $hrFisk->appendChild($hrLegal);

        $extContent->appendChild($hrFisk);
        $ublExtension->appendChild($extContent);
        $ublExtensions->appendChild($ublExtension);
        $invoice->appendChild($ublExtensions);

        // ── Invoice header ────────────────────────────────────────────────────
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.hr:cius-2025:1.0#conformant#urn:mfin.gov.hr:ext-2025:1.0'));
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:ProfileID', 'P1'));
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $racun->broj));
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:IssueDate', $racun->datum_izdavanja->format('Y-m-d')));
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:IssueTime', $vrijemeIzd));
        if ($racun->datum_dospijeca) {
            $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:DueDate', $racun->datum_dospijeca->format('Y-m-d')));
        }
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:InvoiceTypeCode', '380'));
        if ($racun->napomena) {
            $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:Note', $racun->napomena));
        }
        $invoice->appendChild($dom->createElementNS($cbcNs, 'cbc:DocumentCurrencyCode', 'EUR'));

        // ── AccountingSupplierParty ───────────────────────────────────────────
        $supplierParty = $dom->createElementNS($cacNs, 'cac:AccountingSupplierParty');
        $supplierPartyEl = $dom->createElementNS($cacNs, 'cac:Party');

        $supplierEndpoint = $dom->createElementNS($cbcNs, 'cbc:EndpointID', $tvrtka->oib);
        $supplierEndpoint->setAttribute('schemeID', '9934');
        $supplierPartyEl->appendChild($supplierEndpoint);

        $supplierPartyName = $dom->createElementNS($cacNs, 'cac:PartyName');
        $supplierPartyName->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', $tvrtka->naziv));
        $supplierPartyEl->appendChild($supplierPartyName);

        $supplierAddr = $dom->createElementNS($cacNs, 'cac:PostalAddress');
        if ($tvrtka->adresa) {
            $supplierAddr->appendChild($dom->createElementNS($cbcNs, 'cbc:StreetName', $tvrtka->adresa));
        }
        if ($tvrtka->po_broj) {
            $supplierAddr->appendChild($dom->createElementNS($cbcNs, 'cbc:PostalZone', $tvrtka->po_broj));
        }
        if ($tvrtka->mjesto) {
            $supplierAddr->appendChild($dom->createElementNS($cbcNs, 'cbc:CityName', $tvrtka->mjesto));
        }
        $supplierCountry = $dom->createElementNS($cacNs, 'cac:Country');
        $supplierCountry->appendChild($dom->createElementNS($cbcNs, 'cbc:IdentificationCode', 'HR'));
        $supplierAddr->appendChild($supplierCountry);
        $supplierPartyEl->appendChild($supplierAddr);

        $supplierTaxScheme = $dom->createElementNS($cacNs, 'cac:PartyTaxScheme');
        $supplierTaxScheme->appendChild($dom->createElementNS($cbcNs, 'cbc:CompanyID', 'HR' . $tvrtka->oib));
        $supplierTaxSchemeEl = $dom->createElementNS($cacNs, 'cac:TaxScheme');
        $supplierTaxSchemeEl->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', 'VAT'));
        $supplierTaxScheme->appendChild($supplierTaxSchemeEl);
        $supplierPartyEl->appendChild($supplierTaxScheme);

        $supplierLegal = $dom->createElementNS($cacNs, 'cac:PartyLegalEntity');
        $supplierLegal->appendChild($dom->createElementNS($cbcNs, 'cbc:RegistrationName', $tvrtka->naziv));
        $supplierLegal->appendChild($dom->createElementNS($cbcNs, 'cbc:CompanyID', $tvrtka->oib));
        $supplierPartyEl->appendChild($supplierLegal);

        if ($tvrtka->vlasnik || $tvrtka->email || $tvrtka->kontakt_broj) {
            $supplierContact = $dom->createElementNS($cacNs, 'cac:Contact');
            if ($tvrtka->vlasnik) {
                $supplierContact->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', $tvrtka->vlasnik));
            }
            if ($tvrtka->kontakt_broj) {
                $supplierContact->appendChild($dom->createElementNS($cbcNs, 'cbc:Telephone', $tvrtka->kontakt_broj));
            }
            if ($tvrtka->email) {
                $supplierContact->appendChild($dom->createElementNS($cbcNs, 'cbc:ElectronicMail', $tvrtka->email));
            }
            $supplierPartyEl->appendChild($supplierContact);
        }

        $supplierParty->appendChild($supplierPartyEl);

        // SellerContact — operator (HR-BT-4/HR-BT-5)
        if ($tvrtka->oib_operatera || $tvrtka->vlasnik) {
            $sellerContact = $dom->createElementNS($cacNs, 'cac:SellerContact');
            if ($tvrtka->oib_operatera) {
                $sellerContact->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $tvrtka->oib_operatera));
            }
            if ($tvrtka->vlasnik) {
                $sellerContact->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', $tvrtka->vlasnik));
            }
            $supplierParty->appendChild($sellerContact);
        }

        $invoice->appendChild($supplierParty);

        // ── AccountingCustomerParty ───────────────────────────────────────────
        $customerParty   = $dom->createElementNS($cacNs, 'cac:AccountingCustomerParty');
        $customerPartyEl = $dom->createElementNS($cacNs, 'cac:Party');

        $customerEndpoint = $dom->createElementNS($cbcNs, 'cbc:EndpointID', $klijent->oib ?? ($klijent->email ?? ''));
        $customerEndpoint->setAttribute('schemeID', $klijent->oib ? '9934' : '0184');
        $customerPartyEl->appendChild($customerEndpoint);

        $customerPartyName = $dom->createElementNS($cacNs, 'cac:PartyName');
        $customerPartyName->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', $klijent->naziv));
        $customerPartyEl->appendChild($customerPartyName);

        $customerAddr = $dom->createElementNS($cacNs, 'cac:PostalAddress');
        if ($klijent->adresa) {
            $customerAddr->appendChild($dom->createElementNS($cbcNs, 'cbc:StreetName', $klijent->adresa));
        }
        if ($klijent->po_broj) {
            $customerAddr->appendChild($dom->createElementNS($cbcNs, 'cbc:PostalZone', $klijent->po_broj));
        }
        if ($klijent->mjesto) {
            $customerAddr->appendChild($dom->createElementNS($cbcNs, 'cbc:CityName', $klijent->mjesto));
        }
        $customerCountry = $dom->createElementNS($cacNs, 'cac:Country');
        $customerCountry->appendChild($dom->createElementNS($cbcNs, 'cbc:IdentificationCode', 'HR'));
        $customerAddr->appendChild($customerCountry);
        $customerPartyEl->appendChild($customerAddr);

        if ($klijent->oib) {
            $customerTaxScheme = $dom->createElementNS($cacNs, 'cac:PartyTaxScheme');
            $customerTaxScheme->appendChild($dom->createElementNS($cbcNs, 'cbc:CompanyID', 'HR' . $klijent->oib));
            $customerTaxSchemeEl = $dom->createElementNS($cacNs, 'cac:TaxScheme');
            $customerTaxSchemeEl->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', 'VAT'));
            $customerTaxScheme->appendChild($customerTaxSchemeEl);
            $customerPartyEl->appendChild($customerTaxScheme);
        }

        $customerLegal = $dom->createElementNS($cacNs, 'cac:PartyLegalEntity');
        $customerLegal->appendChild($dom->createElementNS($cbcNs, 'cbc:RegistrationName', $klijent->naziv));
        if ($klijent->oib) {
            $customerLegal->appendChild($dom->createElementNS($cbcNs, 'cbc:CompanyID', $klijent->oib));
        }
        $customerPartyEl->appendChild($customerLegal);

        $customerParty->appendChild($customerPartyEl);
        $invoice->appendChild($customerParty);

        // ── PaymentMeans ──────────────────────────────────────────────────────
        $paymentMeans = $dom->createElementNS($cacNs, 'cac:PaymentMeans');
        $paymentMeans->appendChild($dom->createElementNS($cbcNs, 'cbc:PaymentMeansCode', '30'));
        $paymentMeans->appendChild($dom->createElementNS($cbcNs, 'cbc:PaymentID', 'HR00 ' . $racun->broj));
        if ($tvrtka->iban) {
            $payeeAccount = $dom->createElementNS($cacNs, 'cac:PayeeFinancialAccount');
            $payeeAccount->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $tvrtka->iban));
            if ($tvrtka->naziv) {
                $payeeAccount->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', $tvrtka->naziv));
            }
            if ($tvrtka->banka) {
                $finBranch = $dom->createElementNS($cacNs, 'cac:FinancialInstitutionBranch');
                $finBranch->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $tvrtka->banka));
                $payeeAccount->appendChild($finBranch);
            }
            $paymentMeans->appendChild($payeeAccount);
        }
        $invoice->appendChild($paymentMeans);

        // ── TaxTotal ─────────────────────────────────────────────────────────
        $taxTotal = $dom->createElementNS($cacNs, 'cac:TaxTotal');
        $taxTotalAmtEl = $dom->createElementNS($cbcNs, 'cbc:TaxAmount', number_format($ukupnoPdv, 2, '.', ''));
        $taxTotalAmtEl->setAttribute('currencyID', 'EUR');
        $taxTotal->appendChild($taxTotalAmtEl);

        foreach ($pdvGrupe as $g) {
            $taxSubtotal = $dom->createElementNS($cacNs, 'cac:TaxSubtotal');

            $subTaxable2 = $dom->createElementNS($cbcNs, 'cbc:TaxableAmount', number_format(round($g['osnovica'], 2), 2, '.', ''));
            $subTaxable2->setAttribute('currencyID', 'EUR');
            $taxSubtotal->appendChild($subTaxable2);

            $subTaxAmt2 = $dom->createElementNS($cbcNs, 'cbc:TaxAmount', number_format(round($g['iznos'], 2), 2, '.', ''));
            $subTaxAmt2->setAttribute('currencyID', 'EUR');
            $taxSubtotal->appendChild($subTaxAmt2);

            $taxCat = $dom->createElementNS($cacNs, 'cac:TaxCategory');
            $taxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $g['kat']));
            $taxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:Percent', number_format($g['stopa'], 2, '.', '')));
            if (in_array($g['kat'], ['E', 'Z'])) {
                $exemptText = $racun->napomena ?? 'Nije u sustavu PDV-a';
                $taxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:TaxExemptionReason', $exemptText));
            }
            $taxCatScheme = $dom->createElementNS($cacNs, 'cac:TaxScheme');
            $taxCatScheme->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', 'VAT'));
            $taxCat->appendChild($taxCatScheme);
            $taxSubtotal->appendChild($taxCat);

            $taxTotal->appendChild($taxSubtotal);
        }

        $invoice->appendChild($taxTotal);

        // ── LegalMonetaryTotal ────────────────────────────────────────────────
        $legalMonetary = $dom->createElementNS($cacNs, 'cac:LegalMonetaryTotal');

        $lineExtAmtEl = $dom->createElementNS($cbcNs, 'cbc:LineExtensionAmount', number_format($netoUkupno, 2, '.', ''));
        $lineExtAmtEl->setAttribute('currencyID', 'EUR');
        $legalMonetary->appendChild($lineExtAmtEl);

        $taxExclAmtEl = $dom->createElementNS($cbcNs, 'cbc:TaxExclusiveAmount', number_format($netoUkupno, 2, '.', ''));
        $taxExclAmtEl->setAttribute('currencyID', 'EUR');
        $legalMonetary->appendChild($taxExclAmtEl);

        $taxInclAmtEl = $dom->createElementNS($cbcNs, 'cbc:TaxInclusiveAmount', number_format($ukupno, 2, '.', ''));
        $taxInclAmtEl->setAttribute('currencyID', 'EUR');
        $legalMonetary->appendChild($taxInclAmtEl);

        $payableAmtEl = $dom->createElementNS($cbcNs, 'cbc:PayableAmount', number_format($ukupno, 2, '.', ''));
        $payableAmtEl->setAttribute('currencyID', 'EUR');
        $legalMonetary->appendChild($payableAmtEl);

        $invoice->appendChild($legalMonetary);

        // ── InvoiceLines ──────────────────────────────────────────────────────
        foreach ($stavkeData as $sd) {
            $stavka = $sd['stavka'];
            $unitCode = static::jedinicaMjereKod($stavka->jedinica_mjere ?? 'kom');

            $line = $dom->createElementNS($cacNs, 'cac:InvoiceLine');
            $line->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', (string) ($sd['i'] + 1)));

            $lineQtyEl = $dom->createElementNS($cbcNs, 'cbc:InvoicedQuantity', number_format($sd['kolicina'], 3, '.', ''));
            $lineQtyEl->setAttribute('unitCode', $unitCode);
            $line->appendChild($lineQtyEl);

            $lineExtAmtLineEl = $dom->createElementNS($cbcNs, 'cbc:LineExtensionAmount', number_format($sd['neto'], 2, '.', ''));
            $lineExtAmtLineEl->setAttribute('currencyID', 'EUR');
            $line->appendChild($lineExtAmtLineEl);

            $item = $dom->createElementNS($cacNs, 'cac:Item');
            if ($stavka->opis) {
                $item->appendChild($dom->createElementNS($cbcNs, 'cbc:Description', $stavka->opis));
            }
            $item->appendChild($dom->createElementNS($cbcNs, 'cbc:Name', $stavka->naziv));

            if ($tvrtka->nkd) {
                $commClass = $dom->createElementNS($cacNs, 'cac:CommodityClassification');
                $classCode = $dom->createElementNS($cbcNs, 'cbc:ItemClassificationCode', $tvrtka->nkd);
                $classCode->setAttribute('listID', 'CG');
                $commClass->appendChild($classCode);
                $item->appendChild($commClass);
            }

            $classifiedTaxCat = $dom->createElementNS($cacNs, 'cac:ClassifiedTaxCategory');
            $classifiedTaxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', $sd['katPdv']));
            $classifiedTaxCat->appendChild($dom->createElementNS($cbcNs, 'cbc:Percent', number_format($sd['pdvStopa'], 2, '.', '')));
            $classifiedTaxScheme = $dom->createElementNS($cacNs, 'cac:TaxScheme');
            $classifiedTaxScheme->appendChild($dom->createElementNS($cbcNs, 'cbc:ID', 'VAT'));
            $classifiedTaxCat->appendChild($classifiedTaxScheme);
            $item->appendChild($classifiedTaxCat);

            $line->appendChild($item);

            $price = $dom->createElementNS($cacNs, 'cac:Price');
            $priceAmtEl = $dom->createElementNS($cbcNs, 'cbc:PriceAmount', number_format($sd['cijena'], 6, '.', ''));
            $priceAmtEl->setAttribute('currencyID', 'EUR');
            $price->appendChild($priceAmtEl);
            $baseQtyEl = $dom->createElementNS($cbcNs, 'cbc:BaseQuantity', '1.000');
            $baseQtyEl->setAttribute('unitCode', $unitCode);
            $price->appendChild($baseQtyEl);
            $line->appendChild($price);

            $invoice->appendChild($line);
        }

        return $dom->saveXML();
    }

    private static function hrTaxCategoryName(string $kat, float $stopa): string
    {
        if ($kat === 'S') {
            return match ((int) $stopa) {
                25      => 'HR:PDV25',
                13      => 'HR:PDV13',
                5       => 'HR:PDV5',
                default => 'HR:PDV' . (int) $stopa,
            };
        }
        return match ($kat) {
            'O'     => 'HR:PP',
            'E'     => 'HR:OSL',
            'Z'     => 'HR:NUL',
            default => 'HR:PDV0',
        };
    }

    private static function jedinicaMjereKod(string $jm): string
    {
        return match (strtolower(trim($jm))) {
            'kom', 'kos', 'pc', 'pcs', 'komad' => 'H87', // piece
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
            default                             => 'H87', // fallback: piece
        };
    }
}
