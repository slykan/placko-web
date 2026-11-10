<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Android uređaj (FCM token) na kojem je korisnik prijavljen u aplikaciji. */
class MobilePushDevice extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'token', 'session_hash'];

    protected $hidden = ['token', 'token_hash', 'session_hash'];

    protected $casts = ['token' => 'encrypted'];
}
