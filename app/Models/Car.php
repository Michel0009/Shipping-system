<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ['driver_id', 'vehicle_type_id', 'license_plate_number', 'manufacturer', 'model', 'year_of_manufacture', 'color', 'fuel_type', 'car_status'];

    public function vehicle_type()
    {
        return $this->belongsTo(Vehicle_type::class);
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function car_papers()
    {
        return $this->hasMany(Car_paper::class);
    }
}
