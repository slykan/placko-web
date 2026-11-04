<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zaposlenik extends Model
{
    protected $table = 'zaposlenici';

    protected $fillable = [
        'tvrtka_id',
        'ime_prezime',
        'radno_mjesto',
        'kontakt_broj',
        'email',
        'cijena_sata',
        'aktivan',
        'napomena',
    ];

    protected $casts = [
        'cijena_sata' => 'decimal:2',
        'aktivan' => 'boolean',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function radniNalozi(): BelongsToMany
    {
        return $this->belongsToMany(RadniNalog::class, 'radni_nalog_zaposlenik');
    }

    public function radovi(): HasMany
    {
        return $this->hasMany(RadniNalogRad::class);
    }

    /** @return array<int, string> aktivni zaposlenici tvrtke (+ već odabrani, da se neaktivni ne izgube s naloga) */
    public static function opcije(int $tvrtkaId, array $ukljuci = []): array
    {
        return static::where('tvrtka_id', $tvrtkaId)
            ->where(fn ($q) => $q->where('aktivan', true)->orWhereIn('id', array_filter($ukljuci)))
            ->orderBy('ime_prezime')
            ->pluck('ime_prezime', 'id')
            ->all();
    }

    public function __toString(): string
    {
        return $this->ime_prezime;
    }
}
