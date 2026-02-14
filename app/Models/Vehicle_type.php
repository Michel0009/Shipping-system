<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle_type extends Model
{
    protected $fillable = ['type', 'description', 'vehicle_coefficient', 'avg_fuel_consumption'];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}
