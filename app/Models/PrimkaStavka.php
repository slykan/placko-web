<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrimkaStavka extends Model
{
    protected $table = 'primka_stavke';

    protected $fillable = [
        'primka_id', 'usluga_id', 'kolicina', 'nabavna_cijena', 'ukupno', 'redni_broj',
    ];

    public function primka(): BelongsTo
    {
        return $this->belongsTo(Primka::class);
    }

    public function usluga(): BelongsTo
    {
        return $this->belongsTo(Usluga::class);
    }
}
