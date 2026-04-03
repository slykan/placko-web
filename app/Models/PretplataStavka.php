<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PretplataStavka extends Model
{
    protected $table = 'pretplata_stavke';

    protected $fillable = [
        'pretplata_id',
        'usluga_id',
        'naziv',
        'opis',
        'kolicina',
        'cijena',
        'pdv_stopa',
    ];

    protected $casts = [
        'kolicina'  => 'decimal:3',
        'cijena'    => 'decimal:2',
        'pdv_stopa' => 'decimal:2',
    ];

    public function pretplata(): BelongsTo
    {
        return $this->belongsTo(Pretplata::class);
    }

    public function usluga(): BelongsTo
    {
        return $this->belongsTo(Usluga::class);
    }
}
