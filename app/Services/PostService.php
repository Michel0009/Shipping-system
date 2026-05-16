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

    public function apply_to_post(array $data)
    {
        $driver = $this->driverRepository->findByUserId(auth()->id());
        
        if (!$driver) {
            return ['success' => false, 'message' => 'عذراً، هذا الحساب ليس حساب سائق.', 'code' => 403];
        }

        $post = $this->postRepository->find($data['post_id']);

        if ($post->finished) {
            return ['success' => false, 'message' => 'عذراً، هذا الإعلان مغلق ومكتمل ولا يمكن التقديم عليه.', 'code' => 400];
        }

        if ($data['price'] < $post->min_price || $data['price'] > $post->max_price) {
            return ['success' => false, 'message' => "يجب أن يكون السعر ضمن النطاق المقترح بين {$post->min_price} و {$post->max_price}.", 'code' => 422];
        }

        if ($data['date'] > $post->last_date) {
            return ['success' => false, 'message' => 'تاريخ التوصيل لا يمكن أن يتجاوز تاريخ انتهاء الإعلان.', 'code' => 422];
        }

        $this->postRepository->attachDriver($post, $driver->id, [
            'price' => $data['price'],
            'date' => $data['date']
        ]);

        return ['success' => true, 'message' => 'تم تقديم عرضك على هذه الشحنة بنجاح.', 'code' => 200];
    }

    public function cancel_application($postId)
    {
        $driver = $this->driverRepository->findByUserId(auth()->id());
        
        if (!$driver) {
            return ['success' => false, 'message' => 'غير مصرح للعملاء بإجراء هذه العملية.', 'code' => 403];
        }

        $post = $this->postRepository->find($postId);

        if ($post->finished) {
            return ['success' => false, 'message' => 'لا يمكنك إلغاء العرض، الإعلان منتهي ومغلق بالفعل.', 'code' => 400];
        }

        $this->postRepository->detachDriver($post, $driver->id);

        return ['success' => true, 'message' => 'تم سحب وإلغاء عرضك بنجاح.', 'code' => 200];
    }

    public function get_post_details($postId)
    {
        $user = auth()->user();

        if ($user->role_id == 3) {
            $post = $this->postRepository->getPostWithApplicants($postId);
            
            if ($post->user_id == $user->id) {
                return ['success' => true, 'data' => $this->transform_post_applicants($post), 'code' => 200];
            }
        }

        $post = $this->postRepository->find($postId);
        return ['success' => true, 'data' => $post, 'code' => 200];
    }

    public function get_suitable_posts_for_driver()
    {
        $driver = $this->driverRepository->getDriverWithVehicleType(auth()->id());

        if (!$driver || !$driver->car || !$driver->car->vehicle_type) {
            return ['success' => false, 'message' => 'يرجى إكمال بيانات مركبتك ونوعها أولاً لتتمكن من رؤية الإعلانات المناسبة.', 'code' => 400];
        }

        $posts = $this->postRepository->getAvailablePostsForVehicle($driver->car->vehicle_type);

        return ['success' => true, 'data' => $posts, 'code' => 200];
    }

    private function transform_post_applicants($post)
    {
        $formattedDrivers = $post->drivers->map(function ($driver) {
            $type = $driver->car?->vehicle_type;
            $rating = $driver->reviews->avg('rating') ?? 0;

            return [
                'id' => $driver->id,
                'first_name' => $driver->user->first_name,
                'last_name' => $driver->user->last_name,
                'rating' => round($rating, 1),
                'vehicle' => $type?->type,
                'date' => $driver->pivot->date,
                'price' => round($driver->pivot->price),
                'badge' => $driver->badge->name,
                'badge_text' => $driver->badge->text,
            ];
        });

        $postData = $post->toArray();
        $postData['drivers'] = $formattedDrivers;

        return $postData;
    }
}
