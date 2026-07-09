<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventuraStavka extends Model
{
    protected $table = 'inventura_stavke';

    protected $fillable = [
        'inventura_id', 'usluga_id', 'ocekivana_kolicina', 'stvarna_kolicina',
    ];

    public function inventura(): BelongsTo
    {
        return $this->belongsTo(Inventura::class);
    }

    public function usluga(): BelongsTo
    {
        return $this->belongsTo(Usluga::class);
    }

    public function getRazlikaAttribute(): ?float
    {
        if ($this->stvarna_kolicina === null) {
            return null;
        }

        return round((float) $this->stvarna_kolicina - (float) $this->ocekivana_kolicina, 3);
    }
}
