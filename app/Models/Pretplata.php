<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pretplata extends Model
{
    protected $table = 'pretplate';

    protected $fillable = [
        'tvrtka_id',
        'klijent_id',
        'period',
        'datum_pocetka',
        'datum_isteka',
        'status',
        'opis',
        'ukupno',
    ];

    protected $casts = [
        'datum_pocetka' => 'date',
        'datum_isteka'  => 'date',
        'ukupno'        => 'decimal:2',
    ];

    public static function periodi(): array
    {
        return [
            'mjesecno'      => 'Mjesečno',
            'tromjesecno'   => 'Tromjesečno',
            'polugodisnje'  => 'Polugodišnje',
            'godisnje'      => 'Godišnje',
        ];
    }

    public function sljedeciDatum(): \Carbon\Carbon
    {
        return match ($this->period) {
            'mjesecno'     => $this->datum_isteka->addMonth(),
            'tromjesecno'  => $this->datum_isteka->addMonths(3),
            'polugodisnje' => $this->datum_isteka->addMonths(6),
            'godisnje'     => $this->datum_isteka->addYear(),
            default        => $this->datum_isteka->addYear(),
        };
    }

    public function izracunajUkupno(): void
    {
        $ukupno = $this->stavke->sum(function ($stavka) {
            $neto = (float) $stavka->cijena * (float) $stavka->kolicina;
            $pdv  = $neto * (((float) ($stavka->pdv_stopa ?? 0)) / 100);
            return $neto + $pdv;
        });

        $this->update(['ukupno' => round($ukupno, 2)]);
    }

    public function renew(): self
    {
        $nova = $this->replicate();
        $nova->datum_pocetka = $this->datum_isteka->addDay();
        $nova->datum_isteka  = $this->sljedeciDatum();
        $nova->status        = 'aktivna';
        $nova->save();

        foreach ($this->stavke as $stavka) {
            $novaStavka = $stavka->replicate();
            $novaStavka->pretplata_id = $nova->id;
            $novaStavka->save();
        }

        return $nova;
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
        return $this->hasMany(PretplataStavka::class);
    }
}
