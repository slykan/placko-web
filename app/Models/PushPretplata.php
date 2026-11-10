<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pretplata jednog preglednika na web push obavijesti. */
class PushPretplata extends Model
{
    protected $table = 'push_pretplate';

    protected $fillable = ['user_id', 'endpoint', 'endpoint_hash', 'p256dh', 'auth'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
