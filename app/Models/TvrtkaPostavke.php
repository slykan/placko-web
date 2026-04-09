<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TvrtkaPostavke extends Model
{
    protected $table = 'tvrtka_postavke';

    protected $fillable = [
        'tvrtka_id',
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_pass',
        'smtp_sigurnost',
        'smtp_from_name',
        'smtp_from_email',
        'pretplate_dani_upozorenja',
        'pretplate_email_predlozak',
        'racun_email_predlozak',
        'fina_cert_putanja',
        'fina_cert_lozinka',
        'fis_prostor_oznaka',
        'fis_uredaj_oznaka',
        'fiskalizacija_aktivna',
        'fiskalizacija_demo',
        'eracun_aktivan',
        'eracun_demo',
        'eracun_cert_putanja',
        'eracun_cert_lozinka',
        'eracun_api_url',
    ];

    protected $casts = [
        'fina_cert_lozinka'   => 'encrypted',
        'fiskalizacija_aktivna' => 'boolean',
        'fiskalizacija_demo'    => 'boolean',
        'eracun_cert_lozinka' => 'encrypted',
        'eracun_aktivan'      => 'boolean',
        'eracun_demo'         => 'boolean',
    ];

    protected $hidden = ['smtp_pass'];

    public function tvrtka(): BelongsTo
    {
        return $this->belongsTo(Tvrtka::class);
    }
}
