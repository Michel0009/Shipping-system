<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car_paper extends Model
{
    protected $fillable = ['car_id','type','car_file'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
