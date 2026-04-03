<?php

namespace App\Filament\Pages;

use App\Models\Racun;
use App\Models\Tvrtka;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;

class PoSd extends Page
{
    protected static ?string $navigationIcon  = null;
    protected static bool    $shouldRegisterNavigation = false;
    protected static string  $view = 'filament.pages.po-sd';

    public Tvrtka $tvrtka;
    public int    $godina;
    public float  $ukupniPrimitci;
    public float  $pausalni_izdatci;
    public float  $dohodak;
    public float  $osobniOdbitak     = 560.00; // mjesecni, godisnji = 6720
    public float  $poreznaOsnovica;
    public float  $porezNaDohodak;
    public int    $brojRacuna;
    public array  $racuniPoMjesecu;

    public function mount(int $godina = 0): void
    {
        $this->tvrtka = filament()->getTenant();
        $this->godina = $godina ?: now()->year - 1;

        $racuni = Racun::where('tvrtka_id', $this->tvrtka->id)
            ->where('godina', $this->godina)
            ->get();

        $this->brojRacuna     = $racuni->count();
        $this->ukupniPrimitci = round((float) $racuni->sum('ukupno'), 2);

        // Paušalni izdatci = 30% primitaka (max 12.750 EUR za 2024+)
        $this->pausalni_izdatci = round(min($this->ukupniPrimitci * 0.30, 12750.00), 2);
        $this->dohodak          = round($this->ukupniPrimitci - $this->pausalni_izdatci, 2);

        // Godišnji osobni odbitak
        $godisnjiOdbitak      = $this->osobniOdbitak * 12;
        $this->poreznaOsnovica = max(0, round($this->dohodak - $godisnjiOdbitak, 2));

        // Porez: 20% do 50.400 EUR, 30% iznad
        if ($this->poreznaOsnovica <= 50400) {
            $this->porezNaDohodak = round($this->poreznaOsnovica * 0.20, 2);
        } else {
            $this->porezNaDohodak = round(50400 * 0.20 + ($this->poreznaOsnovica - 50400) * 0.30, 2);
        }

        // Računi po mjesecu
        $this->racuniPoMjesecu = [];
        for ($m = 1; $m <= 12; $m++) {
            $mj = $racuni->filter(fn ($r) => $r->datum_izdavanja->month === $m);
            $this->racuniPoMjesecu[$m] = [
                'broj'   => $mj->count(),
                'iznos'  => round((float) $mj->sum('ukupno'), 2),
            ];
        }
    }

    public function preuzmiPdf(): mixed
    {
        $pdf = Pdf::loadView('pdf.po-sd', [
            'tvrtka'            => $this->tvrtka,
            'godina'            => $this->godina,
            'ukupniPrimitci'    => $this->ukupniPrimitci,
            'pausalni_izdatci'  => $this->pausalni_izdatci,
            'dohodak'           => $this->dohodak,
            'poreznaOsnovica'   => $this->poreznaOsnovica,
            'porezNaDohodak'    => $this->porezNaDohodak,
            'brojRacuna'        => $this->brojRacuna,
            'racuniPoMjesecu'   => $this->racuniPoMjesecu,
        ])->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'PO-SD_' . $this->tvrtka->oib . '_' . $this->godina . '.pdf'
        );
    }
}
