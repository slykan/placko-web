<?php

namespace App\Models;

use App\Support\PutniObracun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PutniNalog extends Model
{
    protected $table = 'putni_nalozi';

    protected $fillable = [
        'tvrtka_id', 'zaposlenik_id', 'radni_nalog_id', 'broj', 'redni_broj', 'godina', 'status', 'vrsta',
        'putnik', 'radno_mjesto', 'datum_naloga', 'nalogodavac', 'odrediste', 'drzava', 'svrha',
        'prijevoz', 'registracija', 'vozilo', 'polazak_at', 'povratak_at', 'granica_odlazak_at', 'granica_povratak_at',
        'km_pocetno', 'km_zavrsno', 'km', 'obroci', 'predujam', 'tecaj', 'izvjesce',
        'ukupno_dnevnice', 'ukupno_km', 'ukupno_troskovi', 'za_isplatu', 'obracun',
    ];

    protected $attributes = ['status' => 'otvoren', 'vrsta' => 'tuzemni', 'prijevoz' => 'privatni_auto', 'obroci' => 0, 'predujam' => 0];

    protected $casts = [
        'datum_naloga' => 'date',
        'polazak_at' => 'datetime',
        'povratak_at' => 'datetime',
        'granica_odlazak_at' => 'datetime',
        'granica_povratak_at' => 'datetime',
        'km' => 'decimal:1',
        'predujam' => 'decimal:2',
        'tecaj' => 'decimal:6',
        'ukupno_dnevnice' => 'decimal:2',
        'ukupno_km' => 'decimal:2',
        'ukupno_troskovi' => 'decimal:2',
        'za_isplatu' => 'decimal:2',
        'obracun' => 'array',
    ];

    public static function statusi(): array
    {
        return ['otvoren' => 'Otvoren', 'obracunat' => 'Obračunat', 'isplacen' => 'Isplaćen', 'otkazan' => 'Otkazan'];
    }

    public static function statusBoja(?string $status): string
    {
        return match ($status) {
            'otvoren' => 'info',
            'obracunat' => 'warning',
            'isplacen' => 'success',
            default => 'gray',
        };
    }

    /** Prijevozna sredstva; kod automobila je registracija obavezna. */
    public static function prijevozi(): array
    {
        return [
            'privatni_auto' => 'Privatni automobil',
            'sluzbeni_auto' => 'Službeni automobil',
            'autobus' => 'Autobus',
            'vlak' => 'Vlak',
            'zrakoplov' => 'Zrakoplov',
            'ostalo' => 'Ostalo',
        ];
    }

    public static function vrsteTroskova(): array
    {
        return [
            'prijevoz' => 'Prijevoz (karta)',
            'gorivo' => 'Gorivo (službeni auto)',
            'cestarina' => 'Cestarina',
            'parking' => 'Parking',
            'smjestaj' => 'Smještaj',
            'ostalo' => 'Ostalo',
        ];
    }

    public function jeAutomobil(): bool
    {
        return in_array($this->prijevoz, ['privatni_auto', 'sluzbeni_auto'], true);
    }

    public static function generiraBroj(int $tvrtkaId): array
    {
        $godina = now()->year;
        $redni = (static::where('tvrtka_id', $tvrtkaId)->where('godina', $godina)->max('redni_broj') ?? 0) + 1;

        return ['redni_broj' => $redni, 'godina' => $godina, 'broj' => "PN-{$redni}-{$godina}"];
    }

    /** Ponovni obračun dnevnica, kilometara i troškova (poziva se nakon svakog spremanja). */
    public function izracunaj(): void
    {
        $this->load('troskovi');
        $o = PutniObracun::obracunaj($this);

        $this->forceFill([
            'tecaj' => $o['tecaj'] ?? $this->tecaj,
            'ukupno_dnevnice' => $o['dnevnice_ukupno'],
            'ukupno_km' => $o['km_iznos'],
            'ukupno_troskovi' => $o['troskovi_ukupno'],
            'za_isplatu' => $o['za_isplatu'],
            'obracun' => $o,
        ])->saveQuietly();
    }

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function zaposlenik(): BelongsTo
    {
        return $this->belongsTo(Zaposlenik::class);
    }

    public function radniNalog(): BelongsTo
    {
        return $this->belongsTo(RadniNalog::class);
    }

    public function troskovi(): HasMany
    {
        return $this->hasMany(PutniNalogTrosak::class)->orderBy('redni_broj');
    }
}
