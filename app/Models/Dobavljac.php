<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dobavljac extends Model
{
    protected $table = 'dobavljaci';

    protected $fillable = [
        'tvrtka_id',
        'naziv',
        'oib',
        'adresa',
        'mjesto',
        'kontakt_osoba',
        'email',
        'kontakt_broj',
        'napomena',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function __toString(): string
    {
        return $this->naziv;
    }
}
