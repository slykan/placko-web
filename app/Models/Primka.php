<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Primka extends Model
{
    protected $table = 'primke';

    protected $fillable = [
        'tvrtka_id', 'dobavljac_id', 'skladiste_id', 'broj', 'redni_broj', 'godina',
        'datum_primke', 'napomena', 'ukupno',
    ];

    protected $casts = ['datum_primke' => 'date'];

    public static function generiraBroj(int $tvrtkaId): array
    {
        $godina = now()->year;
        $redni = (static::where('tvrtka_id', $tvrtkaId)
            ->where('godina', $godina)->max('redni_broj') ?? 0) + 1;

        return ['redni_broj' => $redni, 'godina' => $godina, 'broj' => "{$redni}-P-{$godina}"];
    }

    public function izracunajUkupno(): void
    {
        $this->update([
            'ukupno' => round((float) $this->stavke->sum('ukupno'), 2),
        ]);
    }

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function dobavljac(): BelongsTo
    {
        return $this->belongsTo(Dobavljac::class);
    }

    public function skladiste(): BelongsTo
    {
        return $this->belongsTo(Skladiste::class);
    }

    public function stavke(): HasMany
    {
        return $this->hasMany(PrimkaStavka::class)->orderBy('redni_broj');
    }
}
