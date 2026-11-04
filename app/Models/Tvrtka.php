<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UlazniEracun;

class Tvrtka extends Model
{
    protected $table = 'tvrtke';

    protected $fillable = [
        'naziv',
        'vrsta_poslovanja',
        'nkd',
        'adresa',
        'mjesto',
        'po_broj',
        'vlasnik',
        'oib',
        'iban',
        'swift',
        'banka',
        'djelatnost',
        'kontakt_broj',
        'email',
        'web_mjesto',
        'oznaka_operatera',
        'oib_operatera',
        'logo',
        'napomena',
        'u_sustavu_pdv',
        'pnb',
    ];

    protected $casts = [
        'u_sustavu_pdv' => 'boolean',
    ];

    public static function vrstePoslovanja(): array
    {
        return [
            'pausalni_obrt' => 'Paušalni obrt',
            'obrt'          => 'Obrt',
            'jdoo'          => 'j.d.o.o.',
            'doo'           => 'd.o.o.',
            'dd'            => 'd.d.',
            'jtd'           => 'j.t.d.',
            'kd'            => 'k.d.',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->naziv ?? '';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function klijenti(): HasMany
    {
        return $this->hasMany(Klijent::class);
    }

    public function usluge(): HasMany
    {
        return $this->hasMany(Usluga::class);
    }

    public function racuni(): HasMany
    {
        return $this->hasMany(Racun::class);
    }

    public function ponude(): HasMany
    {
        return $this->hasMany(Ponuda::class);
    }

    /** Radni nalozi i zaposlenici su u paketima Pro i Business — dovoljno je da ga ima jedan korisnik tvrtke. */
    public function imaRadneNaloge(): bool
    {
        return $this->users->contains(fn (User $u) => $u->isAdmin() || $u->paket()->radniNalozi());
    }

    public function radniNalozi(): HasMany
    {
        return $this->hasMany(RadniNalog::class);
    }

    public function zaposlenici(): HasMany
    {
        return $this->hasMany(Zaposlenik::class);
    }

    public function skladista(): HasMany
    {
        return $this->hasMany(Skladiste::class);
    }

    public function dobavljaci(): HasMany
    {
        return $this->hasMany(Dobavljac::class);
    }

    public function primke(): HasMany
    {
        return $this->hasMany(Primka::class);
    }

    public function inventure(): HasMany
    {
        return $this->hasMany(Inventura::class);
    }

    public function pretplate(): HasMany
    {
        return $this->hasMany(Pretplata::class);
    }

    public function ulazniEracuni(): HasMany
    {
        return $this->hasMany(UlazniEracun::class);
    }

    public function __toString(): string
    {
        return $this->naziv;
    }
}
