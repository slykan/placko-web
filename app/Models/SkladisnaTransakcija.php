<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkladisnaTransakcija extends Model
{
    protected $table = 'skladisne_transakcije';

    protected $fillable = [
        'tvrtka_id',
        'usluga_id',
        'skladiste_id',
        'tip',
        'kolicina',
        'cijena',
        'racun_id',
        'primka_id',
        'inventura_id',
        'napomena',
        'datum',
    ];

    protected $casts = [
        'kolicina' => 'decimal:3',
        'cijena' => 'decimal:2',
        'datum' => 'date',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function usluga(): BelongsTo
    {
        return $this->belongsTo(Usluga::class);
    }

    public function skladiste(): BelongsTo
    {
        return $this->belongsTo(Skladiste::class);
    }

    public function racun(): BelongsTo
    {
        return $this->belongsTo(Racun::class);
    }

    public function primka(): BelongsTo
    {
        return $this->belongsTo(Primka::class);
    }

    public function inventura(): BelongsTo
    {
        return $this->belongsTo(Inventura::class);
    }
}
