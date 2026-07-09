<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventura extends Model
{
    protected $table = 'inventure';

    protected $fillable = [
        'tvrtka_id', 'skladiste_id', 'datum', 'napomena', 'status', 'zavrsena_at',
    ];

    protected $casts = [
        'datum' => 'date',
        'zavrsena_at' => 'datetime',
    ];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }

    public function skladiste(): BelongsTo
    {
        return $this->belongsTo(Skladiste::class);
    }

    public function stavke(): HasMany
    {
        return $this->hasMany(InventuraStavka::class);
    }
}
