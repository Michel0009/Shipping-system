<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refresh_token extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'is_revoked'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
