<?php

namespace App\Repositories;

use App\Models\Car;
use App\Models\Car_paper;
use App\Models\Coefficient;
use App\Models\Vehicle_type;

class CarRepository
{
    protected $car;
    protected $car_paper;
    protected $vehicle_type;
    protected $coefficient;
    public function __construct(Car $car, Car_paper $car_paper, Vehicle_type $vehicle_type, Coefficient $coefficient)
    {
        $this->car = $car;
        $this->car_paper = $car_paper;
        $this->vehicle_type = $vehicle_type;
        $this->coefficient = $coefficient;
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

    public function create_vehicle_type(array $data)
    {
        return $this->vehicle_type->create($data);
    }

    public function update_vehicle_type(array $data, $vehicle_type_id)
    {
        return $this->vehicle_type->where('id', $vehicle_type_id)->update($data);
    }

    public function get_vehicle_types()
    {
        return $this->vehicle_type->get();
    }
    public function get_car_files($car)
    {
        return $car->car_papers()->select('id','type', 'car_file')->get();
    }

    public function update($carId, array $data){
        $car= $this->car->find($carId);
        $car->update($data);
    }
    public function update_car_paper($paperId, array $data){
        $carPaper= $this->car_paper->find($paperId);
        $carPaper->update($data);
    }
    public function create_coefficient(array $data)
    {
        return $this->coefficient->create($data);
    }

    public function update_coefficient(array $data, $coefficient_id)
    {
        return $this->coefficient->where('id', $coefficient_id)->update($data);
    }

    public function get_coefficients()
    {
        return $this->coefficient->get();
    }

}
