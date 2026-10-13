<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Klijent extends Model
{
    protected $table = 'klijenti';

    protected $fillable = [
        'tvrtka_id',
        'naziv',
        'fizicka_osoba',
        'adresa',
        'mjesto',
        'po_broj',
        'vlasnik',
        'oib',
        'iban',
        'swift',
        'banka',
        'email',
        'djelatnost',
        'kontakt_broj',
        'web_mjesto',
    ];

    protected $casts = [
        'fizicka_osoba' => 'boolean',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function __toString(): string
    {
        return $this->naziv;
    }
}
