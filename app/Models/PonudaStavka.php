<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PonudaStavka extends Model
{
    protected $table = 'ponuda_stavke';

    protected $fillable = [
        'ponuda_id', 'usluga_id', 'naziv', 'opis', 'jedinica_mjere',
        'kolicina', 'cijena', 'rabat_posto', 'pdv_stopa', 'ukupno', 'redni_broj',
    ];

    protected $casts = [
        'kolicina' => 'decimal:3', 'cijena' => 'decimal:2',
        'rabat_posto' => 'decimal:2', 'pdv_stopa' => 'decimal:2', 'ukupno' => 'decimal:2',
    ];

    public function ponuda(): BelongsTo
    {
        return $this->belongsTo(Ponuda::class);
    }

    public function usluga(): BelongsTo
    {
        return $this->belongsTo(Usluga::class);
    }
}
