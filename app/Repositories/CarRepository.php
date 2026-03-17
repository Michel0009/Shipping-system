<?php

namespace App\Repositories;

use App\Models\Car;
use App\Models\Car_paper;

class CarRepository
{
    protected $car;
    protected $car_paper;
    public function __construct(Car $car,Car_paper $car_paper)
    {
        $this->car = $car;
        $this->car_paper=$car_paper;
    }
    public function create(array $data): Car
    {
        return $this->car->create($data);
    }
    public function create_car_paper(array $data): Car_paper
    {
        return $this->car_paper->create($data);
    }
    public function find_by_driver_ID($id)
    {
        $car = $this->car->where('driver_id', $id)->select('id', 'vehicle_type_id', 'manufacturer', 'model', 'year_of_manufacture', 'color', 'license_plate_number')
        ->with(['vehicle_type:id,type,description'])->first();
        return $car;
    }

}
