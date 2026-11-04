<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadniNalogRad extends Model
{
    protected $table = 'radni_nalog_radovi';

    protected $fillable = [
        'radni_nalog_id', 'zaposlenik_id', 'datum', 'sati', 'cijena_sata', 'opis', 'redni_broj',
    ];

    protected $casts = [
        'datum' => 'date', 'sati' => 'decimal:2', 'cijena_sata' => 'decimal:2',
    ];

    public function radniNalog(): BelongsTo
    {
        return $this->belongsTo(RadniNalog::class);
    }

    public function zaposlenik(): BelongsTo
    {
        return $this->belongsTo(Zaposlenik::class);
    }
}
