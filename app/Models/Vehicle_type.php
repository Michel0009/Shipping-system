<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle_type extends Model
{
    protected $fillable = ['type', 'description', 'vehicle_coefficient', 'avg_fuel_consumption', 
    'base_fare', 'min_weight', 'max_weight', 'min_length', 'max_length', 'min_width', 'max_width', 
    'min_height', 'max_height'];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}
