<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zaliha extends Model
{
    protected $table = 'zalihe';

    protected $fillable = [
        'tvrtka_id',
        'usluga_id',
        'skladiste_id',
        'kolicina',
        'prosjecna_nabavna_cijena',
    ];

    protected $casts = [
        'kolicina' => 'decimal:3',
        'prosjecna_nabavna_cijena' => 'decimal:2',
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

    public function getVrijednostAttribute(): float
    {
        return round((float) $this->kolicina * (float) $this->prosjecna_nabavna_cijena, 2);
    }
}
