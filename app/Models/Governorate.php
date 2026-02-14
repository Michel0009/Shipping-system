<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    protected $fillable = ['name'];

    public function drivers()
    {
        return $this->belongsToMany(Driver::class);
    }
    public function shipments()
    {
        return $this->belongsToMany(Shipment::class)->withPivot('start_end');
    }
    public function posts()
    {
        return $this->belongsToMany(Post::class)->withPivot('start_end');
    }
}
