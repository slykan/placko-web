<?php

namespace App\Services;

use App\Models\Racun;
use DateTime;
use Einvoicing\AllowanceOrCharge;
use Einvoicing\Identifier;
use Einvoicing\Invoice;
use Einvoicing\InvoiceLine;
use Einvoicing\Party;
use Einvoicing\Payments\Transfer;
use Einvoicing\Presets\Peppol;
use Einvoicing\Writers\UblWriter;

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
