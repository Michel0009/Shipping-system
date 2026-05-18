<?php

namespace App\Services;

use App\Repositories\CarRepository;
use App\Repositories\DriverRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostService
{
    protected $postRepository;
    protected $userRepository;
    protected $driverRepository;
    protected $carRepository;

    public function __construct(PostRepository $postRepository, UserRepository $userRepository,
            DriverRepository $driverRepository, CarRepository $carRepository)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->driverRepository = $driverRepository;
        $this->carRepository = $carRepository;
    }

    public function create_post(array $data)
    {
        return DB::transaction(function () use ($data) {
            $distance = $this->calculate_OSRM_distance(
                $data['start_position_lng'],
                $data['start_position_lat'],
                $data['end_position_lng'],
                $data['end_position_lat']
            );

            $suitableVehicles = $this->carRepository->get_suitable_vehicles($data);

            $fuelPrices = $this->carRepository->get_fuel_prices();
            $coeffs = $this->carRepository->get_coefficients_calculation();
            
            $minFuel = min($fuelPrices);
            $maxFuel = max($fuelPrices);
            $baseRate = $coeffs['base_rate'];
            $insuranceRate = $coeffs['insurance'] ?? 0.3;

            $minVehCoeff = $suitableVehicles->min('vehicle_coefficient');
            $maxVehCoeff = $suitableVehicles->max('vehicle_coefficient');
            
            $minBaseFare = $suitableVehicles->min('base_fare');
            $maxBaseFare = $suitableVehicles->max('base_fare');
            
            $minCons = $suitableVehicles->min('avg_fuel_consumption') / 100;
            $maxCons = $suitableVehicles->max('avg_fuel_consumption') / 100;

            $distanceCost = $distance * $baseRate;
            $w = $data['weight'];
            $weightFactor = $w < 50 ? 1 : ($w < 200 ? 1.2 : 1.5);

            $minPrice = ($minBaseFare + $distanceCost + ($distance * $minCons * $minFuel)) * $minVehCoeff * $weightFactor;
            
            $maxPrice = ($maxBaseFare + $distanceCost + ($distance * $maxCons * $maxFuel)) * $maxVehCoeff * $weightFactor;

            if ($data['insurance']) {
                $minPrice *= (1 + $insuranceRate);
                $maxPrice *= (1 + $insuranceRate);
            }

            $data['min_price'] = round($minPrice);
            $data['max_price'] = round($maxPrice);
            $data['user_id'] = auth()->id();

            $post = $this->postRepository->create($data);

            return $post;
        });
    }

    public function update_prices(array $prices)
    {
        $post = $this->postRepository->find($prices['post_id']);

        if ($post->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذا الإعلان');
        }
        if ($prices['min_price'] < $post->min_price) {
            abort(422, "الحد الأدنى لا يمكن أن يكون أقل من السعر المقترح ({$post->min_price})");
        }
        if ($prices['max_price'] > $post->max_price) {
            abort(422, "الحد الأعلى لا يمكن أن يكون أعلى من السعر المقترح ({$post->max_price})");
        }
        $prices_reange = [
            'min_price' => $prices['min_price'],
            'max_price' => $prices['max_price'],
        ];

        return $this->postRepository->update($post, $prices_reange);
    }

    public function delete_post($postId)
    {
        $post = $this->postRepository->find($postId);

        if ($post->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذا الإعلان');
        }

        return $this->postRepository->delete($post);
    }

    public function get_my_posts()
    {
        $userId = auth()->id();
        return Cache::remember('user_posts_' . $userId, now()->addHours(24), function () use ($userId) {
            return $this->postRepository->get_user_posts($userId);
        });
    }

    private function calculate_OSRM_distance($lng1, $lat1, $lng2, $lat2)
    {
        $url = "http://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";
        try {
            $response = json_decode(file_get_contents($url), true);
            return $response['routes'][0]['distance'] / 1000;
        } catch (\Exception $e) {
            abort(400, 'حدثت مشكلة أثناء حساب المسافة الرجاء إعادة المحاولة');
        }
    }
}
