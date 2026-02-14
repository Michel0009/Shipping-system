<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device_token extends Model
{
    protected $fillable = ['user_id', 'token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
