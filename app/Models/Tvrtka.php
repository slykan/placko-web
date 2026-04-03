<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'logo',
        'napomena',
        'u_sustavu_pdv',
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

    public function pretplate(): HasMany
    {
        return $this->hasMany(Pretplata::class);
    }

    public function __toString(): string
    {
        return $this->naziv;
    }
}
