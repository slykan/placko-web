<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadniNalog extends Model
{
    protected $table = 'radni_nalozi';

    protected $fillable = [
        'tvrtka_id', 'klijent_id', 'nositelj_id', 'ponuda_id', 'racun_id',
        'broj', 'redni_broj', 'godina', 'status',
        'datum_otvaranja', 'rok', 'predmet', 'lokacija',
        'opis_posla', 'izvrseni_radovi', 'napomena', 'zavrsen_at',
        'potpis_klijenta', 'potpisao', 'potpisano_at',
        'ukupno_sati', 'ukupno_rad', 'ukupno_materijal',
    ];

    protected $attributes = ['status' => 'otvoren'];

    protected $casts = [
        'datum_otvaranja' => 'date',
        'rok' => 'date',
        'zavrsen_at' => 'datetime',
        'potpisano_at' => 'datetime',
        'ukupno_sati' => 'decimal:2',
        'ukupno_rad' => 'decimal:2',
        'ukupno_materijal' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (RadniNalog $n) {
            if (in_array($n->status, ['zavrsen', 'naplacen'], true)) {
                $n->zavrsen_at ??= now();
            } elseif ($n->status !== 'otkazan') {
                $n->zavrsen_at = null;
            }
        });
    }

    public static function statusi(): array
    {
        return [
            'otvoren' => 'Otvoren',
            'u_tijeku' => 'U tijeku',
            'zavrsen' => 'Završen',
            'naplacen' => 'Naplaćen',
            'otkazan' => 'Otkazan',
        ];
    }

    public static function statusBoja(?string $status): string
    {
        return match ($status) {
            'otvoren' => 'info',
            'u_tijeku' => 'warning',
            'zavrsen' => 'success',
            'naplacen' => 'primary',
            default => 'gray',
        };
    }

    public static function generiraBroj(int $tvrtkaId): array
    {
        $godina = now()->year;
        $redni = (static::where('tvrtka_id', $tvrtkaId)
            ->where('godina', $godina)->max('redni_broj') ?? 0) + 1;

        return ['redni_broj' => $redni, 'godina' => $godina, 'broj' => "RN-{$redni}-{$godina}"];
    }

    public function jeOtvoren(): bool
    {
        return in_array($this->status, ['otvoren', 'u_tijeku'], true);
    }

    public function izracunajUkupno(): void
    {
        $this->load(['stavke', 'radovi']);

        $materijal = 0;
        foreach ($this->stavke as $stavka) {
            $neto = (float) $stavka->cijena * (float) $stavka->kolicina * (1 - (float) $stavka->rabat_posto / 100);
            $materijal += $neto * (1 + (float) ($stavka->pdv_stopa ?? 0) / 100);
        }

        $sati = $rad = 0;
        foreach ($this->radovi as $r) {
            $sati += (float) $r->sati;
            $rad += (float) $r->sati * (float) ($r->cijena_sata ?? 0);
        }

        $this->update([
            'ukupno_sati' => round($sati, 2),
            'ukupno_rad' => round($rad, 2),
            'ukupno_materijal' => round($materijal, 2),
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

    public function nositelj(): BelongsTo
    {
        return $this->belongsTo(Zaposlenik::class, 'nositelj_id');
    }

    public function ponuda(): BelongsTo
    {
        return $this->belongsTo(Ponuda::class);
    }

    public function racun(): BelongsTo
    {
        return $this->belongsTo(Racun::class);
    }

    public function zaposlenici(): BelongsToMany
    {
        return $this->belongsToMany(Zaposlenik::class, 'radni_nalog_zaposlenik')->withPivot('prihvacen_at');
    }

    public function radovi(): HasMany
    {
        return $this->hasMany(RadniNalogRad::class)->orderBy('datum')->orderBy('redni_broj');
    }

    public function stavke(): HasMany
    {
        return $this->hasMany(RadniNalogStavka::class)->orderBy('redni_broj');
    }
}
