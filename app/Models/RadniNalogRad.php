<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadniNalogRad extends Model
{
    protected $table = 'radni_nalog_radovi';

    protected $fillable = [
        'radni_nalog_id', 'zaposlenik_id', 'datum', 'vrijeme_dolaska', 'sati', 'cijena_sata', 'opis', 'redni_broj',
    ];

    protected $casts = [
        'datum' => 'date', 'sati' => 'decimal:2', 'cijena_sata' => 'decimal:2',
    ];

    /** "04.11.2026. 08:30" — datum i vrijeme dolaska za popise i PDF. */
    public function dolazak(): string
    {
        return $this->datum->format('d.m.Y.').($this->vrijeme_dolaska ? ' '.substr($this->vrijeme_dolaska, 0, 5) : '');
    }

    public function radniNalog(): BelongsTo
    {
        return $this->belongsTo(RadniNalog::class);
    }

    public function zaposlenik(): BelongsTo
    {
        return $this->belongsTo(Zaposlenik::class);
    }
}
