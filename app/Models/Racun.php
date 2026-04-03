<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Racun extends Model
{
    protected $table = 'racuni';

    protected $fillable = [
        'tvrtka_id',
        'klijent_id',
        'broj',
        'redni_broj',
        'godina',
        'datum_izdavanja',
        'vrijeme_izdavanja',
        'datum_dospijeca',
        'datum_isporuke',
        'mjesto_izdavanja',
        'nacin_placanja',
        'napomena',
        'status',
        'placen_at',
        'arhiviran_at',
        'zki',
        'jir',
        'fiskaliziran_at',
        'ukupno_osnovica',
        'ukupno_rabat',
        'ukupno_pdv',
        'ukupno',
    ];

    protected $casts = [
        'datum_izdavanja' => 'date',
        'datum_dospijeca' => 'date',
        'datum_isporuke'  => 'date',
        'placen_at'        => 'datetime',
        'arhiviran_at'     => 'datetime',
        'fiskaliziran_at'  => 'datetime',
    ];

    public static function generiraBroj(int $tvrtkaId): array
    {
        $godina = now()->year;
        $zadnji = static::where('tvrtka_id', $tvrtkaId)
            ->where('godina', $godina)
            ->max('redni_broj') ?? 0;

        $redni = $zadnji + 1;

        return [
            'redni_broj' => $redni,
            'godina'     => $godina,
            'broj'       => "{$redni}-1-{$godina}",
        ];
    }

    public function izracunajUkupno(): void
    {
        $osnovica = 0;
        $rabat    = 0;
        $pdv      = 0;

        foreach ($this->stavke as $stavka) {
            $brutoStavka = (float) $stavka->cijena * (float) $stavka->kolicina;
            $rabatIznos  = $brutoStavka * ((float) $stavka->rabat_posto / 100);
            $neto        = $brutoStavka - $rabatIznos;
            $pdvIznos    = $neto * (((float) ($stavka->pdv_stopa ?? 0)) / 100);

            $osnovica += $brutoStavka;
            $rabat    += $rabatIznos;
            $pdv      += $pdvIznos;
        }

        $this->update([
            'ukupno_osnovica' => round($osnovica, 2),
            'ukupno_rabat'    => round($rabat, 2),
            'ukupno_pdv'      => round($pdv, 2),
            'ukupno'          => round($osnovica - $rabat + $pdv, 2),
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
        return $this->hasMany(RacunStavka::class)->orderBy('redni_broj');
    }
}
