<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    protected $fillable = ['user_id', 'warning_text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
