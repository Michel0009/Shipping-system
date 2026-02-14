<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['driver_id', 'successful_shipments_number'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
