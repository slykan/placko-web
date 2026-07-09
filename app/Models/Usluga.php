<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usluga extends Model
{
    protected $table = 'usluge';

    protected $fillable = [
        'tvrtka_id',
        'naziv',
        'jedinica_mjere',
        'prati_zalihu',
        'minimalna_zaliha',
        'cijena',
        'pdv_stopa',
    ];

    protected $casts = [
        'cijena' => 'decimal:2',
        'pdv_stopa' => 'decimal:2',
        'prati_zalihu' => 'boolean',
        'minimalna_zaliha' => 'decimal:3',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function zalihe(): HasMany
    {
        return $this->hasMany(Zaliha::class);
    }

    public function transakcije(): HasMany
    {
        return $this->hasMany(SkladisnaTransakcija::class);
    }

    public function ukupnaKolicina(): float
    {
        return (float) $this->zalihe()->sum('kolicina');
    }

    public function getCijenaSPdvomAttribute(): float
    {
        if ($this->pdv_stopa === null) {
            return (float) $this->cijena;
        }

        return round((float) $this->cijena * (1 + $this->pdv_stopa / 100), 2);
    }

    public function getPdvIznosAttribute(): float
    {
        if ($this->pdv_stopa === null) {
            return 0.0;
        }

        return round((float) $this->cijena * ($this->pdv_stopa / 100), 2);
    }

    public static function pdvStope(): array
    {
        return [
            null  => 'Bez PDV-a',
            '0'   => '0 %',
            '5'   => '5 %',
            '13'  => '13 %',
            '25'  => '25 %',
        ];
    }

    public function __toString(): string
    {
        return $this->naziv;
    }
}
