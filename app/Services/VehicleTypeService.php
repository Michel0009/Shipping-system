<?php

namespace App\Services;

use App\Repositories\CarRepository;


class VehicleTypeService
{

  protected $carRepository;

  public function __construct(CarRepository $carRepository)
  {
      $this->carRepository = $carRepository;
  }

  public function create_vehicle_type(array $data)
  {
      $vehicle_type = [
          'type' => $data['type'],
          'description' => $data['description'],
          'vehicle_coefficient' => $data['vehicle_coefficient'],
          'avg_fuel_consumption' => $data['avg_fuel_consumption'],
          'base_fare' => $data['base_fare'],
          'min_weight' => $data['min_weight'],
          'max_weight' => $data['max_weight'],
          'min_length' => $data['min_length'],
          'max_length' => $data['max_length'],
          'min_width' => $data['min_width'],
          'max_width' => $data['max_width'],
          'min_height' => $data['min_height'],
          'max_height' => $data['max_height'],
      ];

      $this->carRepository->create_vehicle_type($vehicle_type);
  }

  public function update_vehicle_type(array $data, $id)
  {
      $this->carRepository->update_vehicle_type($data, $id);
  }

  public function get_vehicle_types()
  {
      return $this->carRepository->get_vehicle_types();
  }

}
