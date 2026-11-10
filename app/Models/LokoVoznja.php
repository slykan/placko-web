<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Jedan redak mjesečne evidencije loko vožnje (privatni automobil u službene svrhe, u mjestu rada). */
class LokoVoznja extends Model
{
    protected $table = 'loko_voznje';

    protected $fillable = [
        'tvrtka_id', 'zaposlenik_id', 'radni_nalog_id', 'putnik', 'datum', 'vrijeme', 'relacija', 'svrha', 'km', 'registracija',
    ];

    protected $casts = ['datum' => 'date', 'km' => 'decimal:1'];

    public function iznos(): float
    {
        return round((float) $this->km * (float) config('putni.km_naknada'), 2);
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
}
