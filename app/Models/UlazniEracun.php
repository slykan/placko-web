<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UlazniEracun extends Model
{
    protected $table = 'ulazni_eracuni';

    protected $fillable = [
        'tvrtka_id',
        'fina_id',
        'broj_racuna',
        'dobavljac_naziv',
        'dobavljac_oib',
        'datum_izdavanja',
        'datum_dospijeca',
        'iznos',
        'valuta',
        'status',
        'napomena',
        'xml',
        'primljeno_at',
    ];

    protected $casts = [
        'datum_izdavanja' => 'date',
        'datum_dospijeca' => 'date',
        'iznos'           => 'decimal:2',
        'primljeno_at'    => 'datetime',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }
}
