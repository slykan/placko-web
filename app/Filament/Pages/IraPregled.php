<?php

namespace App\Filament\Pages;

use App\Models\Racun;
use App\Models\Tvrtka;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;

class IraPregled extends Page
{
    protected static ?string $navigationIcon  = null;
    protected static bool    $shouldRegisterNavigation = false;
    protected static string  $view = 'filament.pages.ira-pregled';

    public Tvrtka $tvrtka;
    public int    $godina;
    public        $racuni;
    public float  $ukupnoOsnovica;
    public float  $ukupnoRabat;
    public float  $ukupnoPdv;
    public float  $ukupno;

    public function mount(int $godina = 0): void
    {
        $this->tvrtka = filament()->getTenant();
        $this->godina = $godina ?: now()->year;

        $this->racuni = Racun::with(['klijent', 'stavke'])
            ->where('tvrtka_id', $this->tvrtka->id)
            ->where('godina', $this->godina)
            ->orderBy('redni_broj')
            ->get();

        $this->ukupnoOsnovica = round((float) $this->racuni->sum('ukupno_osnovica'), 2);
        $this->ukupnoRabat    = round((float) $this->racuni->sum('ukupno_rabat'), 2);
        $this->ukupnoPdv      = round((float) $this->racuni->sum('ukupno_pdv'), 2);
        $this->ukupno         = round((float) $this->racuni->sum('ukupno'), 2);
    }

    public function preuzmiPdf(): mixed
    {
        $pdf = Pdf::loadView('pdf.ira-pregled', [
            'tvrtka'          => $this->tvrtka,
            'godina'          => $this->godina,
            'racuni'          => $this->racuni,
            'ukupnoOsnovica'  => $this->ukupnoOsnovica,
            'ukupnoRabat'     => $this->ukupnoRabat,
            'ukupnoPdv'       => $this->ukupnoPdv,
            'ukupno'          => $this->ukupno,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'IRA_' . $this->tvrtka->oib . '_' . $this->godina . '.pdf'
        );
    }
}
