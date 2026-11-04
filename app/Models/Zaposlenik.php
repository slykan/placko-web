<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Zaposlenik extends Model
{
    protected $table = 'zaposlenici';

    protected $fillable = [
        'tvrtka_id',
        'ime',
        'prezime',
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

    /** ime_prezime je spremljeni "Ime Prezime" — koriste ga popisi, mailovi i PDF-ovi. */
    protected static function booted(): void
    {
        static::saving(function (Zaposlenik $z) {
            if (filled($z->ime) || filled($z->prezime)) {
                $z->ime_prezime = trim($z->ime.' '.$z->prezime);
            } elseif (filled($z->ime_prezime)) {
                [$z->ime, $z->prezime] = array_pad(preg_split('/\s+/', trim($z->ime_prezime), 2), 2, '');
            }
        });
    }

    /** Osobni link "Moji nalozi" — tko ima link, vidi naloge tog zaposlenika (bez lozinke). */
    public function portalUrl(): string
    {
        if (! $this->portal_token) {
            $this->noviPortalToken();
        }

        return route('moji-nalozi.index', $this->portal_token);
    }

    public function noviPortalToken(): void
    {
        $this->forceFill(['portal_token' => Str::random(48)])->save();
    }

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function radniNalozi(): BelongsToMany
    {
        return $this->belongsToMany(RadniNalog::class, 'radni_nalog_zaposlenik')->withPivot('prihvacen_at');
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
            ->orderBy('prezime')->orderBy('ime')
            ->pluck('ime_prezime', 'id')
            ->all();
    }

    public function __toString(): string
    {
        return $this->ime_prezime;
    }
}
