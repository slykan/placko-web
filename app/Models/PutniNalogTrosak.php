<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** Trošak na putnom nalogu (karta, cestarina, smještaj…) s fotografijom računa na privatnom disku. */
class PutniNalogTrosak extends Model
{
    protected $table = 'putni_nalog_troskovi';

    protected $fillable = ['putni_nalog_id', 'vrsta', 'opis', 'iznos', 'privitak', 'redni_broj'];

    protected $casts = ['iznos' => 'decimal:2'];

    protected static function booted(): void
    {
        static::deleted(function (PutniNalogTrosak $t) {
            if ($t->privitak) {
                Storage::disk('local')->delete($t->privitak);
            }
        });
    }

    public function putniNalog(): BelongsTo
    {
        return $this->belongsTo(PutniNalog::class);
    }
}
