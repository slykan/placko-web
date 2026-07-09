<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skladiste extends Model
{
    protected $table = 'skladista';

    protected $fillable = [
        'tvrtka_id',
        'naziv',
        'adresa',
        'zadano',
    ];

    protected $casts = [
        'zadano' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Skladiste $skladiste) {
            if ($skladiste->zadano) {
                static::where('tvrtka_id', $skladiste->tvrtka_id)
                    ->when($skladiste->exists, fn ($q) => $q->whereKeyNot($skladiste->id))
                    ->update(['zadano' => false]);
            }
        });
    }

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function zalihe(): HasMany
    {
        return $this->hasMany(Zaliha::class);
    }

    public function __toString(): string
    {
        return $this->naziv;
    }
}
