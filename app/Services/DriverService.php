<?php

namespace App\Services;

use App\Repositories\CarRepository;
use App\Repositories\DriverRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DriverService
{

    protected $driverRepository;
    protected $carRepository;
    protected $reviewRepository;
    protected $userRepository;

    public function __construct(DriverRepository $driverRepository, CarRepository $carRepository,
         ReviewRepository $reviewRepository, UserRepository $userRepository)
    {
        $this->driverRepository = $driverRepository;
        $this->carRepository = $carRepository;
        $this->reviewRepository = $reviewRepository;
        $this->userRepository = $userRepository;
    }
    public function change_driver_availability()
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);
        $driver->availability = !$driver->availability;
        $this->driverRepository->save($driver);
        return [
            'message' => 'تم تغيير الحالة بنجاح',
        ];
    }

    private function get_auth_driver()
    {
        $user = Auth::user();
        return $this->driverRepository->find_by_user_ID($user->id);
    }

    public function get_driver_governorates()
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->get_driver_governorates($driver);
    }

    public function get_governorates()
    {
        return $this->driverRepository->get_governorates();
    }

    public function attach_governorate($govId)
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->attach_governorate($driver, $govId);
    }

    public function detach_governorate($govId)
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->detach_governorate($driver, $govId);
    }

    public function get_available_drivers()
    {
        $shipment = Cache::get("shipment_request_user_" . auth()->id());

        if (!$shipment) {
            throw new Exception('لا يوجد طلب شحنة');
        }

        // Get available drivers who suits the shipment
        $drivers = $this->driverRepository->get_available_drivers($shipment);

        $coeff = $this->driverRepository->get_coefficients();

        $baseRate = $coeff['base_rate'] ?? 500;
        $insuranceRate = $coeff['insurance'] ?? 0.3;
        
        // 2. Distance between start_position and end_position
        $shipmentStart = "{$shipment['start_position_lng']},{$shipment['start_position_lat']}";
        $shipmentEnd = "{$shipment['end_position_lng']},{$shipment['end_position_lat']}";
        $url2 = "http://router.project-osrm.org/route/v1/driving/{$shipmentStart};{$shipmentEnd}?overview=false";
        $res2 = json_decode(file_get_contents($url2), true);

        if (!isset($res2['routes'][0])) {
            throw new Exception('فشل حساب مسافة الشحنة');
        }

        $shipmentDistance = $res2['routes'][0]['distance'] / 1000;

        $result = [];
        foreach ($drivers as $driver) {

            // Get driver location
            $loc = Cache::get("location_driver_{$driver->id}");
            if (!$loc) continue;

            // 1. Distance between driver and start_position
            $startDriver = "{$loc['lng']},{$loc['lat']}";

            $url1 = "http://router.project-osrm.org/route/v1/driving/{$startDriver};{$shipmentStart}?overview=false";
            $res1 = json_decode(file_get_contents($url1), true);

            if (!isset($res1['routes'][0])) continue;

            $distanceToStart = $res1['routes'][0]['distance'] / 1000;

            // Get vehicle type for price calculation
            $vehicle = $driver->car;
            $type = $vehicle->vehicle_type;

            // Get fuel details for price calculation
            $fuelType = $vehicle->fuel_type;
            $fuelPrice = $coeff[$fuelType] ?? 10000;

            // Price calculation
            $fuelCost = $shipmentDistance * ($type->avg_fuel_consumption / 100) * $fuelPrice;

            $distanceCost = $shipmentDistance * $baseRate;

            $price = $type->base_fare + $distanceCost + $fuelCost;

            $price *= $type->vehicle_coefficient;

            $w = $shipment['weight'];
            $weightFactor = $w < 50 ? 1 : ($w < 200 ? 1.2 : 1.5);
            $price *= $weightFactor;

            if ($shipment['insurance']) {
                $price += $price * $insuranceRate;
            }

            // Rates
            $rating = round($driver->reviews->avg('rate'), 2);

            $result[] = [
                'id' => $driver->id,
                'first_name' => $driver->user->first_name,
                'last_name' => $driver->user->last_name,
                'rating' => $rating,
                'vehicle' => $type->type,
                'distance_to_start_km' => round($distanceToStart, 2),
                'distance_of_shipment' => round($shipmentDistance, 2),
                'price' => round($price),
                'badge' => $driver->badge->name,
                'badge_text' => $driver->badge->text,
            ];
        }

        return collect($result)->sortBy('distance_to_start_km')->values();
    }

    public function get_driver_details($id){

        $driver = $this->driverRepository->find_driver($id);
        $user = $this->userRepository->find_user($driver->user_id);
        $userData = [
            'user_id' => $user->id,
            'driver_id' => $id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'user_number' => $user->user_number,
            'phone_number' => $user->phone_number,
        ];

        $car = $this->carRepository->find_by_driver_ID($id);
        $driver_governorates = $this->driverRepository->get_driver_governorates($driver)
            ->makeHidden(['pivot','created_at','updated_at']);
            
        $average = $this->reviewRepository->get_driver_average_rate($id);
        $badge = $this->driverRepository->get_badge($driver);

        return [
          'user' => $userData,
          'car' => $car,
          'driver_governorates' => $driver_governorates,
          'average_rate' => round($average, 2),
          'badge' => $badge,
        ];
    }
}
