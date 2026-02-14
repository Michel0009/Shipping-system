<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = ['user_id','days_number','explaination','end_date'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
