<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ponuda extends Model
{
    protected $table = 'ponude';

    protected $fillable = [
        'tvrtka_id', 'klijent_id', 'broj', 'redni_broj', 'godina',
        'datum_izdavanja', 'vrijeme_izdavanja', 'mjesto_izdavanja',
        'valjanost_dana', 'rok_ispostave', 'napomena',
        'ukupno_osnovica', 'ukupno_rabat', 'ukupno_pdv', 'ukupno',
    ];

    protected $casts = ['datum_izdavanja' => 'date'];

    public static function generiraBroj(int $tvrtkaId): array
    {
        $godina = now()->year;
        $redni = (static::where('tvrtka_id', $tvrtkaId)
            ->where('godina', $godina)->max('redni_broj') ?? 0) + 1;

        return ['redni_broj' => $redni, 'godina' => $godina, 'broj' => "{$redni}-1-{$godina}"];
    }

    public function izracunajUkupno(): void
    {
        $osnovica = $rabat = $pdv = 0;
        foreach ($this->stavke as $stavka) {
            $bruto = (float) $stavka->cijena * (float) $stavka->kolicina;
            $rabatIznos = $bruto * ((float) $stavka->rabat_posto / 100);
            $neto = $bruto - $rabatIznos;
            $osnovica += $bruto;
            $rabat += $rabatIznos;
            $pdv += $neto * ((float) ($stavka->pdv_stopa ?? 0) / 100);
        }
        $this->update([
            'ukupno_osnovica' => round($osnovica, 2),
            'ukupno_rabat' => round($rabat, 2),
            'ukupno_pdv' => round($pdv, 2),
            'ukupno' => round($osnovica - $rabat + $pdv, 2),
        ]);
    }

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function klijent(): BelongsTo
    {
        return $this->belongsTo(Klijent::class);
    }

    public function stavke(): HasMany
    {
        return $this->hasMany(PonudaStavka::class)->orderBy('redni_broj');
    }
}
