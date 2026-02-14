<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id','shipment_id', 'message', 'status', 'title'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
