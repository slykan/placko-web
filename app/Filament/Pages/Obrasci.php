<?php

namespace App\Filament\Pages;

use App\Models\Racun;
use App\Models\Tvrtka;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;

class Obrasci extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Obrasci';

    protected static ?string $title = 'Porezni obrasci';

    protected static ?int $navigationSort = 98;

    protected static string $view = 'filament.pages.obrasci';

    public Tvrtka $tvrtka;
    public string $vrsta;
    public array  $dostupniObrasci = [];
    public array  $godine          = [];

    public function mount(): void
    {
        $this->tvrtka = filament()->getTenant();
        $this->vrsta  = $this->tvrtka->vrsta_poslovanja ?? 'pausalni_obrt';

        $this->godine = Racun::where('tvrtka_id', $this->tvrtka->id)
            ->distinct()
            ->orderByDesc('godina')
            ->pluck('godina')
            ->toArray();

        if (empty($this->godine)) {
            $this->godine = [now()->year];
        }

        $this->dostupniObrasci = static::obrасciZaVrstu($this->vrsta);
    }

    public function preuzmiPoSd(int $godina): mixed
    {
        $racuni = Racun::where('tvrtka_id', $this->tvrtka->id)
            ->where('godina', $godina)
            ->get();

        $ukupniPrimitci   = round((float) $racuni->sum('ukupno'), 2);
        $pausalni_izdatci = round(min($ukupniPrimitci * 0.30, 12750.00), 2);
        $dohodak          = round($ukupniPrimitci - $pausalni_izdatci, 2);
        $godisnjiOdbitak  = 6720.00;
        $poreznaOsnovica  = max(0, round($dohodak - $godisnjiOdbitak, 2));

        if ($poreznaOsnovica <= 50400) {
            $porezNaDohodak = round($poreznaOsnovica * 0.20, 2);
        } else {
            $porezNaDohodak = round(50400 * 0.20 + ($poreznaOsnovica - 50400) * 0.30, 2);
        }

        $racuniPoMjesecu = [];
        for ($m = 1; $m <= 12; $m++) {
            $mj = $racuni->filter(fn ($r) => $r->datum_izdavanja->month === $m);
            $racuniPoMjesecu[$m] = ['broj' => $mj->count(), 'iznos' => round((float) $mj->sum('ukupno'), 2)];
        }

        $pdf = Pdf::loadView('pdf.po-sd', compact(
            'ukupniPrimitci', 'pausalni_izdatci', 'dohodak',
            'poreznaOsnovica', 'porezNaDohodak', 'racuniPoMjesecu', 'godina'
        ) + ['tvrtka' => $this->tvrtka, 'brojRacuna' => $racuni->count()])
            ->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'PO-SD_' . $this->tvrtka->oib . '_' . $godina . '.pdf'
        );
    }

    public function preuzmiIra(int $godina): mixed
    {
        $racuni = Racun::with(['klijent'])
            ->where('tvrtka_id', $this->tvrtka->id)
            ->where('godina', $godina)
            ->orderBy('redni_broj')
            ->get();

        $pdf = Pdf::loadView('pdf.ira-pregled', [
            'tvrtka'         => $this->tvrtka,
            'godina'         => $godina,
            'racuni'         => $racuni,
            'ukupnoOsnovica' => round((float) $racuni->sum('ukupno_osnovica'), 2),
            'ukupnoRabat'    => round((float) $racuni->sum('ukupno_rabat'), 2),
            'ukupnoPdv'      => round((float) $racuni->sum('ukupno_pdv'), 2),
            'ukupno'         => round((float) $racuni->sum('ukupno'), 2),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'IRA_' . $this->tvrtka->oib . '_' . $godina . '.pdf'
        );
    }

    public static function obrасciZaVrstu(string $vrsta): array
    {
        $svi = [
            'po_sd' => [
                'naziv' => 'PO-SD',
                'opis'  => 'Godišnja prijava paušalnog dohotka od samostalne djelatnosti',
                'rok'   => 'do kraja veljače za prethodnu godinu',
                'boja'  => 'blue',
                'ikona' => 'heroicon-o-calculator',
                'vrste' => ['pausalni_obrt'],
                'akcija' => 'po_sd',
            ],
            'doh' => [
                'naziv' => 'DOH',
                'opis'  => 'Godišnja prijava poreza na dohodak',
                'rok'   => 'do kraja veljače za prethodnu godinu',
                'boja'  => 'blue',
                'ikona' => 'heroicon-o-calculator',
                'vrste' => ['obrt'],
                'akcija' => null,
            ],
            'pd' => [
                'naziv' => 'PD',
                'opis'  => 'Prijava poreza na dobit',
                'rok'   => 'do 30. travnja za prethodnu godinu',
                'boja'  => 'purple',
                'ikona' => 'heroicon-o-building-office-2',
                'vrste' => ['doo', 'jdoo', 'dd', 'jtd', 'kd'],
                'akcija' => null,
            ],
            'pdv' => [
                'naziv' => 'PDV',
                'opis'  => 'Prijava poreza na dodanu vrijednost (za obveznike PDV-a)',
                'rok'   => 'do 20. u mjesecu za prethodni period',
                'boja'  => 'orange',
                'ikona' => 'heroicon-o-receipt-percent',
                'vrste' => ['obrt', 'doo', 'jdoo', 'dd', 'jtd', 'kd'],
                'akcija' => null,
            ],
            'joppd' => [
                'naziv' => 'JOPPD',
                'opis'  => 'Izvješće o primicima, porezu na dohodak i prirezu te doprinosima',
                'rok'   => 'do 15. u mjesecu za isplaćene primitke',
                'boja'  => 'green',
                'ikona' => 'heroicon-o-users',
                'vrste' => ['obrt', 'doo', 'jdoo', 'dd', 'jtd', 'kd'],
                'akcija' => null,
            ],
            'ura' => [
                'naziv' => 'IRA — Knjiga izlaznih računa',
                'opis'  => 'Pregled svih izdanih računa za godinu s ukupnim iznosima',
                'rok'   => 'godišnje',
                'boja'  => 'gray',
                'ikona' => 'heroicon-o-book-open',
                'vrste' => ['obrt', 'doo', 'jdoo', 'dd', 'jtd', 'kd', 'pausalni_obrt'],
                'akcija' => 'ira',
            ],
        ];

        return array_filter($svi, fn ($o) => in_array($vrsta, $o['vrste']));
    }
}
